<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddFavoriteRequest;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ColorResource;
use App\Http\Resources\ProductDetailsResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\SizeResource;
use App\Repositories\ProductRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use App\Models\UserProductView;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Retrieve a paginated list of products based on the provided request parameters.
     *
     * @param  Request  $request  The request object containing page, per_page, and search parameters
     * @return Some_Return_Value The JSON response containing total and products data
     */
    public function index(Request $request)
    {
        // Check if user has selected a branch (now handled on frontend)
        $isAuthenticated = auth()->check();

        $page = $request->page;
        $perPage = $request->per_page;
        $skip = ($page * $perPage) - $perPage;

        $search = $request->search;
        $shopID = $request->shop_id;
        $categoryID = $request->category_id;
        $subCategoryID = $request->sub_category_id;

        $rating = $request->rating; // 4.0
        $sortType = $request->sort_type;
        $minPrice = $request->min_price;
        $maxPrice = $request->max_price;
        $brandID = $request->brand_id;
        $colorID = $request->color_id;
        $sizeID = $request->size_id;
        $isDigital = $request->is_digital == true ? true : false;
        $selectedBranchId = $request->branch_id; // Now comes from frontend request
        $product_code = $request->product_code;
        $discountedProduct = $request->discountedProduct == true ? true : false;
        $generaleSetting = generaleSetting('setting');
        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = generaleSetting('rootShop');
        }

        // get data for
        $rootShop = $shop ?? generaleSetting('rootShop');
        $productQuery = ProductRepository::query()->when($shop, function ($query) use ($shop) {
            return $query->where('shop_id', $shop->id);
        })->isActive();

        $flashSaleMinPrice = DB::table('flash_sale_products')
            ->join('flash_sales', 'flash_sales.id', '=', 'flash_sale_products.flash_sale_id')
            ->when($selectedBranchId, function ($query) use ($selectedBranchId) {
                return $query->where('flash_sale_products.branch_id', $selectedBranchId);
            })
            ->where('flash_sale_products.quantity', '>', 0)
            ->where('flash_sales.status', 1)
            ->where('flash_sales.start_date', '<=', now()->toDateString())
            ->where('flash_sales.end_date', '>=', now()->toDateString())
            ->where(function ($query) {
                $query->where('flash_sales.start_time', '<=', now()->toTimeString())
                    ->orWhere('flash_sales.end_time', '>=', now()->toTimeString());
            })
            ->min('flash_sale_products.price');

        $productMinPrice = $productQuery->min('price');
        if ($flashSaleMinPrice && $flashSaleMinPrice < $productMinPrice) {
            $productMinPrice = $flashSaleMinPrice;
        }

        $productMaxPrice = $productQuery->max('price');
        $sizes = $rootShop?->sizes()->isActive()->get();
        $colors = $rootShop?->colors()->isActive()->get();
        $brands = $rootShop?->brands()->isActive()->get();
      
        // filter query
        $products = ProductRepository::query()
            ->withSum('orders as orders_count', 'order_products.quantity')
            ->withAvg('reviews as average_rating', 'rating')
            ->isActive()
            ->when($isDigital, function ($query) {
                return $query->where('is_digital', true);
            })
            ->when($shop, function ($query) use ($shop) {
                return $query->where('shop_id', $shop->id);
            })->when($shopID && ! $shop, function ($query) use ($shopID) {
                return $query->where('shop_id', $shopID);
            })
            ->when($selectedBranchId, function ($query) use ($selectedBranchId) {
                return $query->whereHas('productBranches', function ($query) use ($selectedBranchId) {
                    return $query->where('branch_id', $selectedBranchId)->where('qty', '>', 0);
                });
            })
            ->when($product_code, function ($query) use ($product_code) {
                return $query->where('code', $product_code);
            })
            ->when($discountedProduct, function ($query) use ($selectedBranchId) {
                $query->where('discount_price', '>', 0)->whereNotNull('discount_price')->orWhereHas('flashSales', function ($q) use ($selectedBranchId) {
                    if ($selectedBranchId) {
                        $q->where('flash_sale_products.branch_id', $selectedBranchId);
                    }
                    $q->isActive();
                });
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('short_description', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%');
                });
            })->when($brandID, function ($query) use ($brandID) {
                return $query->where('brand_id', $brandID);
            })->when($colorID, function ($query) use ($colorID) {
                return $query->whereHas('colors', function ($query) use ($colorID) {
                    return $query->where('id', $colorID);
                });
            })->when($sizeID, function ($query) use ($sizeID) {
                return $query->whereHas('sizes', function ($query) use ($sizeID) {
                    return $query->where('id', $sizeID);
                });
            })->when($categoryID, function ($query) use ($categoryID) {
                return $query->whereHas('categories', function ($query) use ($categoryID) {
                    return $query->where('id', $categoryID);
                });
            })->when($subCategoryID, function ($query) use ($subCategoryID) {
                $query->whereHas('subcategories', function ($query) use ($subCategoryID) {
                    return $query->where('id', $subCategoryID);
                });
            })->when($rating, function ($query) use ($rating) {
                $ratingValue = floatval($rating);
                $upperBound = $ratingValue + 1;

                return $query->havingRaw('average_rating >= '.$rating.' AND average_rating < '.$upperBound);
            })->when($sortType == 'top_selling', function ($query) {
                return $query->orderByDesc('orders_count');
            })->when($sortType == 'popular_product', function ($query) {
                return $query->orderByDesc('orders_count')->orderByDesc('average_rating');
            })->when($sortType == 'newest' || $sortType == 'just_for_you', function ($query) {
                return $query->orderBy('id', 'desc');
            })->when($minPrice || $maxPrice, function ($query) use ($minPrice, $maxPrice, $selectedBranchId) {
                $branchCondition = $selectedBranchId ? ' AND flash_sale_products.branch_id = ?' : '';
                $bindings = $selectedBranchId
                    ? [$selectedBranchId, $minPrice ?? 0, $maxPrice ?? PHP_INT_MAX]
                    : [$minPrice ?? 0, $maxPrice ?? PHP_INT_MAX];

                $query->whereRaw('
                    COALESCE(
                        (SELECT flash_sale_products.price
                         FROM flash_sale_products
                         INNER JOIN flash_sales ON flash_sales.id = flash_sale_products.flash_sale_id
                         WHERE flash_sale_products.product_id = products.id
                         AND flash_sale_products.quantity > 0
                         '.$branchCondition.'
                         AND flash_sales.status = 1
                         AND flash_sales.start_date <= CURDATE()
                         AND flash_sales.end_date >= CURDATE()
                         AND (flash_sales.start_time <= CURTIME() OR flash_sales.end_time >= CURTIME())
                         ORDER BY flash_sale_products.price ASC LIMIT 1
                        ),
                        IF(discount_price > 0, discount_price, price)
                    ) BETWEEN ? AND ?
                ', $bindings);
            })
            ->when(in_array($sortType, ['high_to_low', 'low_to_high']), function ($query) use ($sortType, $selectedBranchId) {
                $order = $sortType === 'high_to_low' ? 'DESC' : 'ASC';
                $branchCondition = $selectedBranchId ? ' AND flash_sale_products.branch_id = '.(int) $selectedBranchId : '';

                return $query->orderByRaw("
                    COALESCE(
                        (SELECT flash_sale_products.price
                         FROM flash_sale_products
                         INNER JOIN flash_sales ON flash_sales.id = flash_sale_products.flash_sale_id
                         WHERE flash_sale_products.product_id = products.id
                         AND flash_sale_products.quantity > 0
                         $branchCondition
                         AND flash_sales.status = 1
                         AND flash_sales.start_date <= CURDATE()
                         AND flash_sales.end_date >= CURDATE()
                         AND (flash_sales.start_time <= CURTIME() OR flash_sales.end_time >= CURTIME())
                         ORDER BY flash_sale_products.price $order LIMIT 1
                        ),
                        IF(discount_price > 0, discount_price, price)
                    ) $order
                ")->orderByDesc('id');
            });

        $total = $products->count();
        $products = $products->when($perPage && $page, function ($query) use ($perPage, $skip) {
            return $query->skip($skip)->take($perPage);
        })->get();

        return $this->json('products', [
            'total' => $total,
            'products' => ProductResource::collection($products),
            'filters' => [
                'sizes' => $sizes ? SizeResource::collection($sizes) : [],
                'colors' => $colors ? ColorResource::collection($colors) : [],
                'brands' => $brands ? BrandResource::collection($brands) : [],
                'min_price' => (int) intval($productMinPrice),
                'max_price' => (int) intval($productMaxPrice),
            ],
        ]);
    }

    /**
     * Show the product details.
     *
     * @param  datatype  $id  description
     * @return response
     */
    public function show(Request $request)
    {
        // Check if user has selected a branch (now handled on frontend)
        // Frontend should include branch_id in request if needed

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $selectedBranchId = $request->branch_id; // Get selected branch from request

        $product = ProductRepository::find($request->product_id);
        ProductRepository::recentView($product);

        $relatedProducts = ProductRepository::query()->whereHas('categories', function ($query) use ($product) {
            $query->whereIn('categories.id', $product->categories->pluck('id'));
        })->where('id', '!=', $product->id)
            ->isActive()
            ->inRandomOrder()
            ->limit(6)->get();

        $shop = $product->shop;

        $popularProducts = ProductRepository::query()
            ->where('shop_id', $shop->id)
            ->where('id', '!=', $product->id)
            ->isActive()
            ->withSum('orders as orders_count', 'order_products.quantity')
            ->withAvg('reviews as average_rating', 'rating')
            ->when($selectedBranchId, function ($query) use ($selectedBranchId) {
                return $query->whereHas('productBranches', function ($query) use ($selectedBranchId) {
                    return $query->where('branch_id', $selectedBranchId)->where('qty', '>', 0);
                });
            })
            ->orderByDesc('average_rating')
            ->orderByDesc('orders_count')
            ->take(6)
            ->get();

        return $this->json('product details', [
            'product' => ProductDetailsResource::make($product),
            'related_products' => ProductResource::collection($relatedProducts),
            'popular_products' => ProductResource::collection($popularProducts),
        ]);
    }

    /**
     * Add or remove favorite product for the user.
     *
     * @param  AddFavoriteRequest  $request  The request for adding a favorite.
     * @return json Response with favorite updated successfully
     */
    public function addFavorite(AddFavoriteRequest $request)
    {
        $product = ProductRepository::find($request->product_id);

        auth()->user()?->customer->favorites()->toggle($product->id);

        return $this->json('favorite updated successfully', [
            'product' => ProductResource::make($product),
        ]);
    }

    /**
     * get list of favorite products.
     *
     * @return json Response
     */
    public function favoriteProducts(Request $request)
    {
        $selectedBranchId = $request->branch_id; // Now comes from frontend request

        $products = auth()->user()->customer->favorites()
            ->when($selectedBranchId, function ($query) use ($selectedBranchId) {
                return $query->whereHas('productBranches', function ($query) use ($selectedBranchId) {
                    return $query->where('branch_id', $selectedBranchId)->where('qty', '>', 0);
                });
            })
            ->get();

        return $this->json('favorite products', [
            'products' => ProductResource::collection($products),
        ]);
    }

    /**
     * Store a new review.
     *
     * @param  ReviewRequest  $request  The review request
     * @return json Response
     */
    public function storeReview(ReviewRequest $request)
    {
        $product = ProductRepository::find($request->product_id);

        $hasReview = $product->reviews()->where('customer_id', auth()->user()->customer->id)->where('order_id', $request->order_id)->first();

        if ($hasReview) {
            return $this->json('review already exists', [
                'review' => ReviewResource::make($hasReview),
            ]);
        }

        $review = ReviewRepository::storeByRequest($request, $product);

        return $this->json('review added successfully', [
            'review' => ReviewResource::make($review),
        ]);
    }
    
    public function trackVisit(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not logged in']);
        }
        $productId = $request->product_id;
        
        if (!$productId) {
            return response()->json(['success' => false, 'message' => 'Product ID is required']);
        }

        $visitCount = $request->visit_count ?? 1;
        $timeSpent = $request->time_spent ?? 0;
        $isFavorite = $request->is_favorite ?? false;
    
        $view = UserProductView::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $productId],
            [
                'visit_count' => DB::raw("visit_count + {$visitCount}"),
                'total_time' => DB::raw("total_time + {$timeSpent}"),
                'is_favorite' => $isFavorite,
                'last_visited_at' => now()
            ]
        );
    
        return response()->json(['success' => true, 'data' => $view]);
    }

    public function couponPopup(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['data' => 'User not authenticated'], 401);
        }
    
        $record = UserProductView::with('product.translations')
            ->where('user_id', auth()->id())
            ->whereNotNull('coupon_code')
            ->latest()
            ->first();
    
        if (!$record || !$record->product) {
            return response()->json(['data' => null]);
        }
    
        return response()->json([
            'data' => [
                'recordId' => $record->id,
                'coupon_code' => $record->coupon_code,
                'product' => [
                    'id'    => $record->product->id,
                    'name'  => is_object($record->product->translated_name)
                                ? $record->product->translated_name->name
                                : $record->product->translated_name,
                    'image' => $record->product->thumbnail,
                ]
            ]
        ]);
    }
    
}

<?php

namespace App\Http\Controllers\Shop;

use App\Services\Chat;
use App\Models\User;
use App\Models\Media;
use App\Models\Product;
use App\Models\Branch;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Storage;
use App\Events\AdminProductRequestEvent;
use App\Repositories\FlashSaleRepository;
use Illuminate\Support\Facades\Validator;
use App\Repositories\NotificationRepository;
use App\Repositories\ProductLicenseRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    /** stock (in cartons, all branches) at or under which a product counts as low */
    private const LOW_STOCK = 5;

    /**
     * Display the product list.
     */
    public function index(Request $request)
    {
        // get category, brand, color and search from request
        $category = $request->category;
        $brand = $request->brand;
        $color = $request->color;
        $branch = $request->branch;
        $search = $request->search;

        $rootShop = generaleSetting('rootShop');
        $shop = generaleSetting('shop');

        // filter products based on category, brand, color, search, status and stock
        $baseQuery = $shop?->products()->withSum('productBranches as stock_total', 'qty');
        $products = (clone $baseQuery)->with(['productBranches.branch', 'brand'])->when($brand, function ($query) use ($brand) {
            return $query->where('brand_id', $brand);
        })->when($category, function ($query) use ($category) {
            return $query->whereHas('categories', function ($query) use ($category) {
                return $query->where('category_id', $category);
            });
        })->when($branch, function ($query) use ($branch) {
            return $query->whereHas('quantities', function ($query) use ($branch) {
                $query->where('branch_id', $branch);
            });
        })->when($color, function ($query) use ($color) {
            return $query->whereHas('colors', function ($query) use ($color) {
                return $query->where('color_id', $color);
            });
        })->when($search, function ($query) use ($search) {
            // an exact product code first, then the (translated) name search
            return $query->where(function ($query) use ($search) {
                $query->where('code', trim($search))->orWhere(fn ($q) => $q->searchTranslated($search));
            });
        })->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->stock === 'low', fn ($q) => $q->havingRaw('COALESCE(stock_total, 0) <= ?', [self::LOW_STOCK]))
            ->when($request->stock === 'out', fn ($q) => $q->havingRaw('COALESCE(stock_total, 0) <= 0'))
            ->latest()->paginate(20)->withQueryString();

        // counts for the quick tabs
        $all = (clone $baseQuery)->get(['products.id', 'products.is_active']);
        $tabCounts = [
            'all' => $all->count(),
            'active' => $all->where('is_active', true)->count(),
            'inactive' => $all->where('is_active', false)->count(),
            'low' => $all->where('stock_total', '<=', self::LOW_STOCK)->count(),
            'out' => $all->where('stock_total', '<=', 0)->count(),
        ];
        $lowStock = self::LOW_STOCK;

        // get brands, colors and categories
        $brands = $rootShop?->brands()->get();
        $colors = $rootShop?->colors()->get();
        $categories = $rootShop?->categories()->get();
        $flashSale = FlashSaleRepository::getIncoming();
        $branches = Branch::all();

        return view('shop.product.index', compact('products', 'brands', 'colors', 'categories', 'flashSale', 'branches', 'tabCounts', 'lowStock'));
    }

    /**
     * Update the price of a product from the product list.
     */
    public function quickUpdate(Request $request, Product $product)
    {
        $data = $request->validate([
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
        ]);

        $data['discount_price'] = (float) ($data['discount_price'] ?? 0);

        // keep the carton price (piece price x pack) in step when it was the calculated one
        $pack = (int) $product->carton_units_count;
        if ($pack > 0 && (! $product->carton_price || round($product->carton_price, 2) == round($product->price * $pack, 2))) {
            $data['carton_price'] = round($data['price'] * $pack, 2);
        }

        // the discount price is the discounted carton price: it must stay under the carton price
        $cartonPrice = $data['carton_price'] ?? $product->sale_carton_price;
        if ($data['discount_price'] > 0 && $data['discount_price'] >= $cartonPrice) {
            throw \Illuminate\Validation\ValidationException::withMessages(['discount_price' => __('The discounted carton price must be less than the carton price.')]);
        }

        $product->update($data);

        return response()->json([
            'message' => __('Price updated successfully'),
            'price' => showCurrency($product->price),
            'discount_price' => $product->discount_price > 0 ? showCurrency($product->discount_price) : null,
            'carton_price' => showCurrency($product->sale_carton_price),
        ]);
    }

    /**
     * Turn on, turn off or delete several products at once.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $products = generaleSetting('shop')?->products()->whereIn('products.id', $request->ids)->get() ?? collect();
        $done = 0;
        $skipped = 0;

        foreach ($products as $product) {
            if ($request->action === 'delete') {
                if (! \Illuminate\Support\Facades\Blade::check('hasPermission', 'shop.product.destroy') || $product->orders()->exists()) {
                    $skipped++;
                    continue;
                }
                ProductRepository::destroy($product);
            } elseif ($request->action === 'activate') {
                if (! $product->is_approve) {
                    $skipped++;
                    continue;
                }
                $product->update(['is_active' => true]);
            } else {
                $product->update(['is_active' => false]);
            }
            $done++;
        }

        $message = __(':count products updated.', ['count' => $done]);
        if ($skipped) {
            $message .= ' '.__(':count skipped (not approved, has orders, or no permission).', ['count' => $skipped]);
        }

        return back()->with($done ? 'success' : 'error', $message);
    }

    /**
     * Display the product details.
     */
    public function show(Product $product)
    {
        return view('shop.product.show', compact('product'));
    }

    /**
     * crete new product.
     */
    public function create()
    {
        $shop = generaleSetting('rootShop');

        // get brands, colors and categories
        $brands = $shop?->brands()->isActive()->get();
        $colors = $shop?->colors()->isActive()->get();
        $categories = $shop?->categories()->active()->get();
        $units = $shop?->units()->isActive()->get();
        $sizes = $shop?->sizes()->isActive()->get();
        $branches = Branch::all();

        return view('shop.product.create', compact('brands', 'colors', 'categories', 'units', 'sizes' , 'branches'));
    }

    public function digitalProductCreate()
    {
        $shop = generaleSetting('rootShop');

        // get brands, colors and categories
        $brands = $shop?->brands()->isActive()->get();
        $colors = $shop?->colors()->isActive()->get();
        $categories = $shop?->categories()->active()->get();
        $units = $shop?->units()->isActive()->get();
        $sizes = $shop?->sizes()->isActive()->get();

        return view('shop.product.digitalProductCreate', compact('brands', 'colors', 'categories', 'units', 'sizes'));
    }

    /**
     * store new product.
     */
    public function store(ProductRequest $request)
    {
        $shop = generaleSetting('shop');

        $skuCode = $shop?->products()->where('code', $request->code)->exists();

        if ($skuCode) {
            return back()->withInput()->withErrors(['code' => __('Product code already exists!')])->with('error', __('Product code already exists!'));
        }

        $this->prepareCartonAndColors($request);

        ProductRepository::storeByRequest($request);

        /** @var User $user */
        $user = auth()->user();
        $isRootUser = $user?->hasRole('root');

        // admin notification message
        if (! $isRootUser && generaleSetting('setting')->shop_type != 'single') {
            $message = 'New product Created Request';
            try {
                AdminProductRequestEvent::dispatch($message);
            } catch (\Throwable $th) {
            }

            $data = (object) [
                'title' => $message,
                'content' => 'New product Created Request from ' . $shop->name,
                'url' => '/admin/products?status=0',
                'icon' => 'bi-shop',
                'type' => 'success',
            ];
            // store notification
            NotificationRepository::storeByRequest($data);
        }

        return to_route('shop.product.index')->withSuccess(__('Product created successfully!'));
    }

    /**
     * Display the product edit form.
     */
    public function edit(Product $product)
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');

        // get brands, colors, units, sizes and categories
        $brands = $rootShop?->brands()->isActive()->get();
        $colors = $rootShop?->colors()->isActive()->get();
        $categories = $rootShop?->categories()->active()->get();
        $units = $rootShop?->units()->isActive()->get();
        $sizes = $rootShop?->sizes()->isActive()->get();

        $categoryId = $product->categories()?->latest('id')->first()?->id;

        $subCategories = SubCategory::whereHas('categories', function ($query) use ($categoryId) {
            return $query->where('category_id', $categoryId);
        })->isActive()->get();
        $branches = Branch::all();

        $metaKeywords = explode(',', $product->meta_keywords) ?: [];

        return view('shop.product.edit', compact('product', 'brands', 'colors', 'categories', 'units', 'sizes', 'subCategories', 'metaKeywords' , 'branches'));
    }

    /**
     * Update the product.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $shop = generaleSetting('shop');

        $skuCode = $shop?->products()->where('code', $request->code)->where('id', '!=', $product->id)->exists();

        if ($skuCode) {
            return back()->withInput()->withErrors(['code' => __('Product code already exists!')])->with('error', __('Product code already exists!'));
        }

        $this->prepareCartonAndColors($request);

        ProductRepository::updateByRequest($request, $product);

        /** @var User $user */
        $user = auth()->user();
        $isRootUser = $user?->hasRole('root');

        // admin notification message
        if (! $isRootUser && generaleSetting('setting')->shop_type != 'single') {
            $message = 'Product Updated Request';
            try {
                AdminProductRequestEvent::dispatch($message);
            } catch (\Throwable $th) {
            }

            $data = (object) [
                'title' => $message,
                'content' => 'Product Updated Request from ' . $shop->name,
                'url' => '/admin/products?status=1',
                'icon' => 'bi-shop',
                'type' => 'success',
            ];
            // store notification
            NotificationRepository::storeByRequest($data);
        }

        return to_route('shop.product.index')->withSuccess(__('Product updated successfully!'));
    }

    /**
     * Delete the product.
     * Products that already appear in orders are kept, because deleting them
     * would also remove them from those orders (order_products cascades).
     */
    public function destroy(Product $product)
    {
        if ($product->orders()->exists()) {
            return back()->withError(__('This product has orders and cannot be deleted. Turn it off instead.'));
        }

        ProductRepository::destroy($product);

        return back()->withSuccess(__('Product deleted successfully'));
    }

    /**
     * Products are always sold by the carton, and colors added from the
     * product form ("new-*" ids) are saved to the shop's color list.
     */
    private function prepareCartonAndColors(ProductRequest $request): void
    {
        $rootShop = generaleSetting('rootShop');

        $cartonUnit = $rootShop?->units()->whereIn('name', ['كرتون', 'كارتون', 'Carton'])->first()
            ?? $rootShop?->units()->create(['name' => 'كرتون', 'is_active' => true]);

        $colors = [];
        foreach ($request->input('color', []) as $color) {
            $colorId = $color['id'] ?? null;

            if (str_starts_with((string) $colorId, 'new-')) {
                $name = trim($color['name'] ?? '');
                if ($name === '') {
                    continue;
                }

                $colorId = $rootShop->colors()->firstOrCreate(
                    ['name' => $name],
                    ['color_code' => $color['code'] ?? null, 'is_active' => true]
                )->id;
            }

            $colors[$colorId] = ['id' => $colorId, 'price' => $color['price'] ?? 0];
        }

        $request->merge(['unit' => $cartonUnit?->id, 'color' => $colors]);
    }

    /**
     * delete thumbnail
     */
    public function thumbnailDestroy(Product $product, Media $media)
    {
        $product->medias()->detach($media->id);
        if (Storage::exists($media->src)) {
            Storage::delete($media->src);
        }

        $media->delete();

        return back()->withSuccess(__('Thumbnail deleted successfully!'));
    }

    public function attachmentDestroy(Product $product, Media $media)
    {
        $product->attachments()->detach($media->id);
        if (Storage::exists($media->src)) {
            Storage::delete($media->src);
        }

        $media->delete();

        return back()->withSuccess(__('Attachment deleted successfully!'));
    }

    public function licenseDestroy(Product $product , $license){

        $productLicense = ProductLicenseRepository::query()->where('id', $license)->where('product_id', $product->id)->first();
        $productLicense->delete();

        return back()->withSuccess(__('License deleted successfully!'));
    }

    /**
     * status toggle a product
     */
    public function statusToggle(Product $product)
    {
        if (! $product->is_approve) {
            return back()->withError(__('Sorry! Your Product is not approved yet!'));
        }

        $product->update([
            'is_active' => ! $product->is_active,
        ]);

        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * generate barcode
     */
    public function generateBarcode(Product $product)
    {
        if (! $product->code) {
            return back()->withError(__('Sorry! Your Product code is not generated yet!'));
        }

        $quantities = request('qty', 4);

        return view('shop.product.barcode', compact('product', 'quantities'));
    }

    /**
     * generate ai data
     */
    public function generateAIData(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'short_description' => 'nullable|string',
            ]);

            $chat = new Chat();
            $chat->systemMessage($request->name);

            $question = str_replace(
                ['{product_name}', '{short_description}'],
                [$request->name, $request->short_description],
                generaleSetting()->product_description
            );

            $question .= "Format the description with proper HTML tags (<p>, <h2>, <ul>, <li>) for CKEditor. Do not include extra phrases like 'The product is','```html', 'Sure', or 'Here is'. Just output the final formatted description.";

            $response = $chat->send($question);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

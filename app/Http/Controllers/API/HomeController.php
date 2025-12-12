<?php

namespace App\Http\Controllers\API;

use App\Models\Ad;
use App\Enums\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ProductBranch;
use App\Models\GeneraleSetting;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopResource;
use App\Repositories\ShopRepository;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\BannerResource;
use App\Repositories\BannerRepository;
use App\Http\Resources\ProductResource;
use App\Repositories\ProductRepository;
use App\Http\Resources\CategoryResource;
use App\Repositories\CategoryRepository;
use App\Http\Resources\FlashSaleResource;
use App\Repositories\FlashSaleRepository;

class HomeController extends Controller
{
    /**
     * Index method for retrieving banners, categories, and popular products.
     *
     * @return Some_Return_Value
     */
    public function index(Request $request)
    {
        // Check if user has selected a branch
        $user = auth()->user();
        $selectedBranch = session('selected_branch');

        // If no session branch, check cache for API users
        if (!$selectedBranch && $user) {
            $selectedBranch = Cache::get('selected_branch_' . $user->id);
        }

        if (auth()->check() && !$selectedBranch) {
            return $this->json('home', [
                'banners' => [],
                'ads' => [],
                'categories' => [],
                'shops' => [],
                'popular_products' => [],
                'just_for_you' => [
                    'total' => 0,
                    'products' => []
                ],
                'incoming_flash_sale' => null,
                'running_flash_sale' => null
            ]);
        }

        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 8;
        $skip = ($page * $perPage) - $perPage;
        $generaleSetting = generaleSetting('setting');
        $rootShop = generaleSetting('rootShop');
        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = $rootShop;
        }

        $banners = BannerRepository::query()->whereNull('shop_id')->active()->get();

        $categories = CategoryRepository::query()->active()
            ->whereHas('shops', function ($query) use ($rootShop) {
                return $query->where('shop_id', $rootShop->id);
            })->whereHas('products', function ($product) {
                return $product->where('is_active', true);
            })->withCount('products')->orderByDesc('products_count')->take(10)->get();

        // Get selected branch for authenticated users
        $selectedBranchId = $request->branch_id ?? session('selected_branch');

        $popularProducts = ProductRepository::query()->isActive()
            ->when($shop, function ($query) use ($shop) {
                return $query->where('shop_id', $shop->id);
            })
            ->when($selectedBranchId, fn($query) => $query->whereHas('productBranches', fn($q) => $q->where('branch_id', $selectedBranchId)->where('qty', '>', 0)))
            ->withSum(['productBranches as branch_qty' => fn($q) => $selectedBranchId ? $q->where('branch_id', $selectedBranchId) : null], 'qty')
            ->having('branch_qty', '>', 0)
            ->withCount('orders as orders_count')
            ->withAvg('reviews as average_rating', 'rating')
            ->orderByDesc('average_rating')
            ->orderByDesc('orders_count')
            ->take(6)->get();

        $justForYou = ProductRepository::query()->isActive()->latest('id')
            ->when($shop, function ($query) use ($shop) {
                return $query->where('shop_id', $shop->id);
            })
            ->when($selectedBranchId, fn($query) => $query->whereHas('productBranches', fn($q) => $q->where('branch_id', $selectedBranchId)->where('qty', '>', 0)))
            ->withSum(['productBranches as branch_qty' => fn($q) => $selectedBranchId ? $q->where('branch_id', $selectedBranchId) : null], 'qty')
            ->having('branch_qty', '>', 0);
        $total = $justForYou->count();
        $justForYou = $justForYou->skip($skip)->take($perPage)->get();

        $shops = collect([]);

        if ($generaleSetting?->shop_type != 'single') {
            $shops = ShopRepository::query()->isActive()->whereHas('products', function ($query) {
                return $query->isActive();
            })->withCount('orders')->withAvg('reviews as average_rating', 'rating')->orderByDesc('average_rating')->orderByDesc('orders_count')->take(8)->get();
        }

        $ads = Ad::where('status', 1)->latest('id')->take(2)->get();

        // get incoming flash sale
        $incomingFlashSale = FlashSaleRepository::getIncoming();

        // get running flash sale
        $runningFlashSale = FlashSaleRepository::getRunning();

        return $this->json('home', [
            'banners' => BannerResource::collection($banners),
            'ads' => BannerResource::collection($ads),
            'categories' => CategoryResource::collection($categories),
            'shops' => ShopResource::collection($shops),
            'popular_products' => ProductResource::collection($popularProducts),
            'just_for_you' => [
                'total' => $total,
                'products' => ProductResource::collection($justForYou),
            ],
            'incoming_flash_sale' => $incomingFlashSale ? FlashSaleResource::make($incomingFlashSale) : null,
            'running_flash_sale' => $runningFlashSale ? FlashSaleResource::make($runningFlashSale)->toArray(request(), 'true', 'true') : null,
        ]);
    }

    /**
     * Get recently viewed products for the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentlyViews()
    {
        $generaleSetting = GeneraleSetting::first();

        $shop = null;
        if ($generaleSetting?->shop_type == 'single') {
            $shop = User::role(Roles::ROOT->value)->first()?->shop;
        }

        /**
         * @var \App\Models\User $user
         */
        $user = auth()->user();

        $products = $user->recentlyViewedProducts()->when($shop, function ($query) use ($shop) {
            return $query->where('shop_id', $shop->id);
        })->where('is_active', true)->orderBy('pivot_updated_at', 'desc')->take(10)->get();

        return $this->json('recently viewed products', [
            'products' => ProductResource::collection($products),
        ]);
    }
}

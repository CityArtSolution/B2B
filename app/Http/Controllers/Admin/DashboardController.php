<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\OrderStatus2;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdraw;
use App\Repositories\FlashSaleRepository;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $generaleSetting = generaleSetting('setting');

        $totalCustomer = Customer::count();

        $totalRider = User::role('driver')->count();

        $shop = null;

        if (! $generaleSetting || $generaleSetting?->shop_type != 'single') {
            $totalShop = Shop::count();
            $totalOrder = Order::count();
            $totalProduct = Product::count();
            $totalCategories = Category::count();
        } else {
            $shop = generaleSetting('shop');
            $totalShop = 0;
            $totalOrder = Order::where('shop_id', $shop?->id)->count();
            $totalProduct = Product::where('shop_id', $shop?->id)->count();
            $totalCategories = Category::whereHas('shops', function ($query) use ($shop) {
                $query->where('id', $shop?->id);
            })->count();
        }

        $orderStatuses = OrderStatus::cases();
        $orderStatuses2 = OrderStatus2::cases();

        $topCustomers = Customer::withCount('orders')->orderBy('orders_count', 'desc')->limit(8)->get();

        $productObject = Product::when($shop, function ($query) use ($shop) {
            return $query->where('shop_id', $shop?->id);
        });

        $topSellingProducts = (clone $productObject)->withCount('orders')->orderBy('orders_count', 'desc')->limit(8)->get();
        
        $lseeSellingProducts = (clone $productObject)->withCount('orders')->orderBy('orders_count', 'asc')->limit(8)->get();

        $topReviewProducts = (clone $productObject)->withAvg('reviews as average_rating', 'rating')->orderBy('average_rating', 'desc')->limit(8)->get();

        $topShops = Shop::withCount('orders')->orderBy('orders_count', 'desc')->withAvg('reviews as average_rating', 'rating')->orderBy('average_rating', 'desc')->limit(8)->get();

        $latestOrders = Order::when($shop, function ($query) use ($shop) {
            return $query->where('shop_id', $shop?->id);
        })->latest('id')->limit(6)->get();

        $topFavorites = (clone $productObject)->whereHas('favorites')->withCount('favorites')->orderBy('favorites_count', 'desc')->limit(8)->get();

        $pendingWithdraw = Withdraw::where('status', 'pending')->sum('amount');
        $alreadyWithdraw = Withdraw::where('status', 'approved')->sum('amount');
        $deniedWithdraw = Withdraw::where('status', 'denied')->sum('amount');

        $totalCommission = Transaction::where('is_commission', true)->sum('amount');

        $flashSale = FlashSaleRepository::getIncoming();
        
        $products_addresses = (clone $productObject)->withCount('orders')->with(['orders.customer'])->orderBy('orders_count', 'desc')->limit(8)->get();

        $overview = $this->overview($shop, $productObject);

        return view('admin.dashboard', compact('overview','totalShop', 'totalOrder' , 'products_addresses' , 'totalCustomer', 'totalProduct', 'orderStatuses', 'orderStatuses2' , 'topCustomers', 'topSellingProducts' , 'lseeSellingProducts' , 'topReviewProducts', 'topShops', 'latestOrders', 'topFavorites', 'pendingWithdraw', 'alreadyWithdraw', 'deniedWithdraw', 'totalCommission', 'totalCategories', 'flashSale', 'totalRider'));
    }

    /**
     * Today's numbers compared with yesterday, and what needs attention, for the top of the dashboard.
     */
    private function overview($shop, $productObject): array
    {
        $orders = fn () => Order::when($shop, fn ($query) => $query->where('shop_id', $shop?->id));
        $sales = fn ($day) => $orders()->whereDate('created_at', $day)
            ->where('order_status', '!=', OrderStatus::CANCELLED->value)->sum('payable_amount');

        $trend = function ($today, $yesterday) {
            if ($yesterday == 0) {
                return $today > 0 ? 100 : 0;
            }

            return (int) round((($today - $yesterday) / $yesterday) * 100);
        };

        $todayOrders = $orders()->whereDate('created_at', today())->count();
        $yesterdayOrders = $orders()->whereDate('created_at', today()->subDay())->count();
        $todaySales = $sales(today());
        $yesterdaySales = $sales(today()->subDay());

        // products whose stock across all branches is at or under the threshold (stock is counted in cartons)
        $lowStockLimit = 5;
        $lowStockProducts = (clone $productObject)->withSum('productBranches as stock_total', 'qty')
            ->havingRaw('COALESCE(stock_total, 0) <= ?', [$lowStockLimit])->orderBy('stock_total')->get();

        return [
            'todayOrders' => $todayOrders,
            'ordersTrend' => $trend($todayOrders, $yesterdayOrders),
            'todaySales' => $todaySales,
            'salesTrend' => $trend($todaySales, $yesterdaySales),
            'pendingOrders' => $orders()->where('order_status', OrderStatus::PENDING->value)->count(),
            'stalePendingOrders' => $orders()->where('order_status', OrderStatus::PENDING->value)
                ->where('created_at', '<', now()->subDay())->count(),
            'unreadOrders' => $orders()->where('is_read', false)->count(),
            'unpaidOrders' => $orders()->where('payment_status', 'Pending')
                ->where('order_status', '!=', OrderStatus::CANCELLED->value)->count(),
            'pendingReturns' => \App\Models\ReturnOrder::where('status', 'pending')->count(),
            'lowStockLimit' => $lowStockLimit,
            'lowStockCount' => $lowStockProducts->count(),
            'outOfStockCount' => $lowStockProducts->where('stock_total', '<=', 0)->count(),
            'lowStockProducts' => $lowStockProducts->take(6),
        ];
    }

    public function orderStatistics()
    {
        $type = request('type') ?? 'daily';
        $generaleSetting = generaleSetting('setting');

        $shop = $generaleSetting?->shop_type == 'single' ? generaleSetting('shop') : null;

        if ($type == 'daily') {

            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();

            $datesAndDays = [];

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $datesAndDays[] = [
                    'date' => $date->toDateString(),
                    'label' => $date->format('l'),
                    'value' => Order::when($shop, function ($query) use ($shop) {
                        return $query->where('shop_id', $shop?->id);
                    })->whereDate('created_at', $date->toDateString())->count(),
                ];
            }

            $labels = array_column($datesAndDays, 'label');
            $values = array_column($datesAndDays, 'value');

            return $this->json('daily order statistics', [
                'labels' => $labels,
                'values' => $values,
                'total' => array_sum($values),
            ]);
        } elseif ($type == 'monthly') {
            $startDate = now()->startOfYear();
            $endDate = now()->endOfYear();

            $datesAndMonths = [];

            for ($date = $startDate; $date->lte($endDate); $date->addMonth()) {
                $datesAndMonths[] = [
                    'date' => $date->toDateString(),
                    'label' => $date->format('M'), // Get the month name
                    'value' => Order::when($shop, function ($query) use ($shop) {
                        return $query->where('shop_id', $shop?->id);
                    })->whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)
                        ->count(),
                ];
            }

            $labels = array_column($datesAndMonths, 'label');
            $values = array_column($datesAndMonths, 'value');

            $total = array_sum($values);

            return $this->json('monthly order statistics', [
                'labels' => $labels,
                'values' => $values,
                'total' => $total,
            ]);
        } else {

            $datesAndYears = [];

            for ($date = now()->startOfYear(); $date->year >= now()->subYears(6)->year; $date->subYear()) {
                $datesAndYears[] = [
                    'date' => $date->toDateString(),
                    'label' => $date->format('Y'),
                    'value' => Order::when($shop, function ($query) use ($shop) {
                        return $query->where('shop_id', $shop?->id);
                    })->whereYear('created_at', $date->year)->count(),
                ];
            }

            $labels = array_column($datesAndYears, 'label');
            $values = array_column($datesAndYears, 'value');

            $total = array_sum($values);

            return $this->json('yearly order statistics', [
                'labels' => $labels,
                'values' => $values,
                'total' => $total,
            ]);
        }
    }
}

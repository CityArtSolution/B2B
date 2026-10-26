{{-- Top of the admin dashboard: today's numbers, what needs attention, quick actions --}}
@php
    $trendClass = fn ($value) => $value > 0 ? 'up' : ($value < 0 ? 'down' : 'flat');
    $trendIcon = fn ($value) => $value > 0 ? 'bi-arrow-up-short' : ($value < 0 ? 'bi-arrow-down-short' : 'bi-dash');
    $ordersUrl = fn ($status = 0) => route('admin.order.index', [$status, 'courier']);
@endphp

<!-- today (the header bar above already greets the user) -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
    <h6 class="m-0 fw-bold">{{ __('Today') }}</h6>
    <span class="text-muted small"><i class="bi bi-calendar3"></i> {{ now()->translatedFormat('l، d F Y') }}</span>
</div>
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="{{ $ordersUrl() }}" class="stat-card st-confirm">
            <span class="stat-icon"><i class="bi bi-bag-check"></i></span>
            <div>
                <div class="stat-label">{{ __('Today orders') }}</div>
                <div class="stat-value">{{ $overview['todayOrders'] }}</div>
                <div class="stat-trend {{ $trendClass($overview['ordersTrend']) }}">
                    <i class="bi {{ $trendIcon($overview['ordersTrend']) }}"></i>{{ abs($overview['ordersTrend']) }}% {{ __('vs yesterday') }}
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card st-delivered">
            <span class="stat-icon"><i class="bi bi-cash-coin"></i></span>
            <div>
                <div class="stat-label">{{ __('Today sales') }}</div>
                <div class="stat-value">{{ showCurrency($overview['todaySales']) }}</div>
                <div class="stat-trend {{ $trendClass($overview['salesTrend']) }}">
                    <i class="bi {{ $trendIcon($overview['salesTrend']) }}"></i>{{ abs($overview['salesTrend']) }}% {{ __('vs yesterday') }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="{{ $ordersUrl('Pending') }}" class="stat-card st-pending">
            <span class="stat-icon"><i class="bi bi-hourglass-split"></i></span>
            <div>
                <div class="stat-label">{{ __('Waiting orders') }}</div>
                <div class="stat-value">{{ $overview['pendingOrders'] }}</div>
                <div class="stat-trend flat">{{ $overview['unreadOrders'] }} {{ __('not opened yet') }}</div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('shop.product.index', ['stock' => 'low', 'view_type' => 'list']) }}" class="stat-card st-cancelled">
            <span class="stat-icon"><i class="bi bi-box-seam"></i></span>
            <div>
                <div class="stat-label">{{ __('Low stock products') }}</div>
                <div class="stat-value">{{ $overview['lowStockCount'] }}</div>
                <div class="stat-trend {{ $overview['outOfStockCount'] ? 'down' : 'flat' }}">{{ $overview['outOfStockCount'] }} {{ __('out of stock') }}</div>
            </div>
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- needs attention -->
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-head">
                <h6><i class="bi bi-bell text-warning"></i> {{ __('Needs your attention') }}</h6>
            </div>
            <div class="panel-body">
                @php
                    $attention = array_filter([
                        ['count' => $overview['stalePendingOrders'], 'icon' => 'bi-clock-history', 'class' => 'st-cancelled', 'text' => __('Orders waiting for more than 24 hours'), 'url' => $ordersUrl('Pending')],
                        ['count' => $overview['unpaidOrders'], 'icon' => 'bi-credit-card', 'class' => 'st-pending', 'text' => __('Unpaid orders'), 'url' => $ordersUrl() . '?payment_status=Pending'],
                        ['count' => $overview['pendingReturns'], 'icon' => 'bi-arrow-return-left', 'class' => 'st-shipping st-on-the-way', 'text' => __('New return requests'), 'url' => route('admin.returnOrder.index')],
                        ['count' => $overview['outOfStockCount'], 'icon' => 'bi-exclamation-octagon', 'class' => 'st-cancelled', 'text' => __('Products out of stock'), 'url' => route('shop.product.index', ['stock' => 'out', 'view_type' => 'list'])],
                    ], fn ($item) => $item['count'] > 0);
                @endphp
                @forelse ($attention as $item)
                    <a href="{{ $item['url'] }}" class="attention-item {{ $item['class'] }}">
                        <span class="d-flex align-items-center gap-2">
                            <span class="status-badge"><i class="bi {{ $item['icon'] }}"></i></span>
                            {{ $item['text'] }}
                        </span>
                        <span class="status-badge">{{ $item['count'] }}</span>
                    </a>
                @empty
                    <div class="empty-state py-4">
                        <i class="bi bi-check2-circle text-success"></i>
                        <h6>{{ __('All caught up!') }}</h6>
                        <p>{{ __('Nothing needs your attention right now.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- low stock -->
    <div class="col-lg-4">
        <div class="panel">
            <div class="panel-head">
                <h6><i class="bi bi-box-seam text-danger"></i> {{ __('Low stock') }}</h6>
                <a href="{{ route('shop.product.index', ['stock' => 'low', 'view_type' => 'list']) }}" class="small">{{ __('View all') }}</a>
            </div>
            <div class="panel-body">
                @forelse ($overview['lowStockProducts'] as $product)
                    <a href="{{ route('shop.product.edit', $product->id) }}" class="attention-item">
                        <span class="d-flex align-items-center gap-2 text-truncate">
                            <img src="{{ $product->thumbnail }}" width="34" height="34" class="rounded object-fit-cover" alt="">
                            <span class="text-truncate">{{ Str::limit($product->translated_name->name ?? $product->name, 32) }}</span>
                        </span>
                        <span class="stock-badge {{ $product->stock_total <= 0 ? 'out' : 'low' }}">
                            {{ (int) $product->stock_total }} {{ __('cartons') }}
                        </span>
                    </a>
                @empty
                    <div class="empty-state py-4">
                        <i class="bi bi-check2-circle text-success"></i>
                        <h6>{{ __('Stock is fine') }}</h6>
                        <p>{{ __('No product has :count cartons or less.', ['count' => $overview['lowStockLimit']]) }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- quick actions -->
    <div class="col-lg-3">
        <div class="panel">
            <div class="panel-head">
                <h6><i class="bi bi-lightning-charge text-primary"></i> {{ __('Quick actions') }}</h6>
            </div>
            <div class="panel-body">
                <div class="quick-actions" style="grid-template-columns: 1fr">
                    @hasPermission('shop.product.create')
                        <a href="{{ route('shop.product.create') }}" class="quick-action"><i class="bi bi-plus-circle"></i> {{ __('Add Product') }}</a>
                    @endhasPermission
                    <a href="{{ $ordersUrl('Pending') }}" class="quick-action"><i class="bi bi-receipt"></i> {{ __('New orders') }}</a>
                    @hasPermission('shop.pos.index')
                        <a href="{{ route('shop.pos.index') }}" class="quick-action"><i class="bi bi-upc-scan"></i> {{ __('Point of sale') }}</a>
                    @endhasPermission
                    @hasPermission('admin.customer.index')
                        <a href="{{ route('admin.customer.index') }}" class="quick-action"><i class="bi bi-people"></i> {{ __('Customers') }}</a>
                    @endhasPermission
                </div>
            </div>
        </div>
    </div>
</div>

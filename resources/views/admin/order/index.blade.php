@extends('layouts.app')

@section('header-title', __('Orders'))

@php
    use App\Enums\PaymentMethod;
    use App\Enums\PaymentStatus;
    use App\Enums\OrderStatus;
    use App\Enums\OrderStatus2;

    $orderStatuses = $co == 'company' ? OrderStatus2::cases() : OrderStatus::cases();
    $currentStatus = $status ?: null;
    $filters = request()->only(['search', 'payment_status', 'payment_method', 'from', 'to']);
    $tabUrl = fn ($value) => route('admin.order.index', [$value ? str_replace(' ', '_', $value) : 0, $co]) . ($filters ? '?' . http_build_query($filters) : '');
    $allCount = $statusCounts->sum();
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h4 class="page-head-title">
                {{ __('Orders') }}
                @if ($co == 'company')
                    <span class="page-head-tag">{{ __('Shipping companies') }}</span>
                @elseif ($co == 'courier')
                    <span class="page-head-tag">{{ __('Courier / private delivery') }}</span>
                @endif
            </h4>
            <p class="page-head-sub">{{ __('Follow orders by status, search by order number or customer, and update the status directly from the list.') }}</p>
        </div>
    </div>

    <!-- status tabs with counts -->
    <div class="status-tabs mb-3">
        <a href="{{ $tabUrl(null) }}" class="status-tab {{ !$currentStatus ? 'active' : '' }}">
            {{ __('All') }} <span class="status-tab-count">{{ $allCount }}</span>
        </a>
        @foreach ($orderStatuses as $orderStatus)
            <a href="{{ $tabUrl($orderStatus->value) }}"
                class="status-tab st-{{ Str::slug($orderStatus->value) }} {{ $currentStatus == $orderStatus->value ? 'active' : '' }}">
                <span class="status-dot"></span>
                {{ __($orderStatus->value) }}
                <span class="status-tab-count">{{ $statusCounts[$orderStatus->value] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <!-- search and filters -->
    <form method="GET" class="filter-bar card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">{{ __('Search') }}</label>
                    <div class="input-icon">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="{{ __('Order number, customer name or phone') }}">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label">{{ __('Payment Status') }}</label>
                    <select name="payment_status" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach (PaymentStatus::cases() as $paymentStatus)
                            <option value="{{ $paymentStatus->value }}" @selected(request('payment_status') == $paymentStatus->value)>
                                {{ __($paymentStatus->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label">{{ __('Payment Method') }}</label>
                    <select name="payment_method" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method }}" @selected(request('payment_method') == $method)>{{ __($method) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label">{{ __('From') }}</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="col-lg-2 col-md-3 col-6">
                    <label class="form-label">{{ __('To') }}</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    @if ($filters)
                        <a href="{{ route('admin.order.index', [$currentStatus ? str_replace(' ', '_', $currentStatus) : 0, $co]) }}"
                            class="btn btn-outline-secondary">{{ __('Clear filters') }}</a>
                    @endif
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-funnel"></i> {{ __('Apply Filters') }}</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern mb-0 cards-on-mobile">
                    <thead>
                        <tr>
                            <th>{{ __('Order ID') }}</th>
                            <th>{{ __('Customer') }}</th>
                            @if ($businessModel == 'multi')
                                <th>{{ __('Shop') }}</th>
                            @endif
                            <th>{{ __('Total Amount') }}</th>
                            <th>{{ __('Payment') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $methodLabel = match ($order->payment_method) {
                                    PaymentMethod::PREVIOUS_CLIENT => __('Previous client'),
                                    PaymentMethod::OFFER_PRICE => __('Offer Price'),
                                    default => __($order->payment_method?->value ?? ''),
                                };
                                $isPaid = $order->payment_status === PaymentStatus::PAID;
                            @endphp
                            <tr class="{{ $order->is_read == 0 ? 'row-new' : '' }}">
                                <td data-label="{{ __('Order ID') }}">
                                    <a href="{{ route('admin.order.show', $order->id) }}" class="fw-bold text-dark">
                                        {{ $order->prefix . $order->order_code }}
                                    </a>
                                    @if ($order->is_read == 0)
                                        <span class="badge-new">{{ __('New') }}</span>
                                    @endif
                                    <div class="text-muted small">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                </td>
                                <td data-label="{{ __('Customer') }}">
                                    <div class="fw-semibold">{{ $order->customer?->user?->name ?? '-' }}</div>
                                    @if ($order->customer?->user?->phone)
                                        <div class="text-muted small" dir="ltr">{{ $order->customer->user->phone }}</div>
                                    @endif
                                </td>
                                @if ($businessModel == 'multi')
                                    <td data-label="{{ __('Shop') }}">{{ $order->shop?->name }}</td>
                                @endif
                                <td data-label="{{ __('Total Amount') }}" class="fw-bold">{{ showCurrency($order->invoice_total) }}</td>
                                <td data-label="{{ __('Payment') }}">
                                    <span class="pay-badge {{ $isPaid ? 'paid' : 'unpaid' }}">
                                        <i class="bi {{ $isPaid ? 'bi-check-circle-fill' : 'bi-hourglass-split' }}"></i>
                                        {{ __($order->payment_status?->value ?? '') }}
                                    </span>
                                    <div class="text-muted small mt-1">{{ $methodLabel }}</div>
                                </td>
                                <td data-label="{{ __('Status') }}">
                                    @hasPermission('admin.order.status.change')
                                        <select class="form-select form-select-sm status-select st-{{ Str::slug($order->order_status->value) }}"
                                            data-url="{{ route('admin.order.status.change', $order->id) }}"
                                            data-current="{{ $order->order_status->value }}">
                                            @foreach ($orderStatuses as $orderStatus)
                                                <option value="{{ $orderStatus->value }}" @selected($order->order_status->value == $orderStatus->value)>
                                                    {{ __($orderStatus->value) }}
                                                </option>
                                            @endforeach
                                            @unless (collect($orderStatuses)->contains(fn ($s) => $s->value == $order->order_status->value))
                                                <option value="{{ $order->order_status->value }}" selected>{{ __($order->order_status->value) }}</option>
                                            @endunless
                                        </select>
                                    @else
                                        <span class="status-badge st-{{ Str::slug($order->order_status->value) }}">{{ __($order->order_status->value) }}</span>
                                    @endhasPermission
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($order->payment_method === PaymentMethod::OFFER_PRICE && $order->order_status->name === 'PENDING')
                                        <a href="{{ route('admin.order.Accept', $order->id) }}" class="btn btn-sm btn-success">
                                            <i class="bi bi-check2"></i> {{ __('Accept Order') }}
                                        </a>
                                    @endif
                                    @hasPermission('admin.order.show')
                                        <a href="{{ route('admin.order.show', $order->id) }}" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="tooltip" data-bs-title="{{ __('view details') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endhasPermission
                                    <a href="{{ route('shop.download-invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="tooltip" data-bs-title="{{ $order->isPriceQuote() ? __('Download Price Quote') : __('Download Invoice') }}">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <h6>{{ __('No orders found') }}</h6>
                                        <p>{{ $filters ? __('Try changing or clearing the filters.') : __('There are no orders in this status currently.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="my-3">
        {{ $orders->links() }}
    </div>
@endsection

@push('scripts')
    <script>
        // change an order's status from the list, after a confirmation
        $(document).on('change', '.status-select', function() {
            const select = $(this);
            const previous = select.data('current');
            const label = select.find('option:selected').text().trim();
            Swal.fire({
                title: "{{ __('Change order status?') }}",
                text: "{{ __('The customer will be notified of the new status:') }} " + label,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: "{{ __('Yes, change it') }}",
                cancelButtonText: "{{ __('Cancel') }}",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = select.data('url') + '?status=' + encodeURIComponent(select.val());
                } else {
                    select.val(previous);
                }
            });
        });
    </script>
@endpush

{{-- Order header: code, status and payment badges, a progress line through the stages and the next-step button --}}
@php
    use App\Enums\PaymentStatus;

    // the usual path of an order; shipping companies have their own shorter path
    $flow = $order->shipping_type == 'company'
        ? ['Pending', 'Confirm', 'Processing', 'On The Way To You', 'Delivered']
        : ['Pending', 'Confirm', 'Processing', 'Delivered To Shipping Company', 'On The Way', 'Delivered'];

    $current = $order->order_status?->value ?? (string) $order->order_status;
    // statuses outside the path sit at the closest stage
    $position = array_search($current, $flow, true);
    if ($position === false) {
        $position = ['Accepted' => 1, 'Pickup' => 2, 'On The Way To You' => 4][$current] ?? 0;
    }
    $isCancelled = $current === 'Cancelled';
    $isDone = $current === 'Delivered';
    $next = (! $isCancelled && ! $isDone) ? ($flow[$position + 1] ?? null) : null;
    $isPaid = $order->payment_status === PaymentStatus::PAID;
    $statusUrl = fn ($status) => route('admin.order.status.change', [$order->id]) . '?status=' . urlencode($status);
@endphp

<div class="order-hero mt-3">
    <div class="order-hero-top">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h4 class="m-0 fw-bold">{{ __('Order') }} #{{ $order->prefix . $order->order_code }}</h4>
                <span class="status-badge st-{{ Str::slug($current) }}">{{ __($current) }}</span>
                <span class="pay-badge {{ $isPaid ? 'paid' : 'unpaid' }}">
                    <i class="bi {{ $isPaid ? 'bi-check-circle-fill' : 'bi-hourglass-split' }}"></i>
                    {{ __($order->payment_status?->value ?? '') }}
                </span>
            </div>
            <div class="text-muted small mt-1">
                <i class="bi bi-calendar3"></i> {{ $order->created_at->format('d M Y, h:i A') }}
                · <i class="bi bi-person"></i> {{ $order->customer?->user?->name ?? '-' }}
                · <i class="bi bi-cash"></i> {{ showCurrency($order->invoice_total) }}
                · {{ __($order->payment_method?->value ?? '') }}
            </div>
        </div>

        @hasPermission('admin.order.status.change')
            <div class="d-flex flex-wrap gap-2">
                @if ($next)
                    <a href="{{ $statusUrl($next) }}" class="btn btn-primary order-next-step" data-status="{{ __($next) }}">
                        <i class="bi bi-arrow-left-circle"></i> {{ __('Move to') }}: {{ __($next) }}
                    </a>
                @endif
                @unless ($isCancelled || $isDone)
                    <a href="{{ $statusUrl('Cancelled') }}" class="btn btn-outline-danger order-next-step" data-status="{{ __('Cancelled') }}">
                        <i class="bi bi-x-circle"></i> {{ __('Cancel order') }}
                    </a>
                @endunless
            </div>
        @endhasPermission
    </div>

    @if ($isCancelled)
        <div class="alert alert-danger mb-0 mt-3"><i class="bi bi-x-octagon"></i> {{ __('This order was cancelled.') }}</div>
    @else
        <div class="order-steps">
            @foreach ($flow as $i => $stage)
                <div class="order-step {{ $i < $position || $isDone ? 'done' : '' }} {{ $i === $position && ! $isDone ? 'current' : '' }}">
                    <span class="order-step-dot">
                        @if ($i < $position || $isDone)
                            <i class="bi bi-check-lg"></i>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </span>
                    <span class="order-step-label">{{ __($stage) }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
    <script>
        $(document).on('click', '.order-next-step', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: `{{ __('Do you really want to mark this order as') }} ${$(this).data('status')}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: "{{ __('Yes, proceed!') }}",
                cancelButtonText: "{{ __('Cancel') }}",
            }).then((result) => {
                if (result.isConfirmed) window.location.href = url;
            });
        });
    </script>
@endpush

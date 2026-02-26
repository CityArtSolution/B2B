@extends('layouts.app')

@section('content')
<style>
    #statement-content {
        font-family: 'Cairo', sans-serif;
        direction: rtl;
        text-align: right;
    }
</style>
<div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2 class="fw-bold">{{ __('account statement') }}</h2>
        <button class="btn btn-dark" onclick="downloadStatementImage()">
            <i class="bi bi-image me-1"></i>
            {{ __('Download Statement') }}
        </button>

    </div>

    <div id="statement-content">
        <div class="card shadow-lg mb-4 border-0">
            <div class="card-body">
                <div class="row align-items-center">

                    <div class="col-md-3 text-center">
                        <img src="{{ $user->thumbnail ?? 'https://placehold.co/140x140' }}"
                             class="rounded-circle border p-1"
                             alt="Customer Image"
                             style="width: 100px;">
                    </div>

                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted">{{ __('Name') }}</small>
                                <div class="fw-semibold">{{ $user->name }} {{ $user->last_name ?? '' }}</div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">{{ __('Phone') }}</small>
                                <div class="fw-semibold">{{ $user->phone }}</div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">{{ __('commercial register') }}</small>
                                <div class="fw-semibold">{{ $user->Commercial_register ?? '-' }}</div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">{{ __('Country') }}</small>
                                <div class="fw-semibold">{{ $user->country ?? '-' }}</div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">{{ __('Additional Number') }}</small>
                                <div class="fw-semibold">{{ $user->Additional_Number ?? '-' }}</div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">{{ __('Tax number') }}</small>
                                <div class="fw-semibold">{{ $user->Tax_number ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        @if($user->customer && $user->customer->addresses->count())
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-geo-alt me-1"></i> {{ __('Address') }}
            </div>

            <div class="card-body">
                <div class="row g-3">
                    @foreach($user->customer->addresses as $address)
                    <div class="col-md-4">
                        <small class="text-muted">{{ __('Area') }}</small>
                        <div class="fw-semibold">{{ $address->area ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">{{ __('Neighborhood') }}</small>
                        <div class="fw-semibold">{{ $address->neighborhood ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">{{ __('Flat') }}</small>
                        <div class="fw-semibold">{{ $address->flat_no ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">{{ __('Postal Code') }}</small>
                        <div class="fw-semibold">{{ $address->post_code ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">{{ __('Address Line 2') }}</small>
                        <div class="fw-semibold">{{ $address->address_line2 ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">{{ __('Address Line 1') }}</small>
                        <div class="fw-semibold">{{ $address->address_line ?? '-' }}</div>
                    </div>
                    <hr/>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-receipt me-1"></i> {{ __('Invoices') }}
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Invoice No') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Download Invoice') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $totalInvoices = 0; @endphp
                        @foreach($user->customer->orders ?? [] as $index => $order)
                        @php $totalInvoices += 1; @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $order->order_code }}</td>
                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                            <td class="fw-semibold">{{ $order->payable_amount }} SAR</td>
                            <td>
                                @if($order->payment_status->value == 'Paid')
                                    <span class="badge bg-success">{{ __('Paid') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('Unpaid') }}</span>
                                @endif
                            </td>
                            <td class="text-center"><a href="{{ route('shop.download-invoice' , $order->id) }}"><i class="bi bi-download"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-arrow-counterclockwise me-1 text-warning"></i> {{ __('returnOrder') }}
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Reason') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Return Date') }}</th>
                            <th>{{ __('view details') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $totalReturns = 0; @endphp
                        @foreach($user->customer->returnOrders ?? [] as $index => $return)
                        @php $totalReturns += 1; @endphp
                        
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $return->reason }}</td>
                            <td class="fw-semibold text-warning">-{{ $return->amount }} SAR</td>
                            <td>{{ $return->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.returnOrder.show', $return->id) }}"
                                    class="btn circleIcon btn-outline-primary btn-sm">
                                    <img src="{{ asset('assets/icons-admin/eye.svg') }}" alt="view"
                                        loading="lazy" />
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <small class="text-muted">Total Invoices</small>
                        <h4 class="fw-bold text-primary mt-1">{{ $totalInvoices }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <small class="text-muted">Total Returns</small>
                        <h4 class="fw-bold text-warning mt-1">{{ $totalReturns }}</h4>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function downloadStatementImage() {
    const element = document.getElementById('statement-content');

    html2canvas(element, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff'
    }).then(canvas => {
        const link = document.createElement('a');
        link.download = 'account-statement.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    });
}
</script>

@endsection

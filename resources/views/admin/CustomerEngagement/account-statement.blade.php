@extends('layouts.app')

@section('content')
<style>
    #statement-content {
        font-family: 'Cairo', sans-serif;
        direction: rtl;
        text-align: right;
        background: #ffffff;
    }

    .kpi-card {
        border-radius: 12px;
        padding: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }

    #statement-content table th, 
    #statement-content table td {
        color: #1e293b !important;
        vertical-align: middle;
    }

    #statement-content .table-striped > tbody > tr:nth-of-type(odd) > * {
        background-color: #f8fafc !important;
        color: #1e293b !important;
        box-shadow: none !important;
    }

    #statement-content .table-striped > tbody > tr:nth-of-type(even) > * {
        background-color: #ffffff !important;
        color: #1e293b !important;
        box-shadow: none !important;
    }

    @media print {
        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 12pt;
        }

        .no-print, header, nav, .sidebar, .navbar, footer, .btn, form {
            display: none !important;
        }

        #statement-content {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
            width: 100% !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
            break-inside: avoid;
        }

        .table th, .table td {
            padding: 6px 8px !important;
            font-size: 11pt;
            border-color: #dee2e6 !important;
        }

        .badge {
            border: 1px solid #000 !important;
            color: #000 !important;
            background: transparent !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
    }
</style>

<div class="container my-4">

    <!-- Top Action Toolbar (no-print) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div class="d-flex align-items-center gap-2">
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="{{ __('Back') }}">
                <i class="bi bi-arrow-right"></i>
            </a>
            <div>
                <h3 class="fw-bold mb-0 text-dark">{{ __('Customer Account Statement') }}</h3>
                <small class="text-muted">{{ __('Statement of accounts and transactions for customer') }}: <strong class="text-dark">{{ $user->name }} {{ $user->last_name ?? '' }}</strong></small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Direct Print Button -->
            <button class="btn btn-primary d-flex align-items-center gap-2 px-3 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer"></i>
                <span>{{ __('Print Statement') }}</span>
            </button>

            <!-- Download Image Button -->
            <button id="download-image-btn" class="btn btn-outline-dark d-flex align-items-center gap-2 px-3 shadow-sm" onclick="downloadStatementImage()">
                <i class="bi bi-image"></i>
                <span>{{ __('Download Statement') }}</span>
            </button>
        </div>
    </div>

    <!-- Filter Toolbar (no-print) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 no-print">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.customer.account-statement', $user->id) }}" class="row g-2 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-muted small mb-1">{{ __('From Date') }}</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-muted small mb-1">{{ __('To Date') }}</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>

                <div class="col-md-3 col-sm-6">
                    <label class="form-label text-muted small mb-1">{{ __('Status') }}</label>
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="Paid" {{ request('payment_status') == 'Paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                        <option value="Unpaid" {{ request('payment_status') == 'Unpaid' ? 'selected' : '' }}>{{ __('Unpaid') }}</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-dark flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> {{ __('Filter Statement') }}
                    </button>
                    @if(request()->hasAny(['from_date', 'to_date', 'payment_status']))
                    <a href="{{ route('admin.customer.account-statement', $user->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Reset Filter') }}">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Official Statement Document -->
    <div id="statement-content" class="p-4 p-md-5 rounded-4 shadow-sm border">

        <!-- Store & Statement Official Header -->
        <div class="border-bottom pb-4 mb-4">
            <div class="row align-items-center">
                <!-- Store Brand Info -->
                <div class="col-sm-6 text-start d-flex align-items-center gap-3">
                    <img src="{{ $generaleSetting?->logo ?? asset('assets/logo.png') }}"
                         alt="Store Logo"
                         class="img-fluid"
                         style="max-height: 65px; object-fit: contain;">
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">{{ $generaleSetting?->name ?? config('app.name') }}</h4>
                        <div class="small text-muted">
                            @if($generaleSetting?->footer_phone)
                                <span><i class="bi bi-telephone ms-1"></i>{{ $generaleSetting->footer_phone }}</span>
                            @endif
                            @if($generaleSetting?->address)
                                <span class="ms-3"><i class="bi bi-geo-alt ms-1"></i>{{ $generaleSetting->address }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Document Metadata -->
                <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6 rounded-pill">
                        {{ __('Customer Account Statement') }}
                    </span>
                    <div class="mt-2 small text-muted">
                        <div><strong>{{ __('Date of Issue') }}:</strong> {{ now()->format('Y-m-d - h:i A') }}</div>
                        <div><strong>{{ __('Statement Period') }}:</strong> 
                            @if(request('from_date') || request('to_date'))
                                {{ request('from_date') ?? '...' }} إلى {{ request('to_date') ?? now()->format('Y-m-d') }}
                            @else
                                {{ __('All Transactions') }}
                            @endif
                        </div>
                        <div><strong>{{ __('Customer ID') }}:</strong> #{{ $user->id }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Details Card -->
        <div class="card border-0 bg-light rounded-3 mb-4 p-3">
            <div class="row align-items-center g-3">
                <div class="col-auto text-center">
                    <img src="{{ $user->thumbnail ?? asset('assets/icons-admin/user-circle.svg') }}"
                         crossorigin="anonymous"
                         class="rounded-circle border p-1 bg-white shadow-sm"
                         alt="Customer Avatar"
                         style="width: 80px; height: 80px; object-fit: cover;"
                         onerror="this.src='{{ asset('assets/icons-admin/user-circle.svg') }}'">
                </div>

                <div class="col">
                    <div class="row g-2">
                        <div class="col-md-4 col-sm-6">
                            <small class="text-muted d-block">{{ __('Name') }}</small>
                            <span class="fw-bold text-dark fs-6">{{ $user->name }} {{ $user->last_name ?? '' }}</span>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <small class="text-muted d-block">{{ __('Phone') }}</small>
                            <span class="fw-semibold text-dark" dir="ltr">
                                <a href="https://wa.me/+966{{ $user->phone }}" target="_blank" class="text-decoration-none text-dark">
                                    {{ $user->phone }} <i class="bi bi-whatsapp text-success ms-1 no-print"></i>
                                </a>
                            </span>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <small class="text-muted d-block">{{ __('commercial register') }}</small>
                            <span class="fw-semibold">{{ $user->Commercial_register ?? '-' }}</span>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <small class="text-muted d-block">{{ __('Tax number') }}</small>
                            <span class="fw-semibold">{{ $user->Tax_number ?? '-' }}</span>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <small class="text-muted d-block">{{ __('Country') }}</small>
                            <span class="fw-semibold">{{ $user->country ?? '-' }}</span>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <small class="text-muted d-block">{{ __('Additional Number') }}</small>
                            <span class="fw-semibold">{{ $user->Additional_Number ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Invoiced -->
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-primary bg-opacity-10 text-primary border-primary border-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-semibold text-primary text-opacity-75">{{ __('Total Invoiced') }}</small>
                        <i class="bi bi-receipt fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-primary">{{ showCurrency($totalInvoicedAmount) }}</div>
                    <small class="text-muted" style="font-size: 11px;">{{ count($orders) }} {{ __('Invoices') }}</small>
                </div>
            </div>

            <!-- Total Paid -->
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-success bg-opacity-10 text-success border-success border-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-semibold text-success text-opacity-75">{{ __('Total Paid') }}</small>
                        <i class="bi bi-check-circle fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-success">{{ showCurrency($totalPaidAmount) }}</div>
                    <small class="text-muted" style="font-size: 11px;">{{ __('Paid Invoices') }}</small>
                </div>
            </div>

            <!-- Balance Due -->
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-danger bg-opacity-10 text-danger border-danger border-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-semibold text-danger text-opacity-75">{{ __('Balance Due') }}</small>
                        <i class="bi bi-exclamation-triangle fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-danger">{{ showCurrency($balanceDue) }}</div>
                    <small class="text-muted" style="font-size: 11px;">{{ __('Unpaid Invoices') }}</small>
                </div>
            </div>

            <!-- Total Returns or Credit Limit -->
            <div class="col-6 col-md-3">
                <div class="kpi-card bg-warning bg-opacity-10 text-dark border-warning border-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-semibold text-warning-emphasis">{{ __('Total Returns') }}</small>
                        <i class="bi bi-arrow-counterclockwise fs-5 text-warning"></i>
                    </div>
                    <div class="fs-4 fw-bold text-dark">{{ showCurrency($totalReturnsAmount) }}</div>
                    <small class="text-muted" style="font-size: 11px;">{{ count($returnOrders) }} {{ __('returnOrder') }}</small>
                </div>
            </div>
        </div>

        @if($user->maximum_invoices_total > 0)
        <!-- Credit Limit Banner -->
        <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-4 rounded-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-primary fs-5"></i>
                <span class="fw-semibold">{{ __('Credit Limit') }}:</span>
                <strong class="text-dark">{{ showCurrency($user->maximum_invoices_total) }}</strong>
            </div>
            <div class="small text-muted">
                @php
                    $creditUsagePercent = $user->maximum_invoices_total > 0 ? min(100, round(($balanceDue / $user->maximum_invoices_total) * 100)) : 0;
                @endphp
                <span>نسبة استهلاك الرصيد الائتماني: </span>
                <span class="badge {{ $creditUsagePercent > 80 ? 'bg-danger' : ($creditUsagePercent > 50 ? 'bg-warning text-dark' : 'bg-success') }}">
                    {{ $creditUsagePercent }}%
                </span>
            </div>
        </div>
        @endif

        <!-- Customer Addresses (if available) -->
        @if($user->customer && $user->customer->addresses->count())
        <div class="card border mb-4 rounded-3 overflow-hidden">
            <div class="card-header bg-white py-2 px-3 fw-bold border-bottom">
                <i class="bi bi-geo-alt text-danger me-1"></i> {{ __('Address') }}
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    @foreach($user->customer->addresses as $address)
                    <div class="col-md-3 col-sm-6">
                        <small class="text-muted d-block">{{ __('Neighborhood') }}</small>
                        <span class="fw-semibold">{{ $address->neighborhood ?? '-' }}</span>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <small class="text-muted d-block">{{ __('Area') }}</small>
                        <span class="fw-semibold">{{ $address->area ?? '-' }}</span>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <small class="text-muted d-block">{{ __('Address Line 1') }}</small>
                        <span class="fw-semibold">{{ $address->address_line ?? '-' }}</span>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <small class="text-muted d-block">{{ __('Postal Code') }}</small>
                        <span class="fw-semibold">{{ $address->post_code ?? '-' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Invoices Table Section -->
        <div class="card border mb-4 rounded-3 overflow-hidden">
            <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                <span class="fw-bold">
                    <i class="bi bi-receipt text-primary me-1"></i> {{ __('Invoices') }}
                </span>
                <span class="badge bg-secondary rounded-pill">{{ count($orders) }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>{{ __('Invoice No') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Payment Method') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center no-print" style="width: 100px;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $index => $order)
                        @php
                            $pStatus = is_object($order->payment_status) ? $order->payment_status->value : $order->payment_status;
                            $isPaid = strtolower((string) $pStatus) === 'paid';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <a href="{{ route('admin.order.show', $order->id) }}" class="fw-bold text-decoration-none text-primary" target="_blank">
                                    {{ $order->order_code }}
                                </a>
                            </td>
                            <td>{{ $order->created_at?->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ is_object($order->payment_method) ? $order->payment_method->value : ($order->payment_method ?? 'Cash on Delivery') }}
                                </span>
                            </td>
                            <td class="text-end fw-bold {{ $isPaid ? 'text-dark' : 'text-danger' }}">
                                {{ showCurrency($order->payable_amount) }}
                            </td>
                            <td class="text-center">
                                @if($isPaid)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check2"></i> {{ __('Paid') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-clock"></i> {{ __('Unpaid') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center no-print">
                                <a href="{{ route('shop.download-invoice', $order->id) }}" class="btn btn-sm btn-outline-secondary rounded-circle" title="{{ __('Download Invoice') }}" target="_blank">
                                    <i class="bi bi-download"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                {{ __('No orders found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    @if(count($orders))
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-start">{{ __('Total') }}:</td>
                            <td class="text-end text-primary fs-6">{{ showCurrency($totalInvoicedAmount) }}</td>
                            <td colspan="2" class="text-center text-muted small">
                                ({{ __('Paid') }}: {{ showCurrency($totalPaidAmount) }} | {{ __('Unpaid') }}: {{ showCurrency($totalUnpaidAmount) }})
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Returns Table Section (if returns exist) -->
        @if(count($returnOrders))
        <div class="card border mb-4 rounded-3 overflow-hidden">
            <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                <span class="fw-bold">
                    <i class="bi bi-arrow-counterclockwise text-warning me-1"></i> {{ __('returnOrder') }}
                </span>
                <span class="badge bg-warning text-dark rounded-pill">{{ count($returnOrders) }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>{{ __('Reason') }}</th>
                            <th>{{ __('Return Date') }}</th>
                            <th class="text-end">{{ __('Amount') }}</th>
                            <th class="text-center no-print" style="width: 100px;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($returnOrders as $index => $return)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $return->reason ?? '-' }}</td>
                            <td>{{ $return->created_at?->format('Y-m-d') }}</td>
                            <td class="text-end fw-bold text-danger">
                                -{{ showCurrency($return->amount) }}
                            </td>
                            <td class="text-center no-print">
                                <a href="{{ route('admin.returnOrder.show', $return->id) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="{{ __('view details') }}" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-start">{{ __('Total Returns') }}:</td>
                            <td class="text-end text-danger fs-6">-{{ showCurrency($totalReturnsAmount) }}</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        <!-- Official Signatures & Declaration Footer -->
        <div class="border-top pt-4 mt-4">
            <div class="row align-items-end g-4">
                <div class="col-sm-6">
                    <p class="small text-muted mb-0 leading-relaxed">
                        <i class="bi bi-info-circle ms-1"></i>
                        تم استخراج هذا الكشف آلياً من نظام إدارة الحسابات، ويعتبر وثيقة مطابقة رسمية للمعاملات المالية المعتمدة للعميل حتى تاريخ استخراجه.
                    </p>
                </div>

                <div class="col-sm-6">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="small text-muted mb-4">{{ __('Accountant Signature') }}</div>
                            <div class="border-bottom mx-auto" style="width: 120px; border-style: dashed !important;"></div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted mb-4">{{ __('Official Stamp') }}</div>
                            <div class="border rounded-circle mx-auto d-flex align-items-center justify-content-center text-muted" style="width: 65px; height: 65px; border-style: dashed !important; font-size: 11px;">
                                {{ __('Official Stamp') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
async function downloadStatementImage() {
    const btn = document.getElementById('download-image-btn');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> {{ __("Downloading...") }}';
    }

    const element = document.getElementById('statement-content');
    if (!element) return;

    // Scroll window to top before capturing to avoid html2canvas viewport offset clipping bug
    const prevScrollY = window.scrollY;
    window.scrollTo(0, 0);

    // Give browser 200ms to settle layout & font rendering
    await new Promise(resolve => setTimeout(resolve, 200));

    try {
        const canvas = await html2canvas(element, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            scrollX: 0,
            scrollY: 0,
            x: 0,
            y: 0,
            width: element.scrollWidth,
            height: element.scrollHeight,
            windowWidth: document.documentElement.scrollWidth,
            windowHeight: document.documentElement.scrollHeight,
            logging: false,
            onclone: (clonedDoc) => {
                const clonedEl = clonedDoc.getElementById('statement-content');
                if (clonedEl) {
                    clonedEl.style.transform = 'none';
                    clonedEl.style.boxShadow = 'none';
                    clonedEl.style.overflow = 'visible';

                    // Ensure all tables and cards inside cloned doc are fully expanded and have visible overflow
                    clonedEl.querySelectorAll('.table-responsive, .overflow-hidden, .card').forEach(el => {
                        el.style.overflow = 'visible';
                        el.style.maxHeight = 'none';
                    });

                    // Force solid dark colors on all table rows to prevent canvas opacity/css-variable bugs
                    clonedEl.querySelectorAll('table, thead, tbody, tfoot, tr, th, td').forEach(el => {
                        el.style.color = '#1e293b';
                        el.style.opacity = '1';
                        el.style.visibility = 'visible';
                    });

                    // Ensure all text elements are 100% opaque
                    clonedEl.querySelectorAll('span, div, p, small, h1, h2, h3, h4, strong').forEach(el => {
                        el.style.opacity = '1';
                    });

                    // Hide buttons/actions that shouldn't appear in the image
                    clonedEl.querySelectorAll('.no-print').forEach(el => {
                        el.style.display = 'none';
                    });
                }
            }
        });

        const link = document.createElement('a');
        link.download = 'كشف-حساب-{{ $user->id }}-' + new Date().toISOString().slice(0,10) + '.png';
        link.href = canvas.toDataURL('image/png', 1.0);
        link.click();
    } catch (error) {
        console.error('Failed to generate image:', error);
        alert('حدث خطأ أثناء تحميل الصورة. يمكنك استخدام زر "طباعة الكشف" لحفظه بصيغة PDF.');
    } finally {
        window.scrollTo(0, prevScrollY);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
}
</script>

@endsection

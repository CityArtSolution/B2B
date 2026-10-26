@extends('layouts.app')
@section('header-title', __('Product List'))

@php
    // the table is the default: it is the faster view to manage stock and prices
    $viewType = request('view_type', 'list') == 'grid' ? 'grid' : 'list';
    $query = request()->except('page');
    $tabUrl = fn (array $change) => route('shop.product.index', array_filter(array_merge($query, ['status' => null, 'stock' => null], $change)));
    $tabs = [
        ['key' => 'all', 'label' => __('All'), 'params' => [], 'active' => !request('status') && !request('stock'), 'class' => ''],
        ['key' => 'active', 'label' => __('Active'), 'params' => ['status' => 'active'], 'active' => request('status') == 'active', 'class' => 'st-delivered'],
        ['key' => 'inactive', 'label' => __('Inactive'), 'params' => ['status' => 'inactive'], 'active' => request('status') == 'inactive', 'class' => ''],
        ['key' => 'low', 'label' => __('Low stock'), 'params' => ['stock' => 'low'], 'active' => request('stock') == 'low', 'class' => 'st-pending'],
        ['key' => 'out', 'label' => __('Out of stock'), 'params' => ['stock' => 'out'], 'active' => request('stock') == 'out', 'class' => 'st-cancelled'],
    ];
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h4 class="page-head-title">{{ __('Product List') }}</h4>
            <p class="page-head-sub">{{ __('Manage prices and stock from here. Click a price to change it directly.') }}</p>
        </div>
        <div class="page-head-actions">
            <div class="btn-group">
                <a href="{{ route('shop.product.index', array_merge($query, ['view_type' => 'list'])) }}"
                    class="btn {{ $viewType == 'list' ? 'btn-primary' : 'btn-outline-secondary' }}" data-bs-toggle="tooltip" data-bs-title="{{ __('Table view') }}">
                    <i class="bi bi-list-ul"></i>
                </a>
                <a href="{{ route('shop.product.index', array_merge($query, ['view_type' => 'grid'])) }}"
                    class="btn {{ $viewType == 'grid' ? 'btn-primary' : 'btn-outline-secondary' }}" data-bs-toggle="tooltip" data-bs-title="{{ __('Grid view') }}">
                    <i class="bi bi-grid"></i>
                </a>
            </div>
            @hasPermission('shop.product.create')
                <a href="{{ route('shop.product.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> {{ __('Add Product') }}
                </a>
            @endhasPermission
        </div>
    </div>

    <div class="container-fluid">
        <!-- Flash Deal Alert -->
        @if ($flashSale)
            <div>
                <div class="alert flash-deal-alert d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex flex-column">
                        <div class="deal-text">{{ $flashSale->name }}</div>
                        <div class="deal-title">{{ __('Coming Soon') }}</div>
                    </div>
                    <div class="countdown d-flex align-items-center">
                        <!-- Days -->
                        <div class="countdown-section">
                            <div class="countdown-label">{{ __('Days') }}</div>
                            <div id="days" class="countdown-time">00</div>
                        </div>
                        <!-- Hours -->
                        <div class="countdown-section">
                            <div class="countdown-label">{{ __('Hours') }}</div>
                            <div id="hours" class="countdown-time">00</div>
                        </div>
                        <!-- Minutes -->
                        <div class="countdown-section">
                            <div class="countdown-label">{{ __('Minutes') }}</div>
                            <div id="minutes" class="countdown-time">00</div>
                        </div>
                        <!-- Seconds -->
                        <div class="countdown-section">
                            <div class="countdown-label">{{ __('Seconds') }}</div>
                            <div id="seconds" class="countdown-time">00</div>
                        </div>
                    </div>
                    @hasPermission('shop.flashSale.show')
                        <a href="{{ route('shop.flashSale.show', $flashSale->id) }}" class="btn btn-primary py-2.5 addBtn">
                            {{ __('Add Product') }}
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endif
        <!-- End Flash Deal Alert -->

        <!-- Filter Product Modal -->
        <form action="" method="GET">
            <div class="modal fade" id="filterProductModal" tabindex="-1" aria-labelledby="filterProductModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="filterProductModalLabel">{{ __('Filter Products') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div>
                                <x-select label="Branch" name="branch" placeholder="Select Branch">
                                    <option value="">
                                        {{ __('Select Branch') }}
                                    </option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ request('branch') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name[app()->getLocale() ?? 'en'] }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>
                            
                            <div>
                                <x-select label="Category" name="category" placeholder="Select Category">
                                    <option value="">
                                        {{ __('Select Category') }}
                                    </option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                                <input type="hidden" name="view_type" value="{{ request('view_type') }}">
                            </div>

                            <div class="mt-3">
                                <x-select label="Brand" name="brand" placeholder="All Brand">
                                    <option value="">
                                        {{ __('All Brand') }}
                                    </option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>

                            <div class="mt-3">
                                <x-select label="Color" name="color" placeholder="All Color">
                                    <option value="">
                                        {{ __('All Color') }}
                                    </option>
                                    @foreach ($colors as $color)
                                        <option value="{{ $color->id }}"
                                            {{ request('color') == $color->id ? 'selected' : '' }}>
                                            {{ $color->name }}
                                        </option>
                                    @endforeach
                                </x-select>
                            </div>
                        </div>
                        <div class=" modal-footer d-flex justify-content-between flex-wrap gap-2">
                            <a href="{{ route('shop.product.index') }}" class="btn btn-light py-2 px-4">
                                {{ __('Reset') }}
                            </a>

                            <button type="submit" class="btn btn-primary">
                                {{ __('Apply Filters') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- End Filter Product Modal -->

        <!-- quick tabs -->
        <div class="status-tabs mb-3">
            @foreach ($tabs as $tab)
                <a href="{{ $tabUrl($tab['params']) }}" class="status-tab {{ $tab['class'] }} {{ $tab['active'] ? 'active' : '' }}">
                    @if ($tab['class'])
                        <span class="status-dot"></span>
                    @endif
                    {{ $tab['label'] }}
                    <span class="status-tab-count">{{ $tabCounts[$tab['key']] }}</span>
                </a>
            @endforeach
        </div>

        <div class="mb-3 card">
            <div class="card-body">
                <form action="" class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <div class="input-icon flex-grow-1" style="max-width: 420px">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="form-control"
                            placeholder="{{ __('Search by product name or code') }}" value="{{ request('search') }}">
                    </div>
                    @foreach (request()->only(['view_type', 'status', 'stock', 'brand', 'category', 'color', 'branch']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> {{ __('Search') }}</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterProductModal">
                        <i class="bi bi-funnel"></i> {{ __('Filter') }}
                        @php $activeFilters = count(array_filter(request()->only(['brand', 'category', 'color', 'branch']))); @endphp
                        @if ($activeFilters)
                            <span class="badge bg-primary rounded-pill">{{ $activeFilters }}</span>
                        @endif
                    </button>
                    @if (array_filter(request()->only(['search', 'brand', 'category', 'color', 'branch', 'status', 'stock'])))
                        <a href="{{ route('shop.product.index', ['view_type' => $viewType]) }}" class="btn btn-link text-muted">{{ __('Clear filters') }}</a>
                    @endif
                </form>

                @if ($viewType == 'list')
                    @include('shop.product.listView')
                @else
                    @include('shop.product.gridView')
                @endif
            </div>
        </div>

        <div class="my-3">
            {{ $products->links() }}
        </div>

        <form action="" method="POST" class="d-none" id="deleteProductForm">
            @csrf
            @method('DELETE')
        </form>

    </div>
@endsection
@push('scripts')
    <script>
        const confirmDeleteProduct = (id) => {
            const form = document.getElementById('deleteProductForm');
            form.action = `{{ route('shop.product.destroy', ':id') }}`.replace(':id', id);
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('You will not be able to revert this!') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#ef4444",
                cancelButtonColor: "#64748b",
                confirmButtonText: "{{ __('Yes, delete it!') }}",
                cancelButtonText: "{{ __('Cancel') }}",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        // ---------- selection and bulk actions ----------
        const updateBulkBar = () => {
            const count = $('.row-check:checked').length;
            $('#bulkCount').text(count);
            $('#bulkForm').toggleClass('d-none', count === 0);
            $('#selectAll').prop('checked', count > 0 && count === $('.row-check').length);
        };
        $(document).on('change', '#selectAll', function() {
            $('.row-check').prop('checked', this.checked);
            updateBulkBar();
        });
        $(document).on('change', '.row-check', updateBulkBar);

        $('#bulkForm').on('submit', function(e) {
            const button = e.originalEvent?.submitter;
            if (!button || !button.hasAttribute('data-confirm-delete') || this.dataset.confirmed) return;
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: "{{ __('Delete the selected products?') }}",
                text: "{{ __('Products that have orders will be skipped and kept.') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: "{{ __('Yes, delete it!') }}",
                cancelButtonText: "{{ __('Cancel') }}",
            }).then((result) => {
                if (!result.isConfirmed) return;
                form.dataset.confirmed = '1';
                $('<input type="hidden" name="action" value="delete">').appendTo(form);
                form.submit();
            });
        });

        // ---------- change a price from the list ----------
        $(document).on('click', '.price-edit', function() {
            const btn = $(this);
            Swal.fire({
                title: "{{ __('Change price') }}",
                html: `
                    <div class="text-start">
                        <label class="form-label">{{ __('Selling Price') }} <small class="text-muted">({{ __('per piece') }})</small></label>
                        <input id="qpPrice" type="number" min="0" step="0.01" class="form-control mb-3" value="${btn.data('price')}">
                        <label class="form-label">{{ __('Carton price after discount') }} <small class="text-muted">({{ __('optional') }})</small></label>
                        <input id="qpDiscount" type="number" min="0" step="0.01" class="form-control" value="${btn.data('discount') || ''}">
                        <small class="text-muted d-block mt-2">{{ __('The carton price is updated too when it was calculated automatically.') }}</small>
                    </div>`,
                showCancelButton: true,
                confirmButtonText: "{{ __('Save') }}",
                cancelButtonText: "{{ __('Cancel') }}",
                focusConfirm: false,
                didOpen: () => document.getElementById('qpPrice').select(),
                preConfirm: () => {
                    const price = document.getElementById('qpPrice').value;
                    const discount = document.getElementById('qpDiscount').value;
                    return $.post(btn.data('url'), { _token: '{{ csrf_token() }}', price, discount_price: discount })
                        .then((response) => ({ ...response, newPrice: price, newDiscount: discount }))
                        .catch((xhr) => {
                            const errors = xhr.responseJSON?.errors;
                            Swal.showValidationMessage(errors ? Object.values(errors)[0][0] : "{{ __('Something went wrong') }}");
                        });
                },
            }).then((result) => {
                if (!result.isConfirmed || !result.value) return;
                const data = result.value;
                const row = btn.closest('tr');
                btn.data('price', data.newPrice).data('discount', data.newDiscount);
                btn.find('.price-main').text(data.price);
                row.find('.carton-price').text(data.discount_price || data.carton_price || '-');
                row.find('.carton-old').text(data.carton_price || '').toggleClass('d-none', !data.discount_price);
                toastr.success(data.message);
            });
        });

        $(".confirmApprove").on("click", function(e) {
            e.preventDefault();
            const url = $(this).attr("href");
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('You want to approve this product') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "{{ __('Yes, Approve it!') }}",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    </script>

    @if ($flashSale)
        <script>
            // Set the start and end date/time
            var startDateAndTime = "{{ $flashSale->start_date }}T{{ $flashSale->start_time }}";
            var endDateAndTime = "{{ $flashSale->end_date }}T{{ $flashSale->end_time }}";
            let startDate = new Date(startDateAndTime).getTime();
            let endDate = new Date(endDateAndTime).getTime();

            // Update the countdown every 1 second
            let countdownInterval = setInterval(() => {
                let now = new Date().getTime();

                // If current time is before the start date, show "Deal Coming" message
                if (now < startDate) {
                    let distanceToStart = startDate - now;

                    // Time calculations for days, hours, minutes, and seconds
                    let days = Math.floor(distanceToStart / (1000 * 60 * 60 * 24));
                    let hours = Math.floor((distanceToStart % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    let minutes = Math.floor((distanceToStart % (1000 * 60 * 60)) / (1000 * 60));
                    let seconds = Math.floor((distanceToStart % (1000 * 60)) / 1000);

                    // Display the countdown with a "Deal Coming" message
                    document.getElementById("days").innerHTML = String(days).padStart(2, '0');
                    document.getElementById("hours").innerHTML = String(hours).padStart(2, '0');
                    document.getElementById("minutes").innerHTML = String(minutes).padStart(2, '0');
                    document.getElementById("seconds").innerHTML = String(seconds).padStart(2, '0');
                    return;
                }

                // Once the current time is after the start date and before the end date, show the active countdown
                let distance = endDate - now;

                // If the deal has ended, stop the countdown and show the message
                if (distance < 0) {
                    clearInterval(countdownInterval);
                    document.getElementById("days").innerHTML = "00";
                    document.getElementById("hours").innerHTML = "00";
                    document.getElementById("minutes").innerHTML = "00";
                    document.getElementById("seconds").innerHTML = "00";
                    document.querySelector(".deal-text").innerHTML = "Deal Ended!";
                    return;
                }

                // Time calculations for days, hours, minutes, and seconds
                let days = Math.floor(distance / (1000 * 60 * 60 * 24));
                let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display the result
                document.getElementById("days").innerHTML = String(days).padStart(2, '0');
                document.getElementById("hours").innerHTML = String(hours).padStart(2, '0');
                document.getElementById("minutes").innerHTML = String(minutes).padStart(2, '0');
                document.getElementById("seconds").innerHTML = String(seconds).padStart(2, '0');
            }, 1000);
        </script>
    @endif
@endpush
@push('css')
    <style>
        /* Flash Deal Alert Styles */
        .flash-deal-alert {
            background: url("{{ asset('assets/images/flash-sale.png') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            border-radius: 8px;
            color: white;
            border-radius: 8px;
            padding: 16px 32px;
        }

        .deal-title,
        .deal-text {
            font-size: 24px;
            font-weight: 600;
            color: white;
            margin: 0;
            line-height: 32px;
        }

        /* Countdown Timer Styles */
        .countdown {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .countdown-section {
            text-align: center;
            padding: 4px 8px;
            border-radius: 8px;
            background-color: white;
            min-width: 68px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .countdown-label {
            font-size: 12px;
            color: #000;
        }

        .countdown-time {
            font-size: 20px;
            font-weight: bold;
            color: var(--theme-color);
        }

        .addBtn {
            border-radius: 25px;
            padding: 10px 20px;
        }
    </style>
@endpush

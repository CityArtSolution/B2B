@extends('layouts.app')
@php
    $segments = [
        'all' => ['label' => __('All'), 'class' => ''],
        'new' => ['label' => __('New (no orders yet)'), 'class' => 'st-confirm'],
        'regular' => ['label' => __('Regular (3+ orders)'), 'class' => 'st-delivered'],
        'inactive' => ['label' => __('Inactive (60+ days)'), 'class' => 'st-pending'],
    ];
    $currentSegment = request('segment', 'all');
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h4 class="page-head-title">{{ __('All Customers') }}</h4>
            <p class="page-head-sub">{{ __('See what each customer buys and find customers who stopped ordering, to contact them with an offer.') }}</p>
        </div>
        @hasPermission('admin.customer.create')
            <div class="page-head-actions">
                <a href="{{ route('admin.customer.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> {{ __('Add Customer') }}
                </a>
            </div>
        @endhasPermission
    </div>

    <div class="status-tabs mb-3">
        @foreach ($segments as $key => $segment)
            <a href="{{ route('admin.customer.index', array_filter(['segment' => $key === 'all' ? null : $key, 'search' => request('search')])) }}"
                class="status-tab {{ $segment['class'] }} {{ $currentSegment === $key ? 'active' : '' }}">
                @if ($segment['class'])
                    <span class="status-dot"></span>
                @endif
                {{ $segment['label'] }}
                <span class="status-tab-count">{{ $segmentCounts[$key] }}</span>
            </a>
        @endforeach
    </div>

    <div class="container-fluid p-0">

        <div class="mb-3 card">
            <div class="card-body p-0">
                <form method="GET" class="d-flex gap-2 flex-wrap p-3 border-bottom">
                    <div class="input-icon flex-grow-1" style="max-width: 420px">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="{{ __('Name, phone or commercial register') }}">
                    </div>
                    @if (request('segment'))
                        <input type="hidden" name="segment" value="{{ request('segment') }}">
                    @endif
                    <button class="btn btn-primary"><i class="bi bi-search"></i> {{ __('Search') }}</button>
                    @if (request('search'))
                        <a href="{{ route('admin.customer.index', array_filter(['segment' => request('segment')])) }}" class="btn btn-link text-muted">{{ __('Clear filters') }}</a>
                    @endif
                </form>
                <div class="table-responsive">
                    <table class="table table-modern cards-on-mobile mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 220px">{{ __('Customer') }}</th>
                                <th>{{ __('Commercial register') }}</th>
                                <th class="text-center">{{ __('Orders') }}</th>
                                <th>{{ __('Total purchases') }}</th>
                                <th>{{ __('Last order') }}</th>
                                <th class="text-end">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($customers as $key => $customer)
                            @php
                                $profile = $customer->customer;
                                $ordersCount = (int) ($profile?->orders_count ?? 0);
                                $lastOrder = $profile?->last_order_at ? \Carbon\Carbon::parse($profile->last_order_at) : null;
                                $segmentKey = $ordersCount === 0 ? 'new' : ($lastOrder && $lastOrder->lt($inactiveSince) ? 'inactive' : ($ordersCount >= 3 ? 'regular' : null));
                            @endphp
                            <tr>
                                <td data-label="{{ __('Customer') }}">
                                    <div class="d-flex align-items-center gap-2 text-start">
                                        <img src="{{ $customer->thumbnail }}" width="42" height="42" class="rounded-circle object-fit-cover flex-shrink-0" alt="" loading="lazy">
                                        <div class="min-w-0">
                                            <div class="fw-semibold">
                                                {{ Str::limit($customer->fullName, 40, '...') }}
                                                @if ($segmentKey)
                                                    <span class="status-badge {{ $segments[$segmentKey]['class'] }} ms-1" style="font-size: .7rem">{{ ['new' => __('New customer'), 'regular' => __('Regular customer'), 'inactive' => __('Inactive customer')][$segmentKey] }}</span>
                                                @endif
                                            </div>
                                            <div class="text-muted small" dir="ltr">{{ $customer->phone ?? '--' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="{{ __('Commercial register') }}">
                                    {{ $customer->Commercial_register ?? '--' }}
                                    @if ($customer->Tax_number)
                                        <div class="text-muted small">{{ __('Tax number') }}: {{ $customer->Tax_number }}</div>
                                    @endif
                                </td>
                                <td data-label="{{ __('Orders') }}" class="text-center fw-bold">{{ $ordersCount }}</td>
                                <td data-label="{{ __('Total purchases') }}" class="fw-bold">{{ showCurrency($profile?->total_spent ?? 0) }}</td>
                                <td data-label="{{ __('Last order') }}">
                                    @if ($lastOrder)
                                        {{ $lastOrder->diffForHumans() }}
                                        <div class="text-muted small">{{ $lastOrder->format('d M Y') }}</div>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        @hasPermission('admin.customer.account-statement')
                                            <a href="{{ route('admin.customer.account-statement', $customer->id) }}"
                                                class="btn btn-outline-secondary circleIcon" data-bs-toggle="tooltip"
                                                data-bs-placement="left" data-bs-title="{{ __('View Account Statement') }}">
                                                <i class="bi bi-file-earmark-text"></i>
                                            </a>
                                        @endhasPermission
                                        @hasPermission('admin.customer.edit')
                                            <a href="{{ route('admin.customer.edit', $customer->id) }}"
                                                class="btn btn-outline-primary circleIcon" data-bs-toggle="tooltip"
                                                data-bs-placement="left" data-bs-title="{{ __('Edit') }}">
                                                <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit"
                                                    loading="lazy" />
                                            </a>
                                        @endhasPermission

                                        @hasPermission('admin.customer.destroy')
                                            <a href="{{ route('admin.customer.destroy', $customer->id) }}"
                                                class="btn btn-outline-danger circleIcon deleteConfirm" data-bs-toggle="tooltip"
                                                data-bs-placement="left" data-bs-title="{{ __('Delete') }}">
                                                <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="delete"
                                                    loading="lazy" />
                                            </a>
                                        @endhasPermission

                                        @hasPermission('admin.customer.reset-password')
                                            <!--<button type="button" class="btn btn-outline-info circleIcon"-->
                                            <!--    data-bs-toggle="tooltip" data-bs-placement="left"-->
                                            <!--    data-bs-title="{{ __('Reset Password') }}"-->
                                            <!--    onclick="openResetPasswordModal('{{ $customer->id }}','{{ $customer->fullName }}')">-->
                                            <!--    <img src="{{ asset('assets/icons-admin/role-permission.svg') }}" alt="key"-->
                                            <!--        loading="lazy" />-->
                                            <!--</button>-->
                                        @endhasPermission
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%">
                                    <div class="empty-state">
                                        <i class="bi bi-people"></i>
                                        <h6>{{ __('No customers found') }}</h6>
                                        <p>{{ __('Try changing or clearing the filters.') }}</p>
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
            {{ $customers->withQueryString()->links() }}
        </div>

        <form action="" method="POST" id="resetPasswordForm">
            @csrf
            <div class="modal fade" id="resetPasswordModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title fs-5">{{ __('Reset Password') }} <span id="userName"></span></h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="password1" class="form-label">
                                    {{ __('Password') }}
                                </label>
                                <div class="position-relative passwordInput">
                                    <input type="password" name="password" id="password1" class="form-control"
                                        required="true" placeholder="{{ __('Enter Password') }}">
                                    <span class="eye" onclick="showHidePassword(1)">
                                        <i class="fa fa-eye-slash" id="togglePassword1"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <p class="text text-danger m-0">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password2" class="form-label">
                                    {{ __('Confirm Password') }}
                                </label>
                                <div class="position-relative passwordInput">
                                    <input type="password" name="password_confirmation" id="password2" class="form-control"
                                        required="true" placeholder="{{ __('Enter Password again') }}">
                                    <span class="eye" onclick="showHidePassword(2)">
                                        <i class="fa fa-eye-slash" id="togglePassword2"></i>
                                    </span>
                                </div>
                                <span id="passwordMatch" class="text text-danger d-none"></span>
                                @error('password_confirmation')
                                    <p class="text text-danger m-0">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                {{ __('Close') }}
                            </button>
                            <button type="submit" id="submit" class="btn btn-primary">
                                {{ __('Save changes') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection
@push('scripts')
    <script>
        function openResetPasswordModal(userId, userName) {
            $('#resetPasswordModal').modal('show');
            $('#userName').html('(' + userName + ')');
            $('#resetPasswordForm').attr('action', `{{ route('admin.customer.reset-password', ':id') }}`.replace(':id',
                userId));
        }

        function showHidePassword(num) {
            const toggle = document.getElementById("togglePassword" + num);
            const password = document.getElementById("password" + num);

            // toggle the type attribute
            const type = password.getAttribute("type") === "password" ? "text" : "password";
            password.setAttribute("type", type);
            // toggle the icon
            toggle.classList.toggle("fa-eye");
            toggle.classList.toggle("fa-eye-slash");
        }

        document.getElementById('password2').addEventListener('keyup', function(e) {
            $password1 = document.getElementById('password1').value;
            $password2 = document.getElementById('password2').value;

            $message = document.getElementById('passwordMatch');

            if ($password1 == $password2) {
                document.getElementById('password2').classList.remove('is-invalid');
                $message.classList.add('d-none');
                document.getElementById('submit').disabled = false;
            } else {
                document.getElementById('password2').classList.add('is-invalid');
                $message.classList.remove('d-none');
                $message.innerHTML = "Password doesn't match";
                document.getElementById('submit').disabled = true;
            }
        });
    </script>
@endpush

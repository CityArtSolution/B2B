@extends('layouts.app')

@section('title', __('New client'))

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">{{ __('New client') }}</h3>
    </div>

    {{-- Customers Table --}}
    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Paid Invoices') }}</th>
                            <th>{{ __('Unpaid Invoices') }}</th>
                            <th>{{ __('Credit Limit') }}</th>
                            <th>{{ __('account statement') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <small class="text-muted">ID: {{ $user->id }}</small>
                                </td>

                                <td>
                                    <a href="https://wa.me/+966{{ $user->phone }}" target="_blank">
                                        {{ $user->phone }}
                                    </a>
                                </td>

                                <td class="text-success fw-semibold">
                                    {{ number_format($user->paid_invoices_total, 2) }}
                                </td>

                                <td class="text-danger fw-semibold">
                                    {{ number_format($user->unpaid_invoices_total, 2) }}
                                </td>

                                <td>
                                    <form action="{{route('admin.CustomerEngagement.update-limit' , $user->id)}}"
                                          method="POST"
                                          class="d-flex gap-2 align-items-center">
                                        @csrf
                                        @method('PUT')

                                        <input type="number"
                                               name="maximum_invoices_total"
                                               value="{{ $user->maximum_invoices_total }}"
                                               min="0"
                                               class="form-control form-control-sm"
                                               style="width:120px">

                                        <button class="btn btn-sm btn-success">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                </td>
                                
                                <td class="text-center">
                                    <a href="{{ route('admin.customer.account-statement', $user->id) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="{{ __('View Account Statement') }}">
                                        <i class="bi bi-file-text"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    {{ __('No customers found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
@endsection
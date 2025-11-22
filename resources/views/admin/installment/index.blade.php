@extends('layouts.app')
@section('header-title', __('Installments'))

@section('content')
<div class="d-flex align-items-center flex-wrap gap-3 justify-content-between px-3">
    <h4>{{ __('Manage Installments') }}</h4>

    <a href="{{ route('admin.installment.create') }}" class="btn py-2 btn-primary">
        <i class="fa fa-plus-circle"></i> {{ __('Add Installment') }}
    </a>
</div>

<div class="container-fluid mt-3">
    <div class="my-3 card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table border-left-right table-responsive-lg">
                    <thead>
                        <tr>
                            <th>{{ __('SL') }}</th>
                            <th class="text-center">{{ __('Name') }}</th>
                            <th class="text-center">{{ __('Value') }}</th>
                            <th class="text-center">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installments as $installment)
                        <tr>
                            <td>{{ $loop->iteration }}.</td>
                            <td class="text-center">{{ $installment->name }}</td>
                            <td class="text-center">{{ $installment->value }}</td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('admin.installment.edit', $installment->id) }}" class="btn btn-outline-info btn-sm circleIcon">
                                        <img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" />
                                    </a>
                                    <a href="{{ route('admin.installment.destroy', $installment->id) }}" class="btn btn-outline-danger btn-sm deleteConfirm circleIcon">
                                        <img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="delete" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-center" colspan="100%">{{ __('No Data Found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="my-3">
        {{ $installments->links() }}
    </div>
</div>
@endsection

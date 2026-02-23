@extends('layouts.app')

@section('title',  __('Customer Interests'))

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">{{ __('Customer Interests') }}</h4>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Visits') }}</th>
                        <th>{{ __('Time Spent (sec)') }}</th>
                        <th>{{ __('Favorite') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>
                                <strong>{{ $record->user->name ?? 'Guest' }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $record->user->phone ?? '-' }}
                                </small>
                            </td>

                            <td>
                                {{ $record->product->translated_name->name ?? 'Deleted Product' }} RN{{ $record->product->code }}
                            </td>

                            <td>
                                <span class="badge bg-primary">
                                    {{ $record->visit_count }}
                                </span>
                            </td>

                            <td>
                                {{ round($record->total_time, 1) }}
                            </td>

                            <td>
                                @if($record->is_favorite)
                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('No') }}</span>
                                @endif
                            </td>

                            <td class="d-flex gap-2">
                                <!-- Apply Discount -->
                                <a href="{{ route('admin.coupon.create', ['req' => $record->id]) }}">
                                    {{ __('Add Discount') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No interested users yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

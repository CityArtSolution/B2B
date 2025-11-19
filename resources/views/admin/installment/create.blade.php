@extends('layouts.app')
@section('header-title', __('Add Installment'))

@section('content')
<form action="{{ route('admin.installment.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-xl-9 mx-auto">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <x-input label="Name" name="name" type="text" placeholder="Enter Installment Name" required="true"/>
                        </div>
                        <div class="col-md-6">
                            <x-input label="Value" name="value" type="text" placeholder="Enter Value" required="true"/>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-end mt-4">
                        <button class="btn btn-primary py-2 px-5">{{__('Submit')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

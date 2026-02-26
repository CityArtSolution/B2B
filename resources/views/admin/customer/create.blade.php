@extends('layouts.app')

@section('content')
<style>
    /* ================= FORM INPUT ================= */
.input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    font-size: 14px;
    background-color: #fff;
    transition: all 0.2s ease-in-out;
}

.input::placeholder {
    color: #adb5bd;
}

.input:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
}

/* ================= ADDRESS CARD ================= */
.address-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 14px;
    padding: 24px;
    margin-top: 30px;
}

/* ================= ADDRESS HEADER ================= */
.address-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f3f5;
    padding-bottom: 12px;
    margin-bottom: 20px;
}

.address-header h5 {
    margin: 0;
    font-weight: 600;
    color: #212529;
}

/* ================= CHECKBOX ================= */
.form-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #495057;
}

.form-checkbox input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

/* ================= IMAGE PREVIEW ================= */
.ratio1x1 {
    border-radius: 14px;
    overflow: hidden;
    border: 1px dashed #dee2e6;
    background: #f8f9fa;
}

/* ================= CARD SHADOW ================= */
.shadow-md {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

</style>
    <div class="container-fluid my-md-0 my-4">
        <form action="{{ route('admin.customer.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row h-100vh">
                <div class="col-12 m-auto">
                    <div class="card rounded-12 border-0 shadow-md">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                            <h3 class="m-0">{{ __('Add Customer') }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-7">
                                    <div class="row">
                                        <div class="col-md-6">
                                                <div class="mt-3">
                                                    <x-input label="First Name" name="name" type="text"
                                                        placeholder="Enter Name" required="true" />
                                                </div>
                                            </div>
                                        <div class="col-md-6">
                                            <div class="mt-3">
                                                <x-input label="Phone Number" name="phone" type="number"
                                                placeholder="Enter phone number" required="true"/>
                                            </div>
                                        </div>
                                        
                                    </div>


                                    <!--<div class="mt-3">-->
                                    <!--    <x-input type="email" name="email" label="Email" placeholder="Enter Email Address" />-->
                                    <!--</div>-->

                                    <!--<div class="row">-->
                                    <!--    <div class="col-md-6 mt-3">-->
                                    <!--        <x-input type="password" name="password" label="Password" placeholder="Enter Password" required="true" />-->
                                    <!--    </div>-->

                                    <!--    <div class="col-md-6 mt-3">-->
                                    <!--        <x-input type="password" name="password_confirmation" label="Confirm Password"-->
                                    <!--            placeholder="Enter Confirm Password" required="true" />-->
                                    <!--    </div>-->
                                    <!--</div>-->

                                    <div class="mt-3" style="display: flex;align-items: baseline;gap: 19px;">
                                        <div class="col-md-6 mt-3">
                                            <x-input type="text" name="Commercial_register" label="Commercial register"
                                                placeholder="Enter Commercial register" required="true" />
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <x-input type="text" name="country" label="client country"
                                                placeholder="Enter client country" required="true" />
                                        </div>
                                    </div>
                                    <div class="mt-3" style="display: flex;align-items: baseline;gap: 19px;">
                                        <div class="col-md-6 mt-3">
                                            <x-input type="text" name="Additional_Number" label="Additional Number"
                                                placeholder="Enter Additional Number" required="true" />
                                        </div>
                                        <div class="col-md-6 mt-3">
                                            <x-input type="text" name="Tax_number" label="Tax number"
                                                placeholder="Enter Tax number" required="true" />
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <x-select label="{{ __('Payment Status') }}" name="payment_status" required="true">
                                            <option value="New_client">{{ __('New client') }}</option>
                                            <option value="Previous_client">{{ __('Previous client') }}</option>
                                        </x-select>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="mt-3 d-flex align-items-center justify-content-center">
                                        <div class="ratio1x1">
                                            <img id="previewProfile" src="https://placehold.co/500x500/png" alt="photo" width="100%">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <x-file name="profile_photo" label="User profile (Ratio 1:1)"
                                            preview="previewProfile" />
                                    </div>

                                    <!--<div class="mt-3">-->
                                    <!--    <x-input type="date" name="date_of_birth" label="Date of Birth"-->
                                    <!--    placeholder="Enter Date of Birth" />-->
                                    <!--</div>-->
                                </div>
                        </div>
                            <div class="p-6 bg-white rounded-2xl border border-slate-200">
                                <div class="address-card">
                                    <div class="address-header">
                                        <h5>{{ __('address') }}</h5>
                                    </div>
                                
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <input name="address[area]" value="{{ old('address.area') }}"
                                                   type="text" placeholder="{{ __('Area') }}" class="input">
                                        </div>
                                
                                        <div class="col-md-6">
                                            <input name="address[neighborhood]" value="{{ old('address.neighborhood') }}"
                                                   type="text" placeholder="{{ __('Neighborhood') }}" class="input">
                                        </div>
                                
                                        <div class="col-md-6">
                                            <input name="address[flat_no]" value="{{ old('address.flat_no') }}"
                                                   type="text" placeholder="{{ __('Flat') }}" class="input">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <input name="address[Postal]" value="{{ old('address.Postal') }}"
                                                   type="text" placeholder="{{ __('Postal Code') }}" class="input">
                                        </div>
                                
                                        <div class="col-md-12">
                                            <input name="address[address_line]" value="{{ old('address.address_line') }}"
                                                   type="text" placeholder="{{ __('Address Line 1') }}" class="input">
                                        </div>
                                
                                        <div class="col-md-12">
                                            <input name="address[address_line2]" value="{{ old('address.address_line2') }}"
                                                   type="text" placeholder="{{ __('Address Line 2') }}" class="input">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="{{ route('admin.customer.index') }}" class="btn btn-lg btn-outline-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-lg btn-primary">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@extends('layouts.app')
@section('header-title', __('Create New Category'))

@section('content')
    <div class="page-title">
        <div class="d-flex gap-2 align-items-center">
            <i class="fa-solid fa-border-all"></i> {{__('Create New Category')}}
        </div>
    </div>
    <form action="{{ route('admin.category.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-8 m-auto">
                <div class="card mt-3">
                    <div class="card-body">

                        <div class="d-flex gap-2 border-bottom pb-2">
                            <i class="fa-solid fa-user"></i>
                            <h5>
                                {{__('Category Information')}}
                            </h5>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mt-3">
                                <x-input label="Name (AR)" name="name_ar" id="name_ar_input" type="text" placeholder="Enter Name" required="true"/>
                            </div>
                            <div class="col-md-6 mt-3">
                                <x-input label="Name (EN)" name="name" id="product_name" type="text" placeholder="Enter Name" required="true"/>
                            </div>
                            <div class="col-md-3 mt-3" style="display: none">
                                <x-input label="Name (UR)" name="name_ur" id="name_ur_input" type="text" placeholder="Enter Name" required="true"/>
                            </div>
                            <div class="col-md-3 mt-3" style="display: none">
                                <x-input label="Name (IN)" name="name_in" id="name_in_input" type="text" placeholder="Enter Name" required="true"/>
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center justify-content-center">
                            <div class="ratio1x1">
                                <img id="previewProfile" src="https://placehold.co/500x500/f1f5f9/png" alt="" width="100%">
                            </div>
                        </div>
                        <div class="mt-3">
                            <x-file name="thumbnail" label="Thumbnail (Ratio 1:1)" preview="previewProfile" required="true"/>
                        </div>
                        
                        <div class="mt-4">
                            <label for="description" class="form-label">
                                {{__('Description')}}
                            </label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                        </div>


                        <div class="mt-5 d-flex gap-2 justify-content-between">
                            <a href="{{ route('admin.category.index')}}" class="btn btn-secondary py-2 px-4">
                            {{__('Back')}}
                            </a>

                            <button type="submit" class="btn btn-primary py-2 px-4">
                                {{__('Submit')}}
                            </button>

                        </div>

                    </div>

                </div>
            </div>
        </div>
    </form>
    @push('scripts')
    <script>
        $(document).on('input', '#name_ar_input', function () {
            let value = $(this).val();
        
            $.ajax({
                url: '/admin/translate',
                method: 'GET',
                data: {
                    text: value,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                $('#product_name').val(response.en);
                $('#name_ur_input').val(response.ur);
                $('#name_in_input').val(response.in);
                }
            });
        });
    </script>
    @endpush
@endsection

@extends('layouts.admin.app')

@section('title',translate('Food_Bulk_Export'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">

        <div class="page-header">
            <h1 class="page-header-title text-capitalize">
                <div class="card-header-icon d-inline-flex mr-2 img">
                    <img src="{{dynamicAsset('/public/assets/admin/img/export.png')}}" alt="">
                </div>
                {{ translate('Export_Foods') }}
            </h1>
        </div>

        <div class="card mt-2 rest-part">
            <div class="card-body">
                <div class="export-steps">
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{ translate('STEP_1') }}</h5>
                            <p>
                                {{ translate('Select_Data_Type') }}
                            </p>
                        </div>
                    </div>
                    <div class="export-steps-item">
                        <div class="inner">
                            <h5>{{ translate('STEP_2') }}</h5>
                            <p>
                                {{ translate('Select_Data_Range_by_Date_and_Export') }}
                            </p>
                        </div>
                    </div>
                </div>
                <form class="product-form" action="{{route('admin.food.bulk-export')}}" method="POST"
                        enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label" for="exampleFormControlSelect1">{{translate('messages.type')}}<span
                                        class="input-label-secondary"></span></label>
                                <select name="type" id="type" data-placeholder="{{translate('messages.select_type')}}" class="form-control" required title="Select Type">
                                    <option value="all">{{translate('messages.all_data')}}</option>
                                    <option value="date_wise">{{translate('messages.date_wise')}}</option>
                                    <option value="id_wise">{{translate('messages.id_wise')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group id_wise">
                                <label class="form-label" for="exampleFormControlSelect1">{{translate('messages.start_id')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="number" name="start_id" class="form-control" placeholder="{{translate('messages.example')}}: 1">
                            </div>
                            <div class="form-group date_wise">
                                <label class="form-label" for="exampleFormControlSelect1">{{translate('messages.from_date')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="date" name="from_date"  id="date_from" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group id_wise">
                                <label class="form-label" for="exampleFormControlSelect1">{{translate('messages.end_id')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="number" name="end_id" class="form-control" placeholder="{{translate('messages.example')}}: 10">
                            </div>
                            <div class="form-group date_wise">
                                <label class="input-label text-capitalize" for="exampleFormControlSelect1">{{translate('messages.to_date')}}<span
                                        class="input-label-secondary"></span></label>
                                <input type="date" name="to_date" id="date_to" class="form-control" placeholder="{{translate('messages.example')}}: 2025-11-11">
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button class="btn btn--reset" type="reset">{{translate('messages.reset')}}</button>
                        <button class="btn btn--primary" type="submit">{{translate('messages.export')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";
        $(document).on('ready', function () {
            const today = (new Date()).toISOString().split('T')[0];
            $('#date_from, #date_to').attr('max', today);

            $('.id_wise').hide();
            $('.date_wise').hide();

            $('#type').on('change', function () {
                $('.id_wise').hide();
                $('.date_wise').hide();
                $('.' + $(this).val()).show();
            });

            // Date validation
            $('#date_from, #date_to').on('change', function () {
                const fromDate = $('#date_from').val();
                const toDate = $('#date_to').val();

                if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                    toastr.error("{{ translate('messages.from_date_cannot_be_greater_than_to_date') }}");
                    $('#date_from').val('');
                }
            });

            // Reset button logic
            $('button[type="reset"]').on('click', function () {
                $('#type').val('all').trigger('change');
                $('#date_from').val('');
                $('#date_to').val('');
                $('input[name="start_id"], input[name="end_id"]').val('');
            });
        });
    </script>
@endpush

@extends('layouts.vendor.app')

@section('title',translate('messages.restaurant_wallet'))

@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
     <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{dynamicAsset('/public/assets/admin/img/image_90.png')}}" alt="public">
                        </div>
                        <span>
                            {{translate('messages.withdraw_method_setup')}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
<!-- End Page Header -->
    <!-- Card -->
    <div class="card">
        <div class="card-header flex-wrap gap-2 border-0 pt-2 pb-0">
            <div class="search--button-wrapper flex-wrap gap-2">
                <h3 class="card-title">
                    {{ translate('withdrawal_methods') }} &nbsp; <span class="badge badge-soft-secondary"
                                                              id="countfoods">{{ $vendor_withdrawal_methods->total() }}</span>
                </h3>
                <form >

                    <!-- Search -->
                    <div class="input-group input--group">
                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{ translate('Ex : Search by name') }}"  value="{{ request()?->search ?? null }}" aria-label="Search">
                        <button type="submit" class="btn btn--secondary">
                            <i class="tio-search"></i>
                        </button>
                    </div>
                    <!-- End Search -->
                </form>

            </div>
            <div class="p--10px">
                <a class="btn btn--primary btn-outline-primary w-100" href="javascript:" data-toggle="modal" data-target="#balance-modal">{{translate('messages.add_new_method')}}</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="datatable"
                       class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table" data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true,
                            "paging":false
                        }'>
                    <thead class="thead-light">
                    <tr>
                          <th>{{ translate('messages.sl')}}</th>
                        <th>{{ translate('messages.Payment_method_name')}}</th>
                        <th>{{  translate('messages.method_fields') }}</th>
                        <th class="text-center">{{ translate('messages.active_status')}}</th>
                        <th class="text-center">{{ translate('messages.default_method')}}</th>
                        <th class="text-center">{{ translate('messages.action')}}</th>
                    </tr>
                    </thead>
                    <tbody id="set-rows">

                    @foreach($vendor_withdrawal_methods as $key=>$withdrawal_method)
                                <tr>
                                    <td class="p-3">{{$vendor_withdrawal_methods->firstitem()+$key}}</td>
                                    <td class="p-3">{{$withdrawal_method['method_name']}}</td>
                                    <td class="p-3">
                                            <div class="more-withdraw-list">
                                                <div class="more-withdraw-inner">
                                                    @foreach(array_slice(json_decode($withdrawal_method['method_fields'], true), 0, 3) as $key=>$method_field)
                                                        <span class="text-title more-withdraw-item fs-14">
                                                            <span class="mb-1 d-inline-block px-1">
                                                                <span>{{ translate($key)}}:</span> <span class="gray-dark">{{ $method_field ?? translate('N/A')}} </span>
                                                            </span>
                                                        </span>
                                                        <br/>
                                                    @endforeach
                                                </div>
                                                @if(count(json_decode($withdrawal_method['method_fields'], true)) > 1)
                                                    <button type="button"
                                                            class="see__more btn p-0 border-0 bg-transparent text-primary fs-12 font-medium offcanvas-trigger"
                                                            data-target="#withdraw_method-offcanvas"
                                                            data-id="{{ $withdrawal_method->id }}"
                                                            data-name="{{ $withdrawal_method['method_name'] }}"
                                                            data-is_default="{{ $withdrawal_method['is_default'] == 1 ? 'Default' : '' }}"
                                                            data-is_active="{{ $withdrawal_method['is_active'] }}"
                                                            data-action="{{ route('vendor.wallet-method.edit',$withdrawal_method->id) }}"
                                                            data-fields='{{ $withdrawal_method['method_fields'] }}'
                                                            data-created_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->created_at) }}"
                                                            data-updated_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->updated_at) }}">
                                                        {{translate('See More')}}
                                                    </button>
                                                @endif
                                            </div>
                                        </td>

                                    <td class="text-center p-3">
                                        <label class="toggle-switch mx-auto toggle-switch-sm">
                                            <input class="toggle-switch-input status featured-status"
                                                   data-id="{{$withdrawal_method->id}}"
                                                   type="checkbox" {{$withdrawal_method->is_active?'checked':''}}>
                                                   <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                        </label>
                                    </td>
                                    <td class="text-center p-3">
                                        <label class="toggle-switch mx-auto toggle-switch-sm">
                                            <input type="checkbox" class="default-method toggle-switch-input"
                                            id="{{$withdrawal_method->id}}" {{$withdrawal_method->is_default == 1?'checked':''}}>
                                                   <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                        </label>
                                    </td>

                                    <td class="p-3">
                                        <div class="btn--container justify-content-center">
                                           <a class="btn action-btn btn--primary btn-outline-primary see__more offcanvas-trigger"

                                                            data-target="#withdraw_method-offcanvas"
                                                            data-id="{{ $withdrawal_method->id }}"
                                                            data-name="{{ $withdrawal_method['method_name'] }}"
                                                            data-is_default="{{ $withdrawal_method['is_default'] == 1 ? 'Default' : '' }}"
                                                            data-is_active="{{ $withdrawal_method['is_active'] }}"
                                                            data-action="{{ route('vendor.wallet-method.edit',$withdrawal_method->id) }}"
                                                            data-fields='{{ $withdrawal_method['method_fields'] }}'
                                                            data-created_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->created_at) }}"
                                                            data-updated_at="{{ \App\CentralLogics\Helpers::time_date_format($withdrawal_method->updated_at) }}"

                                           href="javascript:;"><i class="tio-visible-outlined"></i></a>

                                            <a href="javascript:" data-url="{{route('vendor.wallet-method.edit',[$withdrawal_method->id])}}" data-id="{{$withdrawal_method->id}}"

                                                data-target="#withdraw_method_edit-offcanvas"
                                                class="btn btn-sm btn--primary btn-outline-primary offcanvas-trigger offcanvas-trigger-edit action-btn">
                                                <i class="tio-edit"></i>
                                            </a>

                                            @if(!$withdrawal_method->is_default)
                                                <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
                                                   title="{{ translate('messages.Delete')}}"
                                                   data-id="delete-{{$withdrawal_method->id}}" data-message="{{ translate('Want to delete this item') }}">
                                                    <i class="tio-delete-outlined"></i>
                                                </a>
                                                <form action="{{route('vendor.wallet-method.delete',[$withdrawal_method->id])}}"
                                                      method="post" id="delete-{{$withdrawal_method->id}}">
                                                    @csrf @method('delete')
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                    </tbody>
                </table>
                @if(count($vendor_withdrawal_methods) === 0)
                    <div class="empty--data">
                        <img src="{{dynamicAsset('/public/assets/admin/img/empty.png')}}" alt="public">
                        <h5>
                            {{translate('no_data_found')}}
                        </h5>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-footer">
            <div class="page-area">
                <table>
                    <tfoot>
                    {!! $vendor_withdrawal_methods->links() !!}
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <!-- Card -->


    <div class="modal fade" id="balance-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        {{translate('messages.add_method')}}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="btn btn--circle btn-soft-danger text-danger"><ti class="tio-clear"></ti></span>
                    </button>
                </div>
                <form action="{{route('vendor.wallet-method.store')}}" method="post">
                    <div class="modal-body">
                        @csrf
                        <div class="">
                            <select class="form-control" id="withdraw_method" name="withdraw_method" required>
                                <option value="" selected disabled>{{translate('Select_Withdraw_Method')}}</option>
                                @foreach($withdrawal_methods as $item)
                                    <option value="{{$item['id']}}">{{$item['method_name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="" id="method-filed__div">
                        </div>
                    </div>
                    <div class="modal-footer pt-0 border-0">
                        <button type="button" class="btn btn--reset" data-dismiss="modal">{{translate('messages.cancel')}}</button>
                        <button type="submit" id="submit_button" disabled class="btn btn--primary">{{translate('messages.Submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Saved Address Offcanvas -->
    <div id="withdraw_method-offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between" style="--offcanvas-width: 500px">
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Withdrawal Method Information') }}</h2>
                    </div>
                    <button type="button"
                            class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                            aria-label="Close">&times;
                    </button>
                </div>
            </div>

            <div class="custom-offcanvas-body p-20">
                <div class="d-flex flex-column gap-20px">
                    <div class="global-bg-box p-10px rounded mb-3">
                        <div class="bg-white rounded-8 border p-xxl-3 p-2">
                            <div class="d-flex justify-content-between gap-2 flex-wrap mb-3">
                                <div>
                                    <h5 class="text-secondary fw-400 mb-1 fs-12">
                                        {{ translate('Method Name') }}
                                    </h5>
                                    <h5 class="text-title mb-0 fs-16">
                                        <span id="method-title"></span> <span id="method-is-default"></span>
                                    </h5>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="border rounded d-flex py-1 px-2 gap-4 justify-content-between align-items-center">
                                        <h4 class="text-capitalize mb-0 fs-12">{{ translate('messages.Status') }}</h4>
                                        <label class="toggle-switch toggle-switch-sm">
                                            <input type="checkbox" id="offcanvas-status" class="status toggle-switch-input" data-id="">
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn offcanvas-delete-btn" href="javascript:" data-id="" title="{{ translate('messages.Delete')}}" style="display:none;">
                                        <i class="tio-delete-outlined"></i>
                                    </a>
                                </div>
                            </div>
                                <p class="text-secondary fs-12 m-0">{{ translate('Created At') }} : <span id="method-created-at"></span></p>
                                <p class="text-secondary fs-12 m-0">{{ translate('Last Modified At') }} : <span id="method-updated-at"></span></p>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-20px" id="method-fields-container">
                </div>
            </div>
        </div>

        <div class="align-items-center bg-white bottom-0 d-flex gap-3 justify-content-center offcanvas-footer p-3 position-sticky">
            <a href="#" id="editMethodBtn" data-target="#withdraw_method_edit-offcanvas" class="btn w-100 btn--secondary offcanvas-trigger offcanvas-trigger-edit">{{ translate('Edit Method') }}</a>
            <a href="#" id="mark" class="btn w-100 btn--primary" style="display:none;">{{ translate('Mark As Default') }}</a>
        </div>
    </div>



    <div id="withdraw_method_edit-offcanvas" class="custom-offcanvas d-flex flex-column justify-content-between" style="--offcanvas-width: 500px">
        <div>
            <div class="custom-offcanvas-header d-flex justify-content-between align-items-center">
                <div class="px-3 py-3 d-flex justify-content-between w-100">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h2 class="mb-0 fs-18 text-title font-medium">{{ translate('Update Withdrawal Method') }}</h2>
                    </div>
                    <button type="button"
                        class="btn-close w-25px h-25px border rounded-circle d-center bg--secondary offcanvas-close fz-15px p-0"
                        aria-label="Close">&times;
                    </button>
                </div>
            </div>

            <div id="data-view"> </div>
        </div>
    </div>

    <div id="offcanvasOverlay" class="offcanvas-overlay"></div>



</div>
@endsection
@push('script')
<script>
    "use strict";
    $('#withdraw_method').on('change', function () {
        $('#submit_button').attr("disabled","true");
        let method_id = this.value;

        // Set header if need any otherwise remove setup part
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('vendor.wallet.method-list')}}" + "?method_id=" + method_id,
            data: {},
            processData: false,
            contentType: false,
            type: 'get',
            success: function (response) {
                $('#submit_button').removeAttr('disabled');
                let method_fields = response.content.method_fields;
                $("#method-filed__div").html("");
                method_fields.forEach((element, index) => {
                    $("#method-filed__div").append(`
                    <div class="form-group mt-2">
                        <label for="wr_num" class="fz-16 c1 mb-2">${element.input_name.replaceAll('_', ' ').toUpperCase()}</label>
                        <input type="${element.input_type == 'phone' ? 'number' : element.input_type  }" class="form-control" name="${element.input_name}" placeholder="${element.placeholder}" ${element.is_required === 1 ? 'required' : ''}>
                    </div>
                `);
                })

            },
            error: function () {

            }
        });
    });

    function showMyModal(data) {
        $(".modal-body #hiddenValue").html(data);
        $('#exampleModal').modal('show');
    }


        $(document).on('change', '.default-method', function () {
          let id = $(this).attr("id");
           updateDefault(id);
      });


      $(document).on('click', '.featured-status', function() {
          let id = $(this).data('id');
          statusToggle(id);
      })

        $(document).on('click', '.see__more', function() {
          let methodId = $(this).data('id');
          let methodName = $(this).data('name');
          let isActive = $(this).data('is_active');
          let isDefault = $(this).data('is_default');
          let action = $(this).data('action');
          let fields = $(this).data('fields');
          let createdAt = $(this).data('created_at');
          let updatedAt = $(this).data('updated_at');

          function formatText(text) {
              if (!text) return '';
              return text
                  .replace(/_/g, ' ')
                  .toLowerCase()
                  .replace(/\b\w/g, char => char.toUpperCase());
          }

          // Set status toggle and delete button data-id
          $('#offcanvas-status').data('id', methodId).prop('checked', isActive == 1);
          var isDefaultBool = (isDefault.toLowerCase() === 'default');
          if (!isDefaultBool) {
              $('.offcanvas-delete-btn').data('id', methodId).show();
              $('#mark').show();
              $('#method-is-default').text('');
          } else {
              $('#method-is-default').text('(' + formatText(isDefault) + ')');
              $('.offcanvas-delete-btn').hide();
              $('#mark').hide();
          }

          let statusBadge = isActive
              ? '<span class="btn fs-10 py-1 px-2 lh-1 badge--success">' + "Active" + '</span>'
              : '<span class="btn fs-10 py-1 px-2 lh-1 bg-danger-opacity5">' + "Inactive" + '</span>';

          $('#method-status').html(statusBadge);
          $('#method-title').text(formatText(methodName));

          $('#method-created-at').text(createdAt ? createdAt : '');
          $('#method-updated-at').text(updatedAt ? updatedAt : '');

          $('#method-fields-container').empty();

          $.each(fields, function(index, field) {
              let inputName = formatText(index);
              let inputType = formatText(field);
            ;
              let fieldHtml = `
                <div class="global-bg-box p-10px rounded">
                    <div class="d-flex align-items-cetner justify-content-between gap-2 flex-wrap mb-10px">
                        <h5 class="text-title m-0 d-flex gap-2">${inputName} </h5>
                    </div>
                    <div class="bg-white rounded p-10px d-flex flex-column gap-1">
                        <div class="d-flex gap-2">
                            <span class="fs-14 text-title">${inputType}</span>
                        </div>
                    </div>
                </div>
            `;

              $('#method-fields-container').append(fieldHtml);
          });

          $('#editMethodBtn').attr('data-url', action);

          $('#withdraw_method-offcanvas').addClass('show');
        });

              // Offcanvas status toggle handler
        $(document).on('change', '#offcanvas-status', function () {
            let id = $(this).data('id');
            statusToggle(id)
        });
        // Offcanvas delete button handler
        $(document).on('click', '.offcanvas-delete-btn', function () {
            let id = $(this).data('id');
            let message = '{{ translate('Want to delete this item') }}';
            Swal.fire({
                title: '{{ translate('messages.Are you sure?') }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '{{ translate('messages.no') }}',
                confirmButtonText: '{{ translate('messages.Yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ route('vendor.wallet-method.delete', 0) }}".replace(/0$/, id),
                        method: 'POST',
                        data: {
                            _method: 'delete'
                        },
                        success: function (data) {
                            toastr.success('{{ translate('messages.withdraw_method_deleted_successfully') ?? "Withdraw method deleted successfully" }}');
                            setTimeout(function(){
                                location.reload();
                            }, 1000);
                        },
                        error: function () {
                            toastr.error('{{ translate('messages.withdraw_method_delete_failed') ?? "Withdraw method delete failed" }}');
                        }
                    });
                }
            });
        });
              // Offcanvas 'Mark As Default' button handler
        $(document).on('click', '#mark', function(e) {
            e.preventDefault();
            var id = $('#offcanvas-status').data('id');
            updateDefault(id);
        });
        function statusToggle(id) {
                $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('vendor.wallet-method.status-update')}}",
                        method: 'POST',
                        data: {
                            id: id
                        },
                        success: function (data) {
                            if(data.success == true) {
                                toastr.success(data.message);
                            }
                            else if(data.success == false) {
                                toastr.error(data.message);
                            }
                            setTimeout(function(){
                                    location.reload();
                                }, 1000);
                        }
                    });
        }


        function updateDefault(id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('vendor.wallet-method.default-status-update')}}",
                method: 'POST',
                data: {
                    id: id
                },
                success: function (data) {
                    if(data.success == true) {
                        toastr.success(data.message);
                        setTimeout(function(){
                            location.reload();
                        }, 1000);
                    }
                    else if(data.success == false) {
                        toastr.error(data.message);
                        setTimeout(function(){
                            location.reload();
                        }, 1000);
                    }
                }
            });
        }



        $(document).on('click', '.offcanvas-trigger-edit', function() {
                let url = $(this).data('url');
                 $('#withdraw_method_edit-offcanvas').addClass('show');
                fetch_data(url);
        });

        $(document).on('click', '.offcanvas-close-1', function() {
                offcanvas_close();
        });

        function fetch_data(url) {
            $.ajax({
                url: url,
                type: "get",
                beforeSend: function() {
                    $('#data-view').empty();
                    $('#loading').show()
                },
                success: function(data) {
                    if(data.error){
                            offcanvas_close();
                            toastr.error(data.message);
                    } else {
                        $("#data-view").append(data.view);

                    }
                },
                complete: function() {
                    $('#loading').hide()
                }
            })
        }



</script>
@endpush

@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.event.breadcrumb', [
    'mainTitle' => trans('event.title.index'),
    'breadcrumb' => Breadcrumbs::render('event.index'),
    'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="nav-tabs-wrap">
            {{-- @if($roleType != 'zsa')
            <ul class="nav nav-tabs tabs-line-style" id="eventTabList" role="tablist">
                <li class="nav-item">
                    <a aria-controls="Bookings" aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#bookings-tab" role="tab">
                        {{ trans('labels.event.bookings_tab') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a aria-controls="Service" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#events-tab" role="tab">
                        {{ trans('labels.event.service_tab') }}
                    </a>
                </li>
            </ul>
            @endif --}}
            <!-- search-block -->
            <div class="card search-card">
                <div class="card-body pb-0">
                    <h4 class="d-md-none">
                        {{ trans('buttons.general.filter') }}
                    </h4>
                    {{ Form::open(['route' => 'admin.event.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'eventSearch']) }}
                    <div class="search-outer d-md-flex justify-content-between">
                        <div>
                            <div class="form-group">
                                {{ Form::text('eventName', request()->get('eventName'), ['class' => 'form-control', 'placeholder' => 'Search by event name', 'id' => 'eventName', 'autocomplete' => 'off']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::select('eventCategory', $eventCategory, request()->get('eventCategory'), ['class' => 'form-control select2', 'id' => 'eventCategory', 'placeholder' => "Select category", 'data-placeholder' => "Select category", 'data-allow-clear' => 'true']) }}
                            </div>
                            <div class="form-group" data-control-for="service-tab">
                                {{ Form::select('eventStatus', config('zevolifesettings.event-status-listing'), request()->get('eventStatus'), ['class' => 'form-control select2', 'id' => 'eventStatus', 'placeholder' => "Select status", 'data-placeholder' => "Select status", 'data-allow-clear' => 'true']) }}
                            </div>
                            <div class="form-group" data-control-for="bookings-tab" style="display: none;">
                                {{ Form::select('bookingStatus', $bookingStatus, request()->get('bookingStatus'), ['class' => 'form-control select2', 'id' => 'bookingStatus', 'placeholder' => "Select status", 'data-placeholder' => "Select status", 'data-allow-clear' => 'true']) }}
                            </div>
                            <div class="form-group" data-control-for="bookings-tab" style="display: none;">
                                {{ Form::select('assigneeCompany', $assigneeComapanies, request()->get('assigneeCompany'), ['class' => 'form-control select2', 'id' => 'assigneeCompany', 'placeholder' => "Select assignee company", 'data-placeholder' => "Select assignee company", 'data-allow-clear' => 'true']) }}
                            </div>
                        </div>
                        <div class="search-actions align-self-start">
                            <button class="me-md-4 filter-apply-btn" id="searchSubmitBtn" type="submit">
                                {{ trans('buttons.general.apply') }}
                            </button>
                            <a class="filter-cancel-icon" href="javascript:void(0);" id="resetSearchBtn">
                                <i class="far fa-times">
                                </i>
                                <span class="d-md-none ms-2 ms-md-0">
                                    {{ trans('buttons.general.reset') }}
                                </span>
                            </a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
            <!-- /.search-block -->
            <div class="tab-content" id="eventTabListContent">
                <div aria-labelledby="service-tab" class="tab-pane fade show active" id="events-tab" role="tabpanel">
                    <a class="btn btn-primary filter-btn" href="javascript:void(0);">
                        <i class="far fa-filter me-2 align-middle">
                        </i>
                        <span class="align-middle">
                            {{ trans('buttons.general.filter') }}
                        </span>
                    </a>
                    <div class="card" id="service-tab-result-block">
                        <div class="card-body">
                            <div class="card-table-outer">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="serviceManagment">
                                        <thead>
                                            <tr>
                                                <th>
                                                    {{ trans('event.table.serviceManagment.event_name') }}
                                                </th>
                                                <th class="text-center">
                                                    {{ trans('event.table.serviceManagment.assigned_companies') }}
                                                </th>
                                                <th>
                                                    {{ trans('event.table.serviceManagment.subcategory_name') }}
                                                </th>
                                                <th>
                                                    {{ trans('event.table.serviceManagment.duration_listing') }}
                                                </th>
                                                <th class="text-center">
                                                    {{ trans('event.table.serviceManagment.status') }}
                                                </th>
                                                <th class="th-btn-4 no-sort">
                                                    {{ trans('event.table.serviceManagment.actions') }}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center bg-white" id="service-tab-process-block" style="display: none;">
                        <p class="m-2 p-2">
                            <span class="fa fa-spinner fa-spin">
                            </span>
                            {{ __("Loading...") }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="modal fade" data-backdrop="static" data-id="0" data-keyboard="false" id="delete-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __("Delete Event?") }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ __("All data related to this event will be deleted.") }}
                </p>
                <p>
                    {{ __("Are you sure you want to delete this event?") }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="delete-model-box-confirm" type="button">
                    {{ trans('buttons.general.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" data-backdrop="static" data-id="0" data-keyboard="false" id="publish-event-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __("Publish Event?") }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ __("Are you sure, you want to publish the event?") }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="event-model-box-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>
@if($roleType != 'pzsa')
<div class="modal fade" data-backdrop="static" data-bid="0" data-keyboard="false" id="cancel-event-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Cancel Event?') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ __('Are you sure, you want to cancel the event?') }}
                </p>
                {{ Form::open(['action' => null, 'class' => 'form-horizontal', 'method' => 'post', 'role' => 'form', 'id' => 'cancelEventForm']) }}
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::textarea('cancel_reason', null, ['id' => 'cancel_reason', 'class' => 'form-control mt-2', 'placeholder' => __('Enter reason for cancel the event'), 'rows' => 3]) }}
                    </div>
                </div>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="event-cancel-model-box-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" data-backdrop="static" data-bid="0" data-keyboard="false" id="cancel-event-details-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Cancellation details') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::label('', trans('labels.event.cancelled_by')) }}:
                        {{ Form::label('', trans('labels.event.event_name'), ['class' => 'f-400', 'id' => 'cancelled_by']) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('', trans('labels.event.date_time')) }}:
                        {{ Form::label('', trans('labels.event.event_name'), ['class' => 'f-400', 'id' => 'cancelled_at']) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('', trans('labels.event.reason')) }}:
                        {{ Form::label('', trans('labels.event.event_name'), ['class' => 'f-400', 'id' => 'cancelation_reason']) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CancelEventRequest', '#cancelEventForm') !!}
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var timezone = '{{ $timezone }}',
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        };
    function loadServiceTabData() {
        $('#serviceManagment').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                type: 'POST',
                url: '{{ route('admin.event.getEvents') }}',
                data: {
                    type: 'service',
                    eventName: $("#eventName").val(),
                    eventStatus: $("#eventStatus").val(),
                    eventCategory: $("#eventCategory").val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'name',
                name: 'name'
            }, {
                data: 'companies_count',
                name: 'companies_count',
                className: 'text-center',
                visible: '{{ (($roleType == "rca") ? false : true) }}'
            }, {
                data: 'subcategory_name',
                name: 'subcategory_name',
            }, {
                data: 'duration',
                name: 'duration',
            }, {
                data: 'status',
                name: 'status',
                className: 'text-center',
            }, {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            }],
            paging: true,
            pageLength: pagination.value,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
            drawCallback: function(settings) {
                $("#service-tab-result-block").show();
                $("#service-tab-process-block").hide();
            }
        });
    }

    @if($roleType != 'zsa')
    function loadBookingsTabData() {
        $('#bookingManagement').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                type: 'POST',
                url: '{{ route('admin.event.getEvents') }}',
                data: {
                    type: 'booking',
                    eventName: $("#eventName").val(),
                    bookingStatus: $("#bookingStatus").val(),
                    eventCategory: $("#eventCategory").val(),
                    assigneeCompany: $("#assigneeCompany").val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'event_name',
                name: 'event_name',
            }, {
                data: 'creator',
                name: 'creator',
            }, {
                data: 'assignee',
                name: 'assignee',
                visible: '{{ (($roleType == "rsa") ? true : false) }}'
            }, {
                data: 'subcategory_name',
                name: 'subcategory_name',
            }, {
                data: 'presenter_name',
                name: 'presenter_name',
            }, {
                data: 'duration',
                name: 'duration',
                className: 'text-center',
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format("MMM DD, YYYY")  + '<br />' + moment.utc(data).tz(timezone).format("hh:mm A") + " - " + moment.utc(row.end_time).tz(timezone).format("hh:mm A");
                }
            }, {
                data: 'status',
                name: 'status',
                className: 'text-center',
            }, {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            }],
            paging: true,
            pageLength: pagination.value,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
            drawCallback: function(settings) {
                $("#booking-tab-result-block").show();
                $("#booking-tab-process-block").hide();
            }
        });
    }
    @endif

    function setSearchBlock(target) {
        if(target == "#bookings-tab") {
            $("[data-control-for='service-tab']").hide();
            @if($roleType == 'rsa')
            $("[data-control-for='bookings-tab']").show();
            @elseif($roleType == 'rca')
            $('#bookingStatus').parent().show();
            @endif
        } else {
            $("[data-control-for='service-tab']").show();
            $("[data-control-for='bookings-tab']").hide();
        }
    }

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        @if($roleType != 'pzsa')
        if (window.location.hash) {
            var hash = window.location.hash,
                hash = (($('.nav-tabs a[href="' + hash + '"]').length > 0) ? hash : '#bookings-tab');
            $('.nav-tabs a[href="' + hash + '"]').tab('show');
            if(hash == "#events-tab") {
                setSearchBlock(hash);
                loadServiceTabData();
            } else {
                setSearchBlock('#bookings-tab');
                loadBookingsTabData();
            }
        } else {
            //setSearchBlock('#events-tab');
            loadServiceTabData();
        }

        $(document).on('show.bs.tab', '#eventTabList.nav-tabs a', function(e) {
            var target = $(e.target).attr("href");
            if (target) {
                window.location.hash = target;
                if(target == "#events-tab") {
                    setSearchBlock(target);
                    loadServiceTabData();
                } else {
                    setSearchBlock('#bookings-tab');
                    loadBookingsTabData();
                }
            }
        });

        // show event cancel popup on cancel button click
        $(document).on('click', '.cancel-event', function(e) {
            var bid = $(this).data('bid'),
                route = "{{ route('admin.event.cancelEvent', ':bid') }}";
            $('#cancelEventForm').attr('action', route.replace(':bid', bid));
            $('#cancel-event-model-box').data("bid", bid).modal('show');
        });

        // reset cancel event reason from once popup is closed
        $(document).on('hidden.bs.modal', '#cancel-event-model-box', function(e) {
            $('#cancel_reason').val('').removeClass('is-invalid is-valid');
            $('#cancel_reason-error').remove();
            $('#cancelEventForm').attr('action', '');
        });

        // cancel event on yes button of cancel modal
        $(document).on('click', '#event-cancel-model-box-confirm', function(e) {
            if($('#cancelEventForm').valid()) {
                toastr.clear();
                var url = "{{ route('admin.event.cancelEvent', [':bid']) }}",
                    bid = $('#cancel-event-model-box').data("bid");
                $('#cancel-event-model-box .modal-header, #cancel-event-model-box .modal-footer').css('pointer-events', 'none');
                $('#event-cancel-model-box-confirm').html(`<i class="fa fa-spinner fa-spin"></i>`);
                $.ajax({
                    type: 'POST',
                    url: url.replace(":bid", bid),
                    data: {
                        cancel_reason: $('#cancel_reason').val()
                    },
                    dataType: 'json',
                }).done(function(data) {
                    $('#bookingManagement').DataTable().ajax.reload(null, false);
                    if (data.cancelled == true) {
                        toastr.success(data.message);
                        $('#cancel-event-model-box').modal('hide');
                    } else {
                        toastr.error((data.message || `Failed to cancel an event.`));
                    }
                }).fail(function(error) {
                    toastr.error((error?.responseJSON?.message || `Failed to cancel an event.`));
                }).always(function() {
                    $('#cancel-event-model-box .modal-header, #cancel-event-model-box .modal-footer').css('pointer-events', '');
                    $('#event-cancel-model-box-confirm').html(`Yes`);
                });
            }
        });

        // show cancelled evetn details on view cancel details button click
        $(document).on('click', '.view-cancel-event-details', function(e) {
            $(".page-loader-wrapper").fadeIn();
            var bid = $(this).data('bid'),
                url = "{{ route('admin.event.cancelEventDetails', [':bid']) }}";
            $.ajax({
                type: 'POST',
                url: url.replace(":bid", bid),
                dataType: 'json',
            }).done(function(data) {
                if (data?.status) {
                    $('#cancelled_by').html(data?.cancelled_by);
                    $('#cancelled_at').html(data?.cancelled_at);
                    $('#cancelation_reason').html(data?.cancelation_reason);
                    $('#cancel-event-details-model-box').modal('show');
                } else {
                    toastr.error((data.message || 'Failed to load details.'));
                }
            }).fail(function(error) {
                toastr.error((error?.responseJSON?.message || `Failed to load details.`));
            }).always(function() {
                $(".page-loader-wrapper").fadeOut();
            });
        });
        @else
        setSearchBlock('#events-tab');
        loadServiceTabData();
        @endif

        $(document).on('click', '#resetSearchBtn', function(e) {
            var activeTab = ($("ul#eventTabList li a.active").attr('href') || "#events-tab");
            $("#eventName").val('');
            $("#bookingStatus, #eventStatus, #eventCategory, #assigneeCompany").val('').trigger('change');
            if(activeTab == "#bookings-tab") {
                $("#booking-tab-result-block").hide();
                $("#booking-tab-process-block").show();
                loadBookingsTabData();
            } else {
                $("#service-tab-result-block").hide();
                $("#service-tab-process-block").show();
                loadServiceTabData();
            }
        });

        $(document).on('submit', '#eventSearch', function(e) {
            e.preventDefault();
            var activeTab = ($("ul#eventTabList li a.active").attr('href') || "#events-tab");
            if(activeTab == "#bookings-tab") {
                $("#booking-tab-result-block").hide();
                $("#booking-tab-process-block").show();
                setSearchBlock(activeTab);
                loadBookingsTabData();
            } else {
                $("#service-tab-result-block").hide();
                $("#service-tab-process-block").show();
                setSearchBlock('#events-tab');
                loadServiceTabData();
            }
        });

        $(document).on('click', '#eventDelete', function(e) {
            $('#delete-model-box').data("id", $(this).data('id')).modal('show');
        });

        $(document).on('click', '#delete-model-box-confirm', function(e) {
            $('.page-loader-wrapper').show();
            var objectId = $('#delete-model-box').data("id");

            $.ajax({
                type: 'DELETE',
                url: "{{ route('admin.event.delete', '/') }}" + `/${objectId}`,
                crossDomain: true,
                cache: false,
                contentType: 'json'
            })
            .done(function(data) {
                if (data && data.deleted == true) {
                    $('#serviceManagment').DataTable().ajax.reload(null, false);
                    toastr.success("Event has been deleted successfully!");
                } else if (data && data.deleted == 'event_booked') {
                    toastr.error("Event is booked for company so couldn't be deleted.");
                } else {
                    toastr.error("Failed to delete event.");
                }
            })
            .fail(function(data) {
                toastr.error("Failed to delete event.");
            })
            .always(function() {
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            });
        });

        $(document).on('click', '#publishEvent', function(e) {
            $('#publish-event-model-box').data("id", $(this).data('id')).modal('show');
        });

        $(document).on('click', '#event-model-box-confirm', function(e) {
            var _this = $(this),
                objectId = $('#publish-event-model-box').data("id");

            _this.prop('disabled', 'disabled');
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.event.publish', '/') }}" + `/${objectId}`,
                dataType: 'json',
            }).done(function(data) {
                $('#serviceManagment').DataTable().ajax.reload(null, false);
                if (data.published == true) {
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            }).fail(function(data) {
                if (data == 'Forbidden') {
                    toastr.error(`Failed to publish event.`);
                }
            }).always(function() {
                _this.removeAttr('disabled');
                $('#publish-event-model-box').modal('hide');
            });
        });
    });
</script>
@endsection

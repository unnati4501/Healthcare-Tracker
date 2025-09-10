@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $eventSubcategory }}
                    <span class="fal fa-long-arrow-right">
                    </span>
                    {{ $event->name }}
                </h1>
                {!! Breadcrumbs::render('event.details') !!}
            </div>
            <div class="align-self-center">
                <a class="btn btn-outline-primary" href="{{ $cancelUrl }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ $cancelButtonText }}
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- search-block -->
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('buttons.general.filter') }}
                </h4>
                {{ Form::open(['route' => ['admin.event.view', $event->id], 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'detailSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('eventCompany', $eventCompanies, request()->get('eventCompany'), ['class' => 'form-control select2', 'id'=>'eventCompany', 'placeholder' => "Select company", 'data-placeholder' => "Select company", 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('eventPresenter', $eventPresenters, request()->get('eventPresenter'), ['class' => 'form-control select2', 'id'=>'eventPresenter', 'placeholder' => "Select presenter", 'data-placeholder' => "Select presenter", 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('eventStatus', $eventStatus, request()->get('eventStatus'), ['class' => 'form-control select2', 'id'=>'eventStatus', 'placeholder' => "Select status", 'data-placeholder' => "Select status", 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group daterange-outer">
                            <div class="input-daterange dateranges justify-content-between mb-0">
                                <div class="datepicker-wrap me-0 mb-0">
                                    {{ Form::text('fromdate', request()->get('fromdate'), ['id' => 'fromdate', 'class' => 'form-control', 'placeholder' => 'From date', 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                                <span class="input-group-addon text-center">
                                    -
                                </span>
                                <div class="datepicker-wrap me-0 mb-0">
                                    {{ Form::text('todate', request()->get('todate'), ['id' => 'todate', 'class' => 'form-control', 'placeholder' => 'To date', 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.event.view', $event->id) }}">
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
        <a class="btn btn-primary filter-btn" href="javascript:void(0);">
            <i class="far fa-filter me-2 align-middle">
            </i>
            <span class="align-middle">
                {{ trans('buttons.general.filter') }}
            </span>
        </a>
        <!-- /.search-block -->
        <!-- grid -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="eventManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('event.details.table.company') }}
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('event.details.table.presenter') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('event.details.table.duration_date_time_view') }}
                                    </th>
                                    <th class="text-center no-sort">
                                        {{ trans('event.details.table.status') }}
                                    </th>
                                    <th class="th-btn-3 no-sort">
                                        {{ trans('event.details.table.actions') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.grid -->
    </div>
</section>
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
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var timezone = '{{ $timezone }}',
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        };
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.dateranges').datepicker({
            format: "mm/dd/yyyy",
            todayHighlight: false,
            autoclose: true,
        });

        $('#eventManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'POST',
                url: '{!! $fetchDataUrl !!}',
                data: {
                    eventCompany: $('#eventCompany').val(),
                    eventPresenter: $('#eventPresenter').val(),
                    fromdate: $('#fromdate').val(),
                    todate: $('#todate').val(),
                    eventStatus: $('#eventStatus').val(),
                    getQueryString: window.location.search,
                },
            },
            columns: [{
                data: 'company_name',
                name: 'company_name'
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
                    $('#eventManagment').DataTable().ajax.reload(null, false);
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
    });
</script>
@endsection

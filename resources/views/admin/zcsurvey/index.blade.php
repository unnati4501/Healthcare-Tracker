@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
{{-- <link href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700&display=swap?var=<?= rand() ?>" rel="stylesheet"/> --}}
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcsurvey.breadcrumb', [
  'mainTitle' => trans('survey.title.index'),
  'breadcrumb' => Breadcrumbs::render('survey.index'),
  'create' => true
])
<!-- /.content-header -->
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
                {{ Form::open(['route' => 'admin.zcsurvey.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'surveySearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('survey_title', request()->get('survey_title'), ['class' => 'form-control', 'placeholder' => 'Search by survey title', 'id' => 'survey_title', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('survey_status', $survey_status, request()->get('survey_status'), ['class' => 'form-control select2', 'id'=>'survey_status', 'placeholder' => "Survey status", 'data-placeholder' => "Survey status", 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.zcsurvey.index') }}">
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
        <!-- .grid -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="surveyManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('labels.zcsurvey.title') }}
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('labels.zcsurvey.description') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('labels.zcsurvey.no_of_questions') }}
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.creation_date') }}
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.modification_date') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('labels.zcsurvey.status') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('labels.zcsurvey.response') }}
                                    </th>
                                    <th class="th-btn-4 no-sort">
                                        {{ trans('labels.zcsurvey.action') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.grid -->
    </div>
</section>
<div class="modal fade" data-id="0" id="delete-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Delete survey?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure you want to delete this survey?
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
<div class="modal fade" data-id="0" id="publish-survey-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Publish survey?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure, you want to publish the survey?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="survey-model-box-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" data-id="0" id="unpublish-survey-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Unpublish survey?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure, you want to unpublish the survey?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="survey-model-unpublish-box-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" data-id="0" id="copy-survey-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Copy survey?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure, you want to create copy of the survey?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="copy-survey-model-box-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
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

        $('#surveyManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.zcsurvey.getSurveys') }}',
                data: {
                    survey_title: $('#survey_title').val(),
                    survey_status: $('#survey_status').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'title',
                name: 'title',
            },
            {
                data: 'description',
                name: 'description',
                searchable: false,
                sortable: false,
            },
            {
                data: 'surveyquestions_count',
                name: 'surveyquestions_count',
                class: 'text-center',
            },
            {
                data: 'created_at',
                name: 'created_at',
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            },
            {
                data: 'updated_at',
                name: 'updated_at',
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            },
            {
                data: 'status',
                name: 'status',
                searchable: false,
                class: 'text-center',
            },
            {
                data: 'surveyreponses_count',
                name: 'surveyreponses_count',
                class: 'text-center',
            },
            {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false,
                class: 'text-center',
            }],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: pagination.value,
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

        $(document).on('click', '.copy-survey', function(e) {
            var id = $(this).data("id");
            $('#copy-survey-model-box').data('id', id).modal('show');
        });

        $(document).on('click', '#copy-survey-model-box-confirm', function(e) {
            showPageLoaderWithMessage('Copying survey...')
            var id = $('#copy-survey-model-box').data("id");
            $.ajax({
                type: 'GET',
                url: "{{ route('admin.zcsurvey.copy', '/') }}" + `/${id}`,
                contentType: 'json'
            })
            .done(function(data) {
                $('#surveyManagment').DataTable().ajax.reload(null, false);
                if (data.status == true) {
                    toastr.success(data.data);
                } else {
                    toastr.error((data.data || "Failed to copy survey! Please try again."));
                }
            })
            .fail(function(data) {
                if (data == 'Forbidden') {
                    toastr.error("Failed to copy survey! Please try again.");
                }
            })
            .always(function() {
                hidesPageLoader();
                $('#copy-survey-model-box').data('id', 0);
                $('#copy-survey-model-box').modal('hide');
            });
        });

        $(document).on('click', '.delete-survey', function(t) {
            $('#delete-model-box').data("id", $(this).data('id'));
            $('#delete-model-box').modal('show');
        });

        $(document).on('click', '#delete-model-box-confirm', function(e) {
            $('.page-loader-wrapper').show();
            var objectId = $('#delete-model-box').data("id");
            $.ajax({
                type: 'DELETE',
                url: "{{ route('admin.zcsurvey.delete', '/') }}" + `/${objectId}`,
                crossDomain: true,
                cache: false,
                contentType: 'json'
            })
            .done(function(data) {
                $('#surveyManagment').DataTable().ajax.reload(null, false);
                if (data.deleted == true) {
                    toastr.success(data.message);
                } else {
                    toastr.error((data.message || "Failed to delete survey! Please try again."));
                }
            })
            .fail(function(data) {
                if (data == 'Forbidden') {
                    toastr.error("Failed to delete survey! Please try again.");
                }
            })
            .always(function() {
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            });
        });

        $(document).on('click', '.publish-action', function(e) {
            var action = $(this).data("action");
            $('#publish-survey-model-box, #unpublish-survey-model-box').data("id", $(this).data('id'));
            $('#publish-survey-model-box, #unpublish-survey-model-box').data("action", action);

            if(action == "unpublish") {
                $('#unpublish-survey-model-box').modal('show');
            } else if(action == "publish") {
                $('#publish-survey-model-box').modal('show');
            }
        });

        $(document).on('click', '#survey-model-box-confirm, #survey-model-unpublish-box-confirm', function(e) {
            var _this = $(this),
                objectId = $('#publish-survey-model-box').data("id"),
                action = $('#publish-survey-model-box').data("action");

            _this.prop('disabled', 'disabled');
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.zcsurvey.publish', '/') }}" + `/${objectId}`,
                data: $.param({ action: action }),
                crossDomain: true,
                cache: false,
                dataType: 'json',
            }).done(function(data) {
                $('#surveyManagment').DataTable().ajax.reload(null, false);
                if (data.status && data.status == 1) {
                    toastr.success(data.data);
                } else {
                    toastr.error((data.data || "{{ trans('labels.common_title.something_wrong_try_again') }}"));
                }
            }).fail(function(data) {
                if (data == 'Forbidden') {
                    toastr.error(`Failed to ${action} survey.`);
                }
            }).always(function() {
                _this.removeAttr('disabled');
                $('#unpublish-survey-model-box').modal('hide');
                $('#publish-survey-model-box').modal('hide');
            });
        });
    });
</script>
@endsection

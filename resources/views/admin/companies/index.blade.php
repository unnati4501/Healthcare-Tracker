@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companies.breadcrumb', [
    'mainTitle'  => trans('company.title.index'),
    'breadcrumb' => Breadcrumbs::render('companies.index'),
    'create'     => ($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null)),
    'companyType'=> $companyType
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
                {{ Form::open(['route' =>  ['admin.companies.index', $companyType], 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'companySearch']) }}
                {{ Form::hidden('companyType', $companyType, ['id' => 'companyType']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('recordCode', request()->get('recordCode'), ['class' => 'form-control', 'placeholder' => 'Search By Code', 'id' => 'recordCode', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('recordName', request()->get('recordName'), ['class' => 'form-control', 'placeholder' => 'Search By Name', 'id' => 'recordName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('domain_verification', ['true' => 'Yes', 'false' => 'No'], request()->get('domain_verification'), ['class' => 'form-control select2', 'id'=>'domain_verification', 'placeholder' => "Domain Verification", 'data-placeholder' => "Domain Verification", 'data-allow-clear' => 'true']) }}
                        </div>
                        @if($companyType == 'reseller')
                        <div class="form-group">
                            {{ Form::select('reseller', ['1' => 'Parent', '2' => 'Child'], request()->get('reseller'), ['class' => 'form-control select2', 'id'=>'reseller', 'placeholder' => "Reseller", 'data-placeholder' => "Reseller", 'data-allow-clear' => 'true']) }}
                        </div>
                        @endif
                        <div class="form-group">
                            {{ Form::select('survey', ['1' => 'Yes', '0' => 'No',], request()->get('survey'), ['class' => 'form-control select2', 'id' => 'survey', 'placeholder' => "Survey", 'data-placeholder' => "Survey", 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('companyplans', $companyplans, request()->get('companyplans'), ['class' => 'form-control select2', 'id' => 'companyplans', 'placeholder' => trans('company.filter.select_company_plan'), 'data-placeholder' => trans('company.filter.select_company_plan'), 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.companies.index',$companyType) }}">
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
                        <table class="table custom-table" id="companyManagment">
                            <thead>
                                <tr>
                                    <th class="text-center hide">
                                        Updated At
                                    </th>
                                    <th class="no-sort th-btn-2">
                                    </th>
                                    <th>
                                        Company Code
                                    </th>
                                    <th>
                                        Company Name
                                    </th>
                                    <th>
                                        Industry
                                    </th>
                                    <th class="th-btn-4">
                                        Domain Verification
                                    </th>
                                    <th class="th-btn-2">
                                        Reseller Type
                                    </th>
                                    <th class="th-btn-2">
                                        Company plan
                                    </th>
                                    <th>
                                        Survey
                                    </th>
                                    <th class="th-btn-2">
                                        Plan Status
                                    </th>
                                    <th class="no-sort th-btn-4">
                                        Actions
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
                    Delete Company?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    All data related to this company deleted.
                </p>
                <p class="m-0" id="del-popup-msg">
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
<div class="modal fade" data-backdrop="static" data-id="0" id="export-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(['url' => '', 'class' => 'form-horizontal', 'method' => 'POST', 'role' => 'form', 'id' => 'frmExportSurveyReport']) }}
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Export Survey Report') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('email', trans('labels.company.emailaddress')) }}
                    {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Enter Email Address', 'id' => 'email', 'autocomplete' => 'off']) }}
                </div>
                <div class="form-group">
                    {{ Form::label('start_date', trans('labels.company.fromdate')) }}
                    {{ Form::text('start_date', null, ['class' => 'form-control bg-white', 'placeholder' => 'Select from date time', 'id' => 'start_date', 'readonly' => true]) }}
                </div>
                <div class="form-group">
                    {{ Form::label('end_date', trans('labels.company.todate')) }}
                    {{ Form::text('end_date', null, ['class' => 'form-control bg-white', 'placeholder' => 'Select to date time', 'id' => 'end_date', 'readonly' => true]) }}
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('labels.buttons.cancel') }}
                </button>
                <button class="btn btn-primary" id="export-model-box-confirm" type="submit">
                    {{ trans('labels.buttons.export') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
{!! JsValidator::formRequest('App\Http\Requests\Admin\ExportSurveyReportRequest','#frmExportSurveyReport') !!}
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#start_date").datepicker({
            format: 'yyyy-mm-dd',
            todayHighlight: false,
            autoclose: true,
        }).on('changeDate', function (selected) {
            $("#start_date").valid();
            if(selected.date) {
                var minDate = new Date(selected.date.valueOf());
                $('#end_date').datepicker('setStartDate', minDate);
                // $('#end_date').datepicker('setDate', minDate);
            }
        });

        $("#end_date").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: false,
        }).on('changeDate', function (selected) {
            $("#end_date").valid();
            if(selected.date) {
                var maxDate = new Date(selected.date.valueOf());
                $('#start_date').datepicker('setEndDate', maxDate);
            }
        });

        $('#companyManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.companies.getCompanies','zevo') }}',
                data: {
                    status: 1,
                    recordName: $('#recordName').val(),
                    recordCode: $('#recordCode').val(),
                    domain_verification: $('#domain_verification').val(),
                    reseller: $('#reseller').val(),
                    survey: $('#survey').val(),
                    companyplans: $('#companyplans').val(),
                    getQueryString: window.location.search,
                    companyType : $("#companyType").val()
                },
            },
            columns: [
                { data: 'updated_at', name: 'updated_at', visible: false },
                { data:'logo', searchable : false, className: 'text-center',
                    render: function (data, type) {
                        return `<div class="table-img table-img-l"><img src="${data}" /></div>`;
                    }
                },
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'industry', name: 'industry', searchable: false },
                { data: 'has_domain', name: 'has_domain', searchable: false },
                { data: 'is_reseller', name: 'is_reseller', searchable: false },
                { data: 'company_plan', name: 'company_plan', searchable: false },
                { data: 'enable_survey', name: 'enable_survey', searchable: false },
                {
                    data: 'diff', name: 'diff', searchable: false, class: 'text-center',
                    render: function (data, type, row) {
                        var days = Math.ceil(data / 24);
                        if(row.start_date_diff > 0) {
                            return `<span class="text-secondary">Inactive</span>`;
                        } else if(days >= 10) {
                            return `<span class="text-success">Active</span>`;
                        } else if(days <= 0) {
                            return `<span class="text-danger">Expired</span>`;
                        } else if(days < 10) {
                            return `<span class="text-warning">Expire soon(${days})</span>`;
                        }
                    }
                },
                { data: 'actions', name: 'actions', searchable: false, sortable: false }
            ],
            paging: true,
            pageLength: pagination.value,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [[0, 'desc']],
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
                var api = this.api();
                if(api.rows().count() <= 2){
                    $("#companyManagment").addClass("adjust-dropdown-height");
                }else {
                    $("#companyManagment").removeClass("adjust-dropdown-height");
                }
            }
        });

        $(document).on('click', '.companyDelete', function (t) {
            var title = ($(this).data("title") || "this company"),
                type = ($(this).data("type") || "(Zevo)");
            $('#del-popup-msg').html(`Are you sure you want to delete <b>'${title} ${type}'</b>?`);
            $('#delete-model-box').data("id", $(this).data('id'));
            $('#delete-model-box').modal('show');
        });

        $(document).on('click', '#delete-model-box-confirm', function (e) {
            $('.page-loader-wrapper').show();
            var objectId = $('#delete-model-box').data("id");
            $.ajax({
                type: 'DELETE',
                url: "{{route('admin.companies.delete','/')}}"+ '/' + objectId,
                data: null,
                crossDomain: true,
                cache: false,
                contentType: 'json',
                success: function (data) {
                    $('#companyManagment').DataTable().ajax.reload(null, false);
                    if (data['deleted'] == 'true') {
                        toastr.success("Company deleted");
                    } else if(data['deleted'] == 'use') {
                        toastr.error("The company is in use!");
                    } else {
                        toastr.error("delete error.");
                    }
                    $('#delete-model-box').modal('hide');
                    $('.page-loader-wrapper').hide();
                },
                error: function (data) {
                    if (data == 'Forbidden') {
                        toastr.error("delete error.");
                    }
                    $('#delete-model-box').modal('hide');
                    $('.page-loader-wrapper').hide();
                }
            });
        });

        $(document).on('click', '.export-survey-report', function(e) {
            var id = ($(this).data('id') || 0),
                url = "{{ route('admin.companies.get-survey-details', [':company_id', 'zcsurvey']) }}",
                frmUrl = "{{ route('admin.companies.export-survey-report', [':company_id', 'zcsurvey']) }}",
                frmUrl = frmUrl.replace(':company_id', id);
            $.ajax({
                url: url.replace(':company_id', id),
                type: 'GET',
                dataType: 'json',
            })
            .done(function(data) {
                $('#email').val(data.email);
                $('#start_date').datepicker('setStartDate', moment(data.startDate).format('YYYY-MM-DD'));
                $('#start_date').datepicker('setEndDate', moment(data.endDate).format('YYYY-MM-DD'));
                $('#start_date').datepicker('setDate', moment(data.startDate).format('YYYY-MM-DD'));

                $('#end_date').datepicker('setStartDate', moment(data.startDate).format('YYYY-MM-DD'));
                $('#end_date').datepicker('setEndDate', moment(data.endDate).format('YYYY-MM-DD'));
                $('#end_date').datepicker('setDate', moment(data.endDate).format('YYYY-MM-DD'));

                $('#frmExportSurveyReport').attr('action', frmUrl);
                $('#export-model-box').modal('show');
            })
            .fail(function(error) {
                toastr.error(error?.responseJSON?.message || "{{ trans('labels.common_title.something_wrong_try_again') }}");
            });
        });

        $(document).on('click', '.masterclass-survey-report', function(e) {
            var id = ($(this).data('id') || 0),
                url = "{{ route('admin.companies.get-survey-details', [':company_id', 'masterclass']) }}",
                frmUrl = "{{ route('admin.companies.export-survey-report', [':company_id', 'masterclass']) }}",
                frmUrl = frmUrl.replace(':company_id', id);
            $.ajax({
                url: url.replace(':company_id', id),
                type: 'GET',
                dataType: 'json',
            })
            .done(function(data) {
                $('#email').val(data.email);
                $('#start_date').datepicker('setStartDate', moment(data.startDate).format('YYYY-MM-DD'));
                $('#start_date').datepicker('setEndDate', moment(data.endDate).format('YYYY-MM-DD'));
                $('#start_date').datepicker('setDate', moment(data.startDate).format('YYYY-MM-DD'));

                $('#end_date').datepicker('setStartDate', moment(data.startDate).format('YYYY-MM-DD'));
                $('#end_date').datepicker('setEndDate', moment(data.endDate).format('YYYY-MM-DD'));
                $('#end_date').datepicker('setDate', moment(data.endDate).format('YYYY-MM-DD'));

                $('#frmExportSurveyReport').attr('action', frmUrl);
                $('#export-model-box').modal('show');
            })
            .fail(function(error) {
                toastr.error(error?.responseJSON?.message || "{{ trans('labels.common_title.something_wrong_try_again') }}");
            });
        });

        $(document).on('hidden.bs.modal', '#export-model-box', function (e) {
            $('#frmExportSurveyReport').attr('action', '');
        });

        $('#frmExportSurveyReport').ajaxForm({
            beforeSend: function() {
                $('#export-model-box .modal-header button, #export-model-box .modal-footer button').attr('disabled', 'disabled');
                $('#export-model-box .modal-footer button[type="submit"]').html(`<i class="fa fa-spinner fa-spin"></i>`);
            },
            success: function(data) {
                if(data.status == 1) {
                    toastr.success(data.message);
                    $('#export-model-box').modal('hide');
                } else {
                    toastr.error(data.message);
                }
            },
            error: function(error) {
                toastr.error(error?.responseJSON?.message || "{{ trans('labels.common_title.something_wrong_try_again') }}");
            },
            complete: function(xhr) {
                $('#export-model-box .modal-header button, #export-model-box .modal-footer button').removeAttr('disabled');
                $('#export-model-box .modal-footer button[type="submit"]').html(`{{ trans('labels.buttons.export') }}`);
            }
        });
    });
</script>
@endsection

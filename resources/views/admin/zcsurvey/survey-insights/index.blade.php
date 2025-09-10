@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcsurvey.survey-insights.breadcrumb', [
    'mainTitle' => trans('survey.insights.title.index'),
    'tooltip' => true,
    'breadcrumb' => Breadcrumbs::render('survey.insights.index')
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
                {{ Form::open(['route' => 'admin.surveyInsights.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        @if($SAOnly)
                        <div class="form-group">
                            {{ Form::select('company_id', $company, request()->get('company_id'), ['class' => "form-control select2", 'id'=>'company_id', 'placeholder' => trans('survey.insights.filter.company'), 'data-placeholder' => trans('survey.insights.filter.company'), 'data-allow-clear' => 'true']) }}
                        </div>
                        @endif
                        <div class="form-group">
                            <div class="datepicker-wrap mb-0">
                                {{ Form::text('publish_date', request()->get('publish_date'), ['class' => 'form-control datepicker', 'id' => 'publish_date', 'placeholder' => trans('survey.insights.filter.publish'), 'readonly' => true]) }}
                                <i class="far fa-calendar">
                                    </i>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="datepicker-wrap mb-0">
                                {{ Form::text('expiry_date', request()->get('expiry_date'), ['class' => 'form-control datepicker', 'id' => 'expiry_date', 'placeholder' => trans('survey.insights.filter.expire'), 'readonly' => true]) }}
                                <i class="far fa-calendar">
                                    </i>
                            </div>
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.surveyInsights.index') }}">
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
            <div class="card-header detailed-header small-gap">
                <div class="d-flex flex-wrap">
                    {{ trans('survey.insights.title.subtitle') }}
                    <span class="font-14 ms-2 " data-original-title="{{ trans('survey.insights.title.subtitle_message') }}" data-placement="auto" data-toggle="help-tooltip" title="{{ trans('survey.insights.title.subtitle_message') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="surveyInsightsManagement">
                            <thead>
                                <tr>
                                    <th class="text-center no-sort th-btn-sm">
                                        {{ trans('survey.insights.table.sr_no') }}
                                    </th>
                                    @if($SAOnly)
                                    <th>
                                        {{ trans('survey.insights.table.company_name') }}
                                    </th>
                                    @endif
                                    <th>
                                        {{ trans('survey.insights.table.survey_title') }}
                                    </th>
                                    <th>
                                        {{ trans('survey.insights.table.publish_date') }}
                                    </th>
                                    <th>
                                        {{ trans('survey.insights.table.expiry_date') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('survey.insights.table.no_of_survey_sent') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('survey.insights.table.responses') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('survey.insights.table.retake_response') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('survey.insights.table.response_rate') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('survey.insights.table.percentage') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('survey.insights.table.status') }}
                                    </th>
                                    <th class="text-center no-sort th-btn-sm">
                                        {{ trans('survey.insights.table.view') }}
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
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}" type="text/javascript">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        surveyInsightsManagement,
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        },
        url = {
            datatable: `{{ route('admin.surveyInsights.getSurveyInsights') }}`,
        };

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#publish_date, #expiry_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        });

        surveyInsightsManagement = $('#surveyInsightsManagement').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url.datatable,
                data: {
                    company_id: $('#company_id').val(),
                    publish_date: $('#publish_date').val(),
                    expiry_date: $('#expiry_date').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'id',
                name: 'id',
                searchable: false,
                sortable: false,
                class: 'text-center',
            },
            @if($SAOnly)
            {
                data: 'company_name',
                name: 'company_name',
            },
            @endif
            {
                data: 'survey_title',
                name: 'survey_title',
            },
            {
                data: 'roll_out_date',
                name: 'roll_out_date',
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            },
            {
                data: 'expire_date',
                name: 'expire_date',
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            },
            {
                data: 'surveysent_count',
                name: 'surveysent_count',
                searchable: false,
                class: 'text-center',
            },
            {
                data: 'surveyreponses_count',
                name: 'surveyreponses_count',
                class: 'text-center',
            },
            {
                data: 'retake_response',
                name: 'retake_response',
                class: 'text-center',
            },
            {
                data: 'response_rate',
                name: 'response_rate',
                searchable: false,
                class: 'text-center',
            },
            {
                data: 'percentage',
                name: 'percentage',
                class: 'text-center',
                render: function (data, type, row) {
                    return data + '%';
                }
            },
            {
                data: 'status',
                name: 'status',
                searchable: false,
                class: 'text-center',
            },
            {
                data: 'view',
                name: 'view',
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
            rowCallback: function(row, data, displayNum, displayIndex, dataIndex) {
                var pageInfo = surveyInsightsManagement.page.info();
                $("td:eq(0)", row).html(((pageInfo.page) * pageInfo.length) + displayIndex + 1);
                return row;
            },
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
        });
    });
</script>
@endsection

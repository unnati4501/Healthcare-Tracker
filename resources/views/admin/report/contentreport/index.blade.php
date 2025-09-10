@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.contentreport.breadcrumb',[
    'mainTitle'  => trans('contentreport.title.index_title'),
    'breadcrumb' => 'contentreport.index',
    'back'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('bookingreport.title.filter') }}
                </h4>
                {{ Form::open(['route' => 'admin.reports.content-report', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'detailedTabSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('title', request()->get('title'), ['class' => 'form-control', 'placeholder' => trans('contentreport.filter.title'), 'id' => 'title', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('contentreport.filter.select_company'), 'data-placeholder' => trans('contentreport.filter.select_company'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('type', $contentType, request()->get('type'), ['class' => 'form-control select2', 'id' => 'type', 'placeholder' => trans('contentreport.filter.select_type'), 'data-placeholder' => trans('contentreport.filter.select_type'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group" id="categorybox" style="display:none">
                            {{ Form::select('category', $subcategoryData, request()->get('category'), ['class' => 'form-control select2 w-100', 'id' => 'category', 'placeholder' => trans('contentreport.filter.select_category'), 'data-placeholder' => trans('contentreport.filter.select_category'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group daterange-outer">
                            <div class="input-daterange dateranges justify-content-between mb-0">
                                <div class="datepicker-wrap me-0 mb-0">
                                {{ Form::text('fromdate', request()->get('fromdate'), ['id' => 'fromdate', 'class' => 'form-control datepicker', 'placeholder' => trans('contentreport.filter.from_date'), 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                                <span class="input-group-addon text-center">
                                    -
                                </span>
                                <div class="datepicker-wrap me-0 mb-0">
                                {{ Form::text('todate', request()->get('todate'), ['id' => 'todate', 'class' => 'form-control datepicker', 'placeholder' => trans('contentreport.filter.to_date'), 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon resetSearchBtn" href="{{ route('admin.reports.content-report') }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{trans('buttons.general.reset')}}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card" id="detailed-tab-result-block">
            <div class="card-body">
                <div class="card-table-outer" id="detailedContentReport-wrap">
                    <div class="dt-buttons">
                        <button class="btn btn-primary" id="exportcontentReportbtn" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="detailedContentReport">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">
                                        {{ trans('contentreport.table.title') }}
                                    </th>
                                    <th style="width: 15%;">
                                        {{ trans('contentreport.table.category_name') }}
                                    </th>
                                    <th style="width: 17%;">
                                        {{ trans('contentreport.table.type') }}
                                    </th>
                                    <th style="width: 17%;">
                                        {{ trans('contentreport.table.likes') }}
                                    </th>
                                    <th style="width: 17%;">
                                        {{ trans('contentreport.table.view_counts') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Project Graph Script Code -->
@include('admin.report.contentreport.export-modal')
@endsection
@section('after-scripts')
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>

<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var loginemail = '{{ $loginemail }}';
    var url = {
        dataTable: `{{ route('admin.reports.get-content-report') }}`,
        getCategoryList: `{{ route('admin.reports.get-category-list', [':category']) }}`,
        exportReport: `{{ route('admin.reports.export-content-report') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
    },
    button = {
        export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('customersatisfaction.buttons.export_to_excel') }}',
    },
    datavalue = {
        category: `{{ request()->get('category') }}`,
    },
    message = {
        failed_to_load: `{!! trans('contentreport.message.failed_to_load') !!}`,
        email_required: `{!! trans('contentreport.message.email_required') !!}`,
        valid_email   : `{!! trans('contentreport.message.valid_email') !!}`,
    };
</script>
<script src="{{ mix('js/contentreport/index.js') }}" type="text/javascript">
</script>
@endsection

@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.usage-report.breadcrumb',[
    'mainTitle'  => trans('usage_report.title.index_title'),
    'breadcrumb' => 'usage-report.index',
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
                {{ Form::open(['route' => 'admin.reports.usage-report', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'detailedTabSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('usage_report.filter.select_company'), 'data-placeholder' => trans('usage_report.filter.select_company'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('location', $location, request()->get('location'), ['class' => 'form-control select2', 'disabled' => (request()->get('company')) ? false : true, 'id' => 'location', 'placeholder' => trans('usage_report.filter.select_location'), 'data-placeholder' => trans('usage_report.filter.select_location'), 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon resetSearchBtn" href="{{ route('admin.reports.usage-report') }}">
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
                <div class="card-table-outer" id="usageReport-wrap">
                    <div class="dt-buttons">
                        <button class="btn btn-primary" id="exportUsageReportbtn" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="usageReport">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">
                                        {{ trans('usage_report.table.company_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('usage_report.table.location') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('usage_report.table.registed_user') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('usage_report.table.active_7_days') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('usage_report.table.active_30_days') }}
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
<!-- partipate model Popup -->
@include('admin.report.usage-report.export-modal')
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
    var loginemail = '{{ $loginemail }}',
    url = {
        dataTable: `{{ route('admin.reports.get-usage-report') }}`,
        exportReport: `{{ route('admin.reports.export-content-report') }}`,
        locUrl: '{{ route('admin.ajax.companyLocation', ':id') }}',
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
    },
    button = {
        export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('usage-report.buttons.export_to_excel') }}',
    },
    message = {
        failed_to_load: `{!! trans('usage-report.messages.failed_to_load') !!}`,
        email_required: `{!! trans('usage-report.messages.email_required') !!}`,
        valid_email   : `{!! trans('usage-report.messages.valid_email') !!}`,
    };
</script>
<script src="{{ mix('js/usagereport/index.js') }}" type="text/javascript">
</script>
@endsection

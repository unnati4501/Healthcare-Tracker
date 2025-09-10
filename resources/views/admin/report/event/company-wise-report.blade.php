@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
<style type="text/css">
    .hidden { display: none; }
    body.modal-open { overflow: visible !important; }
    .f-400 { font-weight: 400 !important; }
</style>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.event.breadcrumb',[
    'mainTitle'  => trans('bookingreport.summarytab.title.index_title'),
    'breadcrumb' => 'bookingreport.summary',
    'back'       => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">{{ trans('bookingreport.summarytab.title.filter') }}</h4>
                {{ Form::open(['route' => ['admin.reports.booking-report-comapny-wise', $company->id], 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'companyWiseSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                <div>
                    <div class="form-group">
                        {{ Form::select('presenter', $presenters, request()->get('presenter'), ['class' => 'form-control select2', 'id' => 'presenter', 'placeholder' => trans('bookingreport.summarytab.filter.select_presenter'), 'data-placeholder' => trans('bookingreport.summarytab.filter.select_presenter'), 'data-allow-clear' => 'true']) }}
                    </div>
                    <div class="form-group daterange-outer">
                        <div class="input-daterange dateranges justify-content-between mb-0">
                            <div class="datepicker-wrap me-0 mb-0 ">
                                {{ Form::text('fromdate', request()->get('fromdate'), ['id' => 'fromdate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.summarytab.filter.from_date'), 'readonly' => true]) }}
                                <i class="far fa-calendar"></i>
                            </div>
                            <span class="input-group-addon text-center">
                                -
                            </span>
                            <div class="datepicker-wrap me-0 mb-0 ">
                                {{ Form::text('todate', request()->get('todate'), ['id' => 'todate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.summarytab.filter.to_date'), 'readonly' => true]) }}
                                <i class="far fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {{ Form::select('status', $status, request()->get('status'), ['class' => 'form-control select2', 'id' => 'status', 'placeholder' => trans('bookingreport.summarytab.filter.select_status'), 'data-placeholder' => trans('bookingreport.summarytab.filter.select_status'), 'data-allow-clear' => 'true']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::select('complementary', ['1' => 'Yes', 0 => 'No'], request()->get('complementary'), ['class' => 'form-control select2', 'id' => 'complementary', 'placeholder' => trans('bookingreport.summarytab.filter.is_complementary'), 'data-placeholder' => trans('bookingreport.summarytab.filter.is_complementary'), 'data-allow-clear' => 'true']) }}
                    </div>
                </div>
                <div class="search-actions align-self-start">
                    <button class="me-md-4 filter-apply-btn" type="submit">
                        {{trans('buttons.general.apply')}}
                    </button>
                    <a class="filter-cancel-icon" href="{{ route('admin.reports.booking-report-comapny-wise', $company->id) }}">
                        <i class="far fa-times">
                        </i>
                        <span class="d-md-none ms-2 ms-md-0">{{trans('buttons.general.reset')}}</span>
                    </a>
                </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="detailedReportManagement-wrap">
                <div class="dt-buttons">
                    <button class="btn btn-primary exportBookingHistoryReport" data-end="" data-start="" id="exportBookingHistoryReport" data-title="Export Booking Detail History Report" type="button">
                        <span>
                            <i class="far fa-envelope me-3 align-middle">
                            </i>
                            {{trans('buttons.general.export')}}
                        </span>
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table custom-table" id="detailedReportManagement">
                        <thead>
                            <tr>
                                <th style="width: 20%;">
                                    {{ trans('bookingreport.summarytab.table.event_name') }}
                                </th>
                                <th style="width: 15%;">
                                    {{ trans('bookingreport.summarytab.table.presenter') }}
                                </th>
                                <th class="text-center" style="width: 17%;">
                                    {{ trans('bookingreport.summarytab.table.date_time') }}
                                </th>
                                <th style="width: 17%;">
                                    {{ trans('bookingreport.summarytab.table.create_by') }}
                                </th>
                                <th class="text-center" style="width: 17%;">
                                    {{ trans('bookingreport.summarytab.table.location') }}
                                </th>
                                <th class="text-center" style="width: 17%;">
                                    {{ trans('bookingreport.summarytab.table.participants') }}
                                </th>
                                <th class="text-center" style="width: 10%;">
                                    {{ trans('bookingreport.summarytab.table.billable') }}
                                </th>
                                <th class="text-center th-btn-2">
                                    {{ trans('bookingreport.summarytab.table.complementary') }}
                                </th>
                                <th class="text-center no-sort">
                                    {{ trans('bookingreport.summarytab.table.status') }}
                                </th>
                                <th class="text-center th-btn-1 no-sort">
                                    {{ trans('bookingreport.summarytab.table.action') }}
                                </th>
                                <th class="text-center th-btn-1 no-sort" style="display: none">
                                    {{ trans('bookingreport.summarytab.table.cancelled_by') }}
                                </th>
                                <th class="text-center th-btn-1 no-sort" style="display: none">
                                    {{ trans('bookingreport.summarytab.table.cancelled_date') }}
                                </th>
                                <th class="text-center th-btn-1 no-sort" style="display: none">
                                    {{ trans('bookingreport.summarytab.table.cancelled_reason') }}
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
@include('admin.report.event.cancellationdetails-modal')
@include('admin.report.export_modal')
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
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var timezone = '{{ $timezone }}',
    loginemail = '{{ $loginemail }}';
    var url = {
        dataTable: `{{ route('admin.reports.booking-report-comapny-wise', $company->id) }}`,
        cancelUrl: `{{ route('admin.event.cancelEventDetails', [':bid']) }}`,
        exportBookingHistoryReportUrl:`{{route('admin.reports.exportBookingReportCompanyWise', $company->id)}}`,
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
    data = {
        isExportButton: `{{ $isExportButton }}`,
    },
    message = {
        failed_to_load: `{!! trans('bookingreport.summarytab.message.failed_to_load') !!}`,
    };
</script>
<script src="{{ mix('js/bookingreport/details.js') }}" type="text/javascript">
</script>
@endsection

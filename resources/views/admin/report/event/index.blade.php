@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/fullCalendar/main.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/toastr/toastr.min.css?var='.rand()) }}" rel="stylesheet"/>
<link rel="stylesheet" href="{{ asset('assets/plugins/qtip/jquery.qtip.min.css?var='.rand()) }}" />
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.event.breadcrumb',[
    'mainTitle'  => trans('bookingreport.title.index_title'),
    'breadcrumb' => 'bookingreport.index',
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
<div class="container-fluid">
    <!-- Nav tabs -->
    <div class="nav-tabs-wrap">
        @if(!in_array($roleType, ['rca', 'zca']))
        <ul class="nav nav-tabs tabs-line-style" id="reportTab" role="tablist">
            <li class="nav-item">
                <a aria-controls="{{ trans('labels.event_booking_report.detailed_tab') }}" aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#detailed-view-tab" role="tab">
                    {{ trans('bookingreport.title.detailed_tab') }}
                </a>
            </li>
            <li class="nav-item">
                <a aria-controls="{{ trans('labels.event_booking_report.summary_tab') }}" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#summary-view-tab" role="tab">
                    {{ trans('bookingreport.title.summary_tab') }}
                </a>
            </li>
            <li class="nav-item">
                <a aria-controls="{{ trans('labels.event_booking_report.calendar_view') }}" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#calender-view-tab" role="tab">
                    {{ trans('bookingreport.title.calendar_view') }}
                </a>
            </li>
        </ul>
        @endif
        <div class="tab-content" id="eventTabListContent">
            <div aria-labelledby="detailed-view-tab" class="tab-pane fade show active" id="detailed-view-tab" role="tabpanel">
                <div class="card search-card">
                    <div class="card-body pb-0">
                        <h4 class="d-md-none">{{ trans('bookingreport.title.filter') }}</h4>
                        {{ Form::open(['route' => 'admin.reports.booking-report', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'detailedTabSearch']) }}
                        <div class="search-outer d-md-flex justify-content-between">
                            <div>
                                <div class="form-group">
                                    {{ Form::text('dtName', request()->get('dtName'), ['class' => 'form-control', 'placeholder' => trans('bookingreport.filter.event_name'), 'id' => 'dtName', 'autocomplete' => 'off']) }}
                                </div>
                                <div class="form-group">
                                    @if($company && !$company->is_reseller)
                                    {{ Form::text('', $company->name, ['class' => 'form-control', 'disabled' => true]) }}
                                    {{ Form::hidden('dtCompany', $company->id, ['id' => 'dtCompany']) }}
                                    @else
                                    {{ Form::select('dtCompany', $companies, request()->get('dtCompany'), ['class' => 'form-control select2', 'id' => 'dtCompany', 'placeholder' => trans('bookingreport.filter.select_company'), 'data-placeholder' => trans('bookingreport.filter.select_company'), 'data-allow-clear' => 'true']) }}
                                    @endif
                                </div>
                                <div class="form-group">
                                    {{ Form::select('dtPresenter', $presenters, request()->get('dtPresenter'), ['class' => 'form-control select2', 'id' => 'dtPresenter', 'placeholder' => trans('bookingreport.filter.select_presenter'), 'data-placeholder' => trans('bookingreport.filter.select_presenter'), 'data-allow-clear' => 'true']) }}
                                </div>
                                <div class="form-group">
                                    {{ Form::select('dtCategory', $categories, request()->get('dtCategory'), ['class' => 'form-control select2', 'id' => 'dtCategory', 'placeholder' => trans('marketplace.filter.category'), 'data-placeholder' => trans('marketplace.filter.category'), 'data-allow-clear' => 'true']) }}
                                </div>
                                <div class="form-group daterange-outer">
                                    <div class="input-daterange dateranges justify-content-between mb-0">
                                        <div class="datepicker-wrap me-0 mb-0 ">
                                            {{ Form::text('dtFromdate', request()->get('dtFromdate'), ['id' => 'dtFromdate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.filter.from_date'), 'readonly' => true]) }}
                                            <i class="far fa-calendar"></i>
                                        </div>
                                        <span class="input-group-addon text-center">
                                           -
                                        </span>
                                        <div class="datepicker-wrap me-0 mb-0 ">
                                            {{ Form::text('dtTodate', request()->get('dtTodate'), ['id' => 'dtTodate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.filter.to_date'), 'readonly' => true]) }}
                                            <i class="far fa-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    {{ Form::select('dtComplementary', ['1' => 'Yes', 0 => 'No'], request()->get('dtComplementary'), ['class' => 'form-control select2', 'id' => 'dtComplementary', 'placeholder' => trans('bookingreport.filter.is_complementary'), 'data-placeholder' => trans('bookingreport.filter.is_complementary'), 'data-allow-clear' => 'true']) }}
                                </div>
                                <div class="form-group">
                                    {{ Form::select('dtStatus', $status, request()->get('dtStatus'), ['class' => 'form-control select2', 'id' => 'dtStatus', 'placeholder' => trans('bookingreport.filter.select_status'), 'data-placeholder' => trans('bookingreport.filter.select_status'), 'data-allow-clear' => 'true']) }}
                                </div>
                            </div>
                            <div class="search-actions align-self-start">
                                <button class="me-md-4 filter-apply-btn" type="submit">
                                    {{trans('buttons.general.apply')}}
                                </button>
                                <a href="javascript:void(0);" class="filter-cancel-icon resetSearchBtn"><i class="far fa-times"></i><span class="d-md-none ms-2 ms-md-0">{{trans('buttons.general.reset')}}</span></a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
                <div class="card" id="detailed-tab-result-block">
                    <div class="card-body">
                        <div class="card-table-outer" id="detailedReportManagement-wrap">
                        {{-- <div class="text-end">
                            <a class="action-icon btn btn-primary" href="javaScript:void(0)" data-end="" data-start="" id="bookingReportDetails" data-title="Export Booking Report With Details" title="{{ trans('challenges.buttons.tooltips.export') }}">
                                <i aria-hidden="true" class="far fa-download">Export Excel
                                </i>
                            </a>
                        </div> --}}
                        <div class="dt-buttons">
                            <button class="btn btn-primary exportBookingDetailReport" data-end="" data-start="" data-tab="booking-details" id="exportBookingReport" data-title="Export Booking Report With Details" type="button">
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
                                            {{ trans('bookingreport.table.event_name') }}
                                        </th>
                                        <th style="width: 15%;">
                                            {{ trans('bookingreport.table.presenter') }}
                                        </th>
                                        <th style="width: 15%;">
                                            {{ trans('bookingreport.table.category') }}
                                        </th>
                                        <th style="width: 17%;">
                                            {{ trans('bookingreport.table.date_time') }}
                                        </th>
                                        <th style="width: 17%;">
                                            {{ trans('bookingreport.table.create_by') }}
                                        </th>
                                        <th style="width: 17%;">
                                            {{ trans('bookingreport.table.company_name') }}
                                        </th>
                                        <th style="width: 10%;">
                                            {{ trans('bookingreport.table.location') }}
                                        </th>
                                        <th style="width: 10%;">
                                            {{ trans('bookingreport.table.billable') }}
                                        </th>
                                        <th class="th-btn-2">
                                            {{ trans('bookingreport.table.complementary') }}
                                        </th>
                                        <th class="no-sort">
                                            {{ trans('bookingreport.table.status') }}
                                        </th>
                                        <th class="th-btn-1 no-sort novis">
                                            {{ trans('bookingreport.table.action') }}
                                        </th>
                                        <th class="th-btn-1 no-sort" style="display: none">
                                            {{ trans('bookingreport.table.cancelled_by') }}
                                        </th>
                                        <th class="th-btn-1 no-sort" style="display: none">
                                            {{ trans('bookingreport.table.cancelled_date') }}
                                        </th>
                                        <th class="th-btn-1 no-sort" style="display: none">
                                            {{ trans('bookingreport.table.cancelled_reason') }}
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(!in_array($roleType, ['rca', 'zca']))
            <div aria-labelledby="summary-view-tab" class="tab-pane fade" id="summary-view-tab" role="tabpanel">
                <div class="card search-card" id="searchpanel">
                    <div class="card-body pb-0">
                        <h4 class="d-md-none">{{ trans('bookingreport.title.filter') }}</h4>
                        {{ Form::open(['route' => 'admin.reports.booking-report', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'summaryTabSearch']) }}
                        <div class="search-outer d-md-flex justify-content-between">
                            <div>
                                <div class="form-group">
                                    {{ Form::select('stCompany', $companies, request()->get('stCompany'), ['class' => 'form-control select2', 'id' => 'stCompany', 'placeholder' => trans('bookingreport.filter.select_company'), 'data-placeholder' => trans('bookingreport.filter.select_company'), 'data-allow-clear' => 'true']) }}
                                </div>
                                <div class="form-group6">
                                    {{ Form::select('stStatus', $summaryStatus, request()->get('stStatus'), ['class' => 'form-control select2', 'id' => 'stStatus', 'placeholder' => trans('bookingreport.filter.select_status'), 'data-placeholder' => trans('bookingreport.filter.select_status'), 'data-allow-clear' => 'true']) }}
                                </div>
                            </div>
                            <div class="search-actions align-self-start">
                                <button class="me-md-4 filter-apply-btn" type="submit">
                                    {{trans('buttons.general.apply')}}
                                </button>
                                <a href="javascript:void(0);" class="filter-cancel-icon resetSearchBtn"><i class="far fa-times"></i><span class="d-md-none ms-2 ms-md-0">{{trans('buttons.general.reset')}}</span></a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
                <div class="card" id="summary-tab-result-block">
                    <div class="card-body">
                        <div class="card-table-outer" id="detailedViewTable-wrap">
                            {{-- <div class="text-end">
                                <a class="action-icon btn btn-primary" href="javaScript:void(0)" data-end="" data-start="" id="bookingReportSummary" data-title="Export Booking Report Summary" title="{{ trans('challenges.buttons.tooltips.export') }}">
                                    <i aria-hidden="true" class="far fa-download">Export Excel
                                    </i>
                                </a>
                            </div> --}}
                            <div class="dt-buttons">
                                <button class="btn btn-primary exportBookingSummaryReport" data-end="" data-start="" data-tab="booking-summary" id="exportBookingReport" data-title="Export Booking Report With Summary" type="button">
                                    <span>
                                        <i class="far fa-envelope me-3 align-middle">
                                        </i>
                                        {{trans('buttons.general.export')}}
                                    </span>
                                </button>
                            </div>
                            <div class="table-responsive">
                            <table class="table custom-table" id="summaryReportManagement">
                                <thead>
                                    <tr>
                                        <th>
                                            {{ trans('bookingreport.table.company_name') }}
                                        </th>
                                        <th class="text-center">
                                            {{ trans('bookingreport.table.total_events') }}
                                        </th>
                                        <th class="text-center">
                                            {{ trans('bookingreport.table.booked') }}
                                        </th>
                                        <th class="text-center">
                                            {{ trans('bookingreport.table.cancelled') }}
                                        </th>
                                        <th class="text-center">
                                            {{ trans('bookingreport.table.billable') }}
                                        </th>
                                        <th class="text-center no-sort">
                                            {{ trans('bookingreport.table.status') }}
                                        </th>
                                        <th class="text-center th-btn-1 no-sort novis">
                                            {{ trans('bookingreport.table.action') }}
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div aria-labelledby="calender-view-tab" class="tab-pane fade" id="calender-view-tab" role="tabpanel">
                @include('admin.report.event.fullcalendarreport')
            </div>
            @endif
        </div>
    </div>
</div>
</section>
<!-- /.modals -->
@include('admin.report.event.cancellationdetails-modal')
@include('admin.report.export_modal')
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/fullCalendar/main.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/toastr/toastr.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/qtip/jquery.qtip.min.js?var='.rand()) }}">
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
    var timezone = '{{ $timezone }}',
    loginemail = '{{ $loginemail }}';
    var url = {
        detailedReport: `{{ route('admin.reports.detailed-report') }}`,
        summaryReport: `{{ route('admin.reports.summary-report') }}`,
        cancelEventDetails: `{{ route('admin.event.cancelEventDetails', [':bid']) }}`,
        calendarReport: `{{ route('admin.reports.calendar-report') }}`,
        bookingReportExportUrl:`{{route('admin.reports.exportBookingDetailReport')}}`,
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
        roleType: `{{ $roleType }}`,
    },
    message = {
        failed_to_load: `{!! trans('bookingreport.message.failed_to_load') !!}`,
    };
</script>
<script src="{{ mix('js/bookingreport/index.js') }}" type="text/javascript">
</script>
<script src="{{ mix('js/bookingreport/customcalendar.js') }}" type="text/javascript">
</script>
@endsection

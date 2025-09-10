@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.occupational-health.breadcrumb',[
    'mainTitle'  => trans('occupationalHealthReport.title.index_title'),
    'breadcrumb' => 'occupational-health.index',
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
                {{ Form::open(['route' => 'admin.reports.occupational-health', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'detailedTabSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('occupationalHealthReport.filter.select_company'), 'data-placeholder' => trans('occupationalHealthReport.filter.select_company'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group daterange-outer">
                            <div class="input-daterange dateranges justify-content-between mb-0">
                                <div class="datepicker-wrap me-0 mb-0 ">
                                    {{ Form::text('fromDate', request()->get('fromDate'), ['id' => 'fromDate', 'class' => 'form-control datepicker', 'placeholder' => trans('occupationalHealthReport.filter.from_date'), 'readonly' => true]) }}
                                    <i class="far fa-calendar"></i>
                                </div>
                                <span class="input-group-addon text-center">
                                   -
                                </span>
                                <div class="datepicker-wrap me-0 mb-0 ">
                                    {{ Form::text('toDate', request()->get('toDate'), ['id' => 'toDate', 'class' => 'form-control datepicker', 'placeholder' => trans('occupationalHealthReport.filter.to_date'), 'readonly' => true]) }}
                                    <i class="far fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            {{ Form::select('wellbeingSpecialist', $wellbeingSpecialists, request()->get('wellbeingSpecialist'), ['class' => 'form-control select2', 'id' => 'wellbeingSpecialist', 'placeholder' => trans('occupationalHealthReport.filter.select_wellbeing_specialist'), 'data-placeholder' => trans('occupationalHealthReport.filter.select_wellbeing_specialist'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('userName', request()->get('userName'), ['class' => 'form-control', 'placeholder' => trans('occupationalHealthReport.filter.user_name'), 'id' => 'userName', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon resetSearchBtn" href="{{ route('admin.reports.occupational-health') }}">
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
                <div class="card-table-outer" id="occupationalHealthReport-wrap">
                    <div class="dt-buttons">
                        <button class="btn btn-primary" id="exportOccupationalHealthReportbtn" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="occupationalHealthReport">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.id') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.user_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.user_email') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.company_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.date_added') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.confirmation_client') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.confirmation_date') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.note') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.attended') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.wellbeing_sepecialist_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('occupationalHealthReport.table.referred_by') }}
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
@include('admin.report.occupational-health.export-modal')
<div class="modal fade" data-backdrop="static" data-id="0" data-keyboard="false" id="diplay-note-model" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('View Note') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body" id="modal_body">
                
            </div>
        </div>
    </div>
</div>
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
        dataTable: `{{ route('admin.reports.get-occupational-health-report') }}`,
        exportReport: `{{ route('admin.reports.export-content-report') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
    },
    button = {
        export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('occupationalHealthReport.buttons.export_to_excel') }}',
    },
    message = {
        failed_to_load: `{!! trans('occupationalHealthReport.messages.failed_to_load') !!}`,
        email_required: `{!! trans('occupationalHealthReport.messages.email_required') !!}`,
        valid_email   : `{!! trans('occupationalHealthReport.messages.valid_email') !!}`,
    };
</script>
<script src="{{ mix('js/occupationalhealthreport/index.js') }}" type="text/javascript">
</script>
@endsection

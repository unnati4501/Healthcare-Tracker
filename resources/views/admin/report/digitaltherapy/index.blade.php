@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.digitaltherapy.breadcrumb',[
    'mainTitle'  => trans('digitaltheraphyreport.title.index_title'),
    'breadcrumb' => 'digitaltherapy.index',
    'back'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        @if($loggedInUserRole->slug == 'wellbeing_team_lead')
        <div class="nav-tabs-wrap">
            <ul class="nav nav-tabs tabs-line-style" id="myTab" role=" tablist">
            <li class="nav-item">
                <a aria-controls="Single" class="nav-link active" aria-selected="true" data-for="single" href="{{ request()->fullUrlWithQuery(['tab' => 'single']) }}" id="tab-single-session" role="tab">
                    {{ trans('Cronofy.session_list.title.single_session') }}
                </a>
            </li>
            <li class="nav-item">
                <a aria-controls="Group" class="nav-link" aria-selected="false" data-for="group" href="{{ request()->fullUrlWithQuery(['tab' => 'group']) }}" id="tab-group-session" role="tab">
                    {{ trans('Cronofy.session_list.title.group') }}
                </a>
            </li>
            </ul>
        </div>
        @endif
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('bookingreport.title.filter') }}
                </h4>
                {{ Form::open(['route' => 'admin.reports.digital-therapy', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'detailedTabSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('digitaltheraphyreport.filter.select_company'), 'data-placeholder' => trans('digitaltheraphyreport.filter.select_company'), 'data-allow-clear' => 'true']) }}
                        </div>
                        @if($loggedInUserRole->slug == 'wellbeing_team_lead' || ($loggedInUserRole->slug == 'super_admin' && $loggedInUserRole->group == 'zevo'))
                            <div class="form-group">
                                {{ Form::select('wellbeingSpecialist', $wellbeingSpecialists, request()->get('wellbeingSpecialist'), ['class' => 'form-control select2', 'id' => 'wellbeingSpecialist', 'placeholder' => trans('digitaltheraphyreport.filter.select_wellbeing_specialist'), 'data-placeholder' => trans('occupationalHealthReport.filter.select_wellbeing_specialist'), 'data-allow-clear' => 'true']) }}
                            </div>
                        @endif
                        <div class="form-group">
                            {{ Form::select('dtStatus', $status, request()->get('dtStatus'), ['class' => 'form-control select2', 'id' => 'dtStatus', 'placeholder' => trans('digitaltheraphyreport.filter.select_status'), 'data-placeholder' => trans('digitaltheraphyreport.filter.select_status'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group daterange-outer">
                            <div class="input-daterange dateranges justify-content-between mb-0">
                                <div class="datepicker-wrap me-0 mb-0 ">
                                    {{ Form::text('dtFromdate', request()->get('dtFromdate'), ['id' => 'dtFromdate', 'class' => 'form-control datepicker', 'placeholder' => trans('digitaltheraphyreport.filter.from_date'), 'readonly' => true]) }}
                                    <i class="far fa-calendar"></i>
                                </div>
                                <span class="input-group-addon text-center">
                                   -
                                </span>
                                <div class="datepicker-wrap me-0 mb-0 ">
                                    {{ Form::text('dtTodate', request()->get('dtTodate'), ['id' => 'dtTodate', 'class' => 'form-control datepicker', 'placeholder' => trans('digitaltheraphyreport.filter.to_date'), 'readonly' => true]) }}
                                    <i class="far fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            {{ Form::select('dtService', $service, request()->get('dtService'), ['class' => 'form-control select2', 'id' => 'dtService', 'placeholder' => trans('digitaltheraphyreport.filter.select_service'), 'data-placeholder' => trans('digitaltheraphyreport.filter.select_service'), 'data-allow-clear' => 'true']) }}
                        </div>
                        @if($loggedInUserRole->slug == 'wellbeing_team_lead')
                        <div class="form-group usersearch">
                            {{ Form::text('user', request()->get('user'), ['class' => 'form-control', 'placeholder' => trans('Cronofy.session_list.filters.client_name_email'), 'id' => 'user', 'autocomplete' => 'off']) }}
                        </div>
                        <input type="hidden" id="tab" name="tab" value="single">
                        @else
                        <input type="hidden" id="tab" name="tab" value="none">
                        @endif
                        @if($loggedInUserRole->slug == 'wellbeing_team_lead')
                        <div class="form-group createdByDiv">
                            {{ Form::select('created_by', $createdByArray, request()->get('created_by'), ['class' => 'form-control select2', 'id' => 'created_by', 'placeholder' => trans('digitaltheraphyreport.filter.select_created_by'), 'data-placeholder' => trans('digitaltheraphyreport.filter.select_created_by'), 'data-allow-clear' => 'true']) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon resetSearchBtn" href="{{ route('admin.reports.digital-therapy') }}">
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
                <div class="card-table-outer" id="detailedDigitalTherapyReport-wrap">
                    <div class="dt-buttons">
                        <button class="btn btn-primary" id="exportdigitaltherapyReportbtn" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        @if($loggedInUserRole->slug != 'wellbeing_team_lead')  
                        <table class="table custom-table" id="detailedDigitalTherapyReport">
                            <thead>
                                <tr>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.company_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.service_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.issue') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.location') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.department') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.booking_date') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.session_date') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.duration') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.mode_of_service') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.wellbeing_sepecialist_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.ws_timezone') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.ws_shift') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.number_of_participants') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.status') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        @else
                        <table class="table custom-table d-none" id="detailedDigitalTherapyReportwbtl">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.user') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.client_email') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.company_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.service_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.issue') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.location') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.department') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.booking_date') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.session_date') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.duration') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.mode_of_service') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.wellbeing_sepecialist_name') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.ws_timezone') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.ws_shift') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.number_of_participants') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.status') }}
                                    </th>
                                    <th style="width: 20%;">
                                        {{ trans('digitaltheraphyreport.table.created_by') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- partipate model Popup -->
@include('admin.report.digitaltherapy.visibleparticipate-model')
@include('admin.report.digitaltherapy.export_modal')
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
        timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
    url = {
        dataTable: `{{ route('admin.reports.get-digital-therapy-report') }}`,
        exportReport: `{{ route('admin.reports.export-content-report') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
    },
    button = {
        export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('digitaltheraphyreport.buttons.export_to_excel') }}',
    },
    roleGroup = `{{ $loggedInUserRole->group }}`,
    roleSlug = `{{ $loggedInUserRole->slug }}`,
    message = {
        failed_to_load: `{!! trans('digitaltheraphyreport.messages.failed_to_load') !!}`,
        email_required: `{!! trans('digitaltheraphyreport.messages.email_required') !!}`,
        valid_email   : `{!! trans('digitaltheraphyreport.messages.valid_email') !!}`,
    };
</script>
<script src="{{ mix('js/digitaltherapyreport/index.js') }}" type="text/javascript">
</script>
@endsection

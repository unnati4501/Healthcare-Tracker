@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.breadcrumb',[
    'mainTitle'  => trans('userregistration.title.index_title'),
    'breadcrumb' => 'userregistration.index',
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
                    {{ trans('userregistration.title.filter') }}
                </h4>
                {{ Form::open(['route' => 'admin.reports.user-registration', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'detailedTabSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            @if($userCompany && !$userCompany->is_reseller)
                            {{ Form::text('', $userCompany->name, ['class' => 'form-control', 'disabled' => true]) }}
                            {{ Form::hidden('company', $userCompany->id, ['id' => 'dtCompany']) }}
                            @else
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('userregistration.filter.select_company'), 'data-placeholder' => trans('bookingreport.filter.select_company'), 'data-allow-clear' => 'true']) }}
                            @endif
                        </div>
                        <div class="form-group daterange-outer">
                            <div class="input-daterange dateranges justify-content-between mb-0">
                                <div class="datepicker-wrap me-0 mb-0">
                                {{ Form::text('fromDate', request()->get('fromDate'), ['id' => 'fromDate', 'class' => 'form-control datepicker', 'placeholder' => trans('userregistration.filter.from_date'), 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                                <span class="input-group-addon text-center">
                                    -
                                </span>
                                <div class="datepicker-wrap me-0 mb-0">
                                {{ Form::text('toDate', request()->get('toDate'), ['id' => 'toDate', 'class' => 'form-control datepicker', 'placeholder' => trans('userregistration.filter.to_date'), 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            {{ Form::select('rolename', $role_name, request()->get('rolename'), ['class' => 'form-control select2', 'id' => 'rolename', 'placeholder' => trans('userregistration.filter.select_role_name'), 'data-placeholder' => trans('userregistration.filter.select_role_name'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('rolegroup', $role_group, request()->get('rolegroup'), ['class' => 'form-control select2', 'id' => 'rolegroup', 'placeholder' => trans('userregistration.filter.select_role_group'), 'data-placeholder' => trans('userregistration.filter.select_role_group'), 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon resetSearchBtn" href="{{ route('admin.reports.user-registration') }}">
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
                <div class="card-table-outer" id="userManagment-wrap">
                    <div class="dt-buttons">
                        <button class="btn btn-primary" id="userRegistrationExport" type="button" data-title="Export User Registration">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="userManagment">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('userregistration.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('userregistration.table.full_name') }}
                                    </th>
                                    <th>
                                        {{ trans('userregistration.table.email') }}
                                    </th>
                                    <th>
                                        {{ trans('userregistration.table.role') }}
                                    </th>
                                    <th>
                                        {{ trans('userregistration.table.rolegroup') }}
                                    </th>
                                    <th>
                                        {{ trans('userregistration.table.company') }}
                                    </th>
                                    <th>
                                        {{ trans('userregistration.table.department') }}
                                    </th>
                                    <th>
                                        {{ trans('userregistration.table.location') }}
                                    </th>
                                    <th>
                                        {{ trans('userregistration.table.team_name') }}
                                    </th>
                                    <th>
                                        {{ trans('userregistration.table.registration_date') }}
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
<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<!-- App Graph Script Code -->
<script type="text/javascript">
    
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        loginemail = '{{ $loginemail }}',
        startDate = '{{ config('zevolifesettings.challenge_set_date.before') }}',
        endDate = '{{ config('zevolifesettings.challenge_set_date.after') }}';
    var url = {
        getUsersDt: `{{ route('admin.reports.get-user-registration') }}`,
        userRegistrationExportUrl:`{{route('admin.reports.exportUserRegistrationReport')}}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
    },
    button = {
        export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('userregistration.buttons.export_to_excel') }}',
    };
</script>
<script src="{{ mix('js/userregistration/index.js') }}" type="text/javascript">
</script>
@endsection


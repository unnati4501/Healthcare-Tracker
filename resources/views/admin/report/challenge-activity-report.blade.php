@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
<style type="text/css">
    .hidden { display: none !important; }
</style>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.breadcrumb',[
    'mainTitle'  => trans('challengeactivity.title.challenge-activity-report'),
    'breadcrumb' => 'report.challengeactivity',
    'back'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="nav-tabs-wrap">
            <ul class="nav nav-tabs tabs-line-style" id="myTab" role=" tablist">
            <li class="nav-item">
                <a aria-controls="Summary" class="nav-link active" aria-selected="true" data-for="summary" href="{{ request()->fullUrlWithQuery(['tab' => 'summary']) }}" id="tab-summary" role="tab">
                    {{ trans('challengeactivity.title.summary') }}
                </a>
            </li>
            <li class="nav-item">
                <a aria-controls="Details" class="nav-link" aria-selected="false" data-for="details" href="{{ request()->fullUrlWithQuery(['tab' => 'detail']) }}" id="tab-details" role="tab">
                    {{ trans('challengeactivity.title.details') }}
                </a>
            </li>
            <li class="nav-item">
                <a aria-controls="Daily Summary" class="nav-link" aria-selected="false" data-for="userwise" href="{{ request()->fullUrlWithQuery(['tab' => 'userwise']) }}" id="tab-userwise" role="tab">
                    {{ trans('challengeactivity.title.daily_summary') }}
                </a>
            </li>
            </ul>
        </div>
        <div class="card search-card">
            <div class="card-body pb-0" style="display: block;">
                <h4 class="d-md-none">{{ trans('challengeactivity.title.filter') }}</h4>
                {{ Form::open(['route' => 'admin.reports.challengeactivityreport', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'challengeActivityReportSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <input type="hidden" id="tab" name="tab" value="summary">
                        <div class="form-group">
                            {{ Form::select('challengeStatus', $challengeStatus, request()->get('challengeStatus'), ['class' => 'form-control select2 getChallengeList', 'id'=>'challengeStatus', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('challengeactivity.filter.select_challengestatus'), 'data-placeholder' => trans('challengeactivity.filter.select_challengestatus'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('challengeType', $challengeType, request()->get('challengeType'), ['class' => 'form-control getChallengeList select2', 'id'=>'challengeType', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('challengeactivity.filter.select_challengetype'), 'data-placeholder' => trans('challengeactivity.filter.select_challengetype'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('challenge', $challengeList ?? [], request()->get('challenge'), ['class' => 'form-control select2', 'id'=>'challenge', 'style'=>'width: 100%;', 'placeholder' => trans('challengeactivity.filter.select_challenge'), 'data-placeholder' => trans('challengeactivity.filter.select_challenge'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('company', $challengeParticipants['companyList'] ?? [], request()->get('company'), ['class' => 'form-control select2', 'id'=>'company', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('challengeactivity.filter.select_company'), 'data-placeholder' => trans('challengeactivity.filter.select_company'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('team', $teamList ?? [], request()->get('team'), ['class' => 'form-control select2', 'id'=>'team', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('challengeactivity.filter.select_team'), 'data-placeholder' => trans('challengeactivity.filter.select_team'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div id="usersearch" class="form-group hidden">
                            {{ Form::text('userrecordsearch', request()->get('userrecordsearch'), ['class' => 'form-control', 'placeholder' => trans('challengeactivity.filter.search_by_nameemail'), 'id' => 'userrecordsearch', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.reports.challengeactivityreport') }}">
                            <i class="far fa-times"></i>
                            <span class="d-md-none ms-2 ms-md-0">{{trans('buttons.general.reset')}}</span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer tab-pane" id="tab-summary">
                    <div class="dt-buttons challengeSummaryReport-export">
                        <button class="btn btn-primary" data-end="" data-start="" data-tab="summary" id="challengeActivityReport" data-title="Export Summary Report" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="challengeSummaryReport">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('challengeactivity.table.user_name') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeactivity.table.email') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeactivity.table.company_name') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeactivity.table.team_name') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeactivity.table.target_type') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeactivity.table.Counts') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeactivity.table.points') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center no-data-table" colspan="5">{{ trans('challengeactivity.message.select_challenge_view_report') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-table-outer tab-pane" id="tab-details">
                    <div class="dt-buttons challengeDetailsReport-export">
                        <button class="btn btn-primary" data-end="" data-start="" data-tab="details" id="challengeActivityReport" data-title="Export Detailed Report" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive hidden">
                    <table class="table custom-table" id="challengeDetailsReport">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('challengeactivity.table.user_name') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.email') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.company_name') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.team_name') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.tracker_name') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.target_type') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.Counts') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.points') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.sync_date') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.log_date') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center no-data-table" colspan="5">{{ trans('challengeactivity.message.select_challenge_view_report') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="card-table-outer tab-pane" id="tab-userwise">
                    <div class="dt-buttons challengeUserWiseReport-export">
                        <button class="btn btn-primary" data-end="" data-start="" data-tab="daily-summary" id="challengeActivityReport" data-title="Export Daily Summary Report" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive hidden">
                    <table class="table custom-table" id="challengeUserWiseReport">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('challengeactivity.table.user_name') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.email') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.company_name') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.team_name') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.tracker_name') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.tracker_change') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.target_type') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.Counts') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.points') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.table.log_date') }}
                                </th>
                                <th class="no-sort">
                                    {{ trans('challengeactivity.table.action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center no-data-table" colspan="5">{{ trans('challengeactivity.message.select_challenge_view_report') }}</td>
                            </tr>
                        </tbody>
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
<script type="text/javascript">
var timezone = '{{ $timezone }}',
    loginemail = '{{ $loginemail }}';
var url = {
    getChallengeSummaryData: `{{ route('admin.reports.getChallengeSummaryData') }}`,
    getChallengeDetailsData: `{{ route('admin.reports.getChallengeDetailsData') }}`,
    getChallengeDailySummaryData: `{{ route('admin.reports.getChallengeDailySummaryData') }}`,
    getICReportChallengeComapnies: `{{ route("admin.reports.getICReportChallengeComapnies") }}`,
    getChallenges: `{{ route("admin.reports.getChallenges") }}`,
    getChallengeParticipant: `{{ route("admin.reports.getChallengeParticipant") }}`,
    challengeActivityReportExportUrl:`{{route('admin.reports.exportChallengeActivityReport')}}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
    entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
},
tabledata = {
    user_name:`{!! trans('challengeactivity.table.user_name') !!}`,
    email:`{!! trans('challengeactivity.table.email') !!}`,
    team_name:`{!! trans('challengeactivity.table.team_name') !!}`,
    target_type:`{!! trans('challengeactivity.table.target_type') !!}`,
    counts:`{!! trans('challengeactivity.table.counts') !!}`,
    points:`{!! trans('challengeactivity.table.points') !!}`,
    company_name: `{!! trans('challengeactivity.table.company_name') !!}`,
},
button = {
        export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('customersatisfaction.buttons.export_to_excel') }}',
    },
message = {
    failed_load_companies: `{!! trans('challengeactivity.message.failed_load_companies') !!}`,
    failed_load_challenge: `{!! trans('challengeactivity.message.failed_load_challenge') !!}`,
    select_challenge_view_report: `{!! trans('challengeactivity.message.select_challenge_view_report') !!}`,
};
</script>
<script src="{{ mix('js/challengeactivity/index.js') }}" type="text/javascript">
</script>
@endsection

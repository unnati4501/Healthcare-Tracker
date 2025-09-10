@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    .hidden { display: none; }
</style>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.breadcrumb',[
    'mainTitle'  => trans('challengeactivity.details.title.challeneg_activity'),
    'breadcrumb' => 'report.challengeactivityhistory',
    'back'       => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        @if($type != 'meditations')
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">{{ trans('challengeactivity.details.title.filter') }}</h4>
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('trackerFilter', $tracker, request()->get('trackerFilter'), ['class' => 'form-control select2', 'id'=>'trackerFilter', 'placeholder' => trans('challengeactivity.details.filter.select_tracker'), 'data-placeholder' => trans('challengeactivity.details.filter.select_tracker'), 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" id="userDailySummary" type="button">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="javascript:void(0)" id="resetuserDailySummary">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">{{ trans('buttons.general.reset') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="card">
            <div class="card-header detailed-header">
                <div>
                    <div class="user-info-wrap">
                        <div class="user-img align-middle">
                            <img class="" src="{{ (!empty($user->logo) ? $user->logo : asset('assets/dist/img/boxed-bg.png')) }}" alt="icon">
                        </div>
                        <div class="d-inline-block align-middle">
                            <h5>{{ $user->full_name }}</h5>
                            <div class="gray-600 mb-0 d-block">{{ $user->company()->first()->name }} | {{ $user->teams()->first()->name }}</div>
                            </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class="dt-buttons challengeUserActivityReport-export">
                        <button class="btn btn-primary" data-end="" data-start="" id="challengeUserActivityReport" data-title="Export Challenge User Activity Report" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <table class="table custom-table" id="challengeUserActivity">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('challengeactivity.details.table.tracker_name') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.details.table.target_type') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.details.table.achived_value') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.details.table.points') }}
                                </th>
                                <th>
                                    {{ trans('challengeactivity.details.table.sync_date') }}
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
</section>
@include('admin.report.export_modal')
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>

<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
var timezone = '{{ $timezone }}',
    loginemail = '{{ $loginemail }}';
var url = {
    datatable: `{{ route("admin.reports.getUserDailyHistoryTableData") }}`,
    exportReport: `{{ route('admin.reports.export-challenge-user-activity-report') }}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
    entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
},
data = {
    logdate: `{{ $logdate  }}`,
    user: `{{ $user->id }}`,
    challenge: `{{ $challenge->id }}`,
    type: `{{ $type  }}`,
    columnName: `{{ $columnName  }}`,
    modelId: `{{ $modelId  }}`,
    uom: `{{ $uom  }}`,
    challengeStatus: `{{ $challengeStatus  }}`,
},
button = {
    export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('customersatisfaction.buttons.export_to_excel') }}',
},
message = {
    enter_email: `{{ trans('challengeactivity.details.messages.enter_email') }}`,
    enter_valid_email: `{{ trans('challengeactivity.details.messages.enter_valid_email') }}`,
};
</script>
<script src="{{ mix('js/challengeactivity/activitydetails.js') }}" type="text/javascript">
</script>
@endsection

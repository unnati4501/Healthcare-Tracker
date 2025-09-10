@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.user.breadcrumb', [
    'mainTitle' => trans('user.tracker_history.index', ['username' => $userData->full_name]),
    'breadcrumb' => Breadcrumbs::render('user.tracker-history'),
    'back' => true
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
                {{ Form::open(['route' => ['admin.users.tracker-history', $userData->id], 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'historytrackerSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('trackerName', request()->get('trackerName'), ['class' => 'form-control', 'placeholder' => trans('user.tracker_history.filter.tracker'), 'id' => 'trackername', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group daterange-outer">
                            <div class="input-group input-daterange dateranges mb-0">
                                {{ Form::text('fromdate', request()->get('fromdate'), ['id' => 'fromdate', 'class' => 'form-control', 'placeholder' => trans('user.tracker_history.filter.from_date'), 'readonly' => true]) }}
                                <span class="input-group-addon text-center">
                                    -
                                </span>
                                {{ Form::text('todate', request()->get('todate'), ['id' => 'todate', 'class' => 'form-control', 'placeholder' => trans('user.tracker_history.filter.to_date'), 'readonly' => true]) }}
                            </div>
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.users.tracker-history', $userData->id) }}">
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
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    {{-- <div class="text-end">
                        <a class="action-icon btn btn-primary" href="javaScript:void(0)" data-end="" data-start="" id="trackerHistoryExport" data-title="Export Tracker History" title="{{ trans('challenges.buttons.tooltips.export') }}">
                            <i aria-hidden="true" class="far fa-download">Export Excel
                            </i>
                        </a>
                    </div> --}}
                    <div class="dt-buttons">
                        <button class="btn btn-primary" data-end="" data-start="" id="trackerHistoryExport" type="button" data-title="Export Tracker History">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <table class="table custom-table" id="trackerhistoryManagement">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('user.tracker_history.table.tracker_name') }}
                                </th>
                                <th>
                                    {{ trans('user.tracker_history.table.tracker_change_date_time') }}
                                </th>
                            </tr>
                        </thead>
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
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        loginemail = '{{ $loginemail }}',
        startDate = '{{ config('zevolifesettings.challenge_set_date.before') }}',
        endDate = '{{ config('zevolifesettings.challenge_set_date.after') }}';
    var pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
    var url = {
        trackerHistoryExportUrl:`{{route('admin.users.exportTrackerHistoryReport', $userData->id)}}`,
    },
    firstSyncDate = `{{ $firstSyncDate }}`,
    datatable = `{{ route('admin.users.gettrackerhistory', $userData->id) }}`,
    fileName = 'TrackHistory_{{ $userData->full_name }}_{{ $company->name }}';
</script>
<script src="{{ mix('js/users/tracker-history.js') }}" type="text/javascript">
</script>
@endsection

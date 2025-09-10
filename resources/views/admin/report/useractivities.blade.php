@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.breadcrumb',[
    'mainTitle'  => trans('useractivity.title.user_activity'),
    'breadcrumb' => 'npssurvey.useractivity',
    'back'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- Nav tabs -->
        <div class="nav-tabs-wrap">
            <ul class="nav nav-tabs tabs-line-style" id="userActivityTab" role="tablist">
                <li class="nav-item">
                    <a aria-controls="Steps" aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#steps" id="enrolledCourse-tab" role="tab">
                        {{ trans('useractivity.title.steps') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a aria-controls="Exercises" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#exercises" id="completedCourse-tab" role="tab">
                        {{ trans('useractivity.title.exercises') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a aria-controls="Meditations" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#meditations" id="contact-tab" role="tab">
                        {{ trans('useractivity.title.meditations') }}
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="userActivityTabContent">
                <div aria-labelledby="enrolledCourse-tab" class="tab-pane fade show active" id="steps" role="tabpanel">
                    <!-- Card -->
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <h4 class="d-md-none">
                                {{ trans('useractivity.title.filter') }}
                            </h4>
                            <form class="userActivityForm">
                                <div class="search-outer d-md-flex justify-content-between">
                                    <div>
                                        <div class="form-group">
                                            {{ Form::text('stepTextSearch', request()->get('stepTextSearch'), ['class' => 'form-control', 'placeholder' => trans('useractivity.filter.search_by_name_email'), 'id' => 'stepTextSearch', 'autocomplete' => 'off']) }}
                                        </div>
                                        <div class="form-group daterange-outer">
                                            <div class="input-daterange dateranges justify-content-between mb-0">
                                                <div class="datepicker-wrap me-0 mb-0 ">
                                                    {{ Form::text('dtFromdate', request()->get('dtFromdate'), ['id' => 'dtFromdate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.filter.steps_log_date_from'), 'readonly' => true]) }}
                                                    <i class="far fa-calendar"></i>
                                                </div>
                                                <span class="input-group-addon text-center">
                                                   -
                                                </span>
                                                <div class="datepicker-wrap me-0 mb-0 ">
                                                    {{ Form::text('dtTodate', request()->get('dtTodate'), ['id' => 'dtTodate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.filter.steps_log_date_to'), 'readonly' => true]) }}
                                                    <i class="far fa-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="search-actions align-self-start">
                                        <button class="me-md-4 filter-apply-btn" id="stepSearch" type="button">
                                            {{trans('buttons.general.apply')}}
                                        </button>
                                        <a class="filter-cancel-icon" id="resetStepSearch" href="javaScript:;">
                                            <i class="far fa-times">
                                            </i>
                                            <span class="d-md-none ms-2 ms-md-0">
                                                {{trans('buttons.general.reset')}}
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer" id="stepTextSearchTable-wrap">
                                <div class="dt-buttons">
                                    <button class="btn btn-primary exportUserActivityStepReport" data-end="" data-start="" data-tab="steps" id="exportUserActivityReport" data-title="Export Steps Summary" type="button">
                                        <span>
                                            <i class="far fa-envelope me-3 align-middle">
                                            </i>
                                            {{trans('buttons.general.export')}}
                                        </span>
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table custom-table" id="stepTextSearchTable">
                                        <thead>
                                            <tr>
                                                <th style="display: none">
                                                    {{trans('useractivity.table.id')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.name')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.email')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.tracker')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.steps')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.distance')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.calories')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.log_date')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.sync_date')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.step_authentication')}}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div aria-labelledby="completedCourse-tab" class="tab-pane fade" id="exercises" role="tabpanel">
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <h4 class="d-md-none">
                                {{ trans('useractivity.title.filter') }}
                            </h4>
                            <form class="userActivityForm">
                                <div class="search-outer d-md-flex justify-content-between">
                                    <div>
                                        <div class="form-group">
                                            {{ Form::text('exercisesTextSearch', request()->get('exercisesTextSearch'), ['class' => 'form-control', 'placeholder' => trans('useractivity.filter.search_by_name_email'), 'id' => 'exercisesTextSearch', 'autocomplete' => 'off']) }}
                                        </div>
                                        <div class="form-group daterange-outer">
                                            <div class="input-daterange dateranges justify-content-between mb-0">
                                                <div class="datepicker-wrap me-0 mb-0 ">
                                                    {{ Form::text('dtExerciseFromdate', request()->get('dtFromdate'), ['id' => 'exerciseFromdate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.filter.from_date'), 'readonly' => true]) }}
                                                    <i class="far fa-calendar"></i>
                                                </div>
                                                <span class="input-group-addon text-center">
                                                   -
                                                </span>
                                                <div class="datepicker-wrap me-0 mb-0 ">
                                                    {{ Form::text('dtExerciseTodate', request()->get('dtTodate'), ['id' => 'exerciseTodate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.filter.to_date'), 'readonly' => true]) }}
                                                    <i class="far fa-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="search-actions align-self-start">
                                        <button class="me-md-4 filter-apply-btn" id="exercisesSearch" type="button">
                                            {{trans('buttons.general.apply')}}
                                        </button>
                                        <a class="filter-cancel-icon" href="javaScript:;" id="resetExercisesSearch">
                                            <i class="far fa-times">
                                            </i>
                                            <span class="d-md-none ms-2 ms-md-0">
                                                {{trans('labels.buttons.reset')}}
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer" id="exercisesReportListTable-wrap">
                                <div class="dt-buttons">
                                    <button class="btn btn-primary exportUserActivityExerciseReport" data-end="" data-start="" data-tab="exercise" id="exportUserActivityReport" data-title="Export Exercise Summary" type="button">
                                        <span>
                                            <i class="far fa-envelope me-3 align-middle">
                                            </i>
                                            {{trans('buttons.general.export')}}
                                        </span>
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table custom-table" id="exercisesReportListTable">
                                        <thead>
                                            <tr>
                                                <th style="display: none">
                                                    id
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.name')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.email')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.tracker')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.exercises')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.distance')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.calories')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.duration')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.start_end_date')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.sync_date')}}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div aria-labelledby="enrolledChallanges-tab" class="tab-pane fade" id="meditations" role="tabpanel">
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <h4 class="d-md-none">
                                {{ trans('useractivity.title.filter') }}
                            </h4>
                            <form class="userActivityForm">
                                <div class="search-outer d-md-flex justify-content-between">
                                    <div>
                                        <div class="form-group">
                                            {{ Form::text('meditationsTextSearch', request()->get('meditationsTextSearch'), ['class' => 'form-control', 'placeholder' => trans('useractivity.filter.search_by_name_email'), 'id' => 'meditationsTextSearch', 'autocomplete' => 'off']) }}
                                        </div>
                                        <div class="form-group daterange-outer">
                                            <div class="input-daterange dateranges justify-content-between mb-0">
                                                <div class="datepicker-wrap me-0 mb-0 ">
                                                    {{ Form::text('dtMeditationFromdate', request()->get('dtFromdate'), ['id' => 'meditationFromdate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.filter.log_date_from'), 'readonly' => true]) }}
                                                    <i class="far fa-calendar"></i>
                                                </div>
                                                <span class="input-group-addon text-center">
                                                   -
                                                </span>
                                                <div class="datepicker-wrap me-0 mb-0 ">
                                                    {{ Form::text('dtMeditationTodate', request()->get('dtTodate'), ['id' => 'meditationTodate', 'class' => 'form-control datepicker', 'placeholder' => trans('bookingreport.filter.log_date_to'), 'readonly' => true]) }}
                                                    <i class="far fa-calendar"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="search-actions align-self-start">
                                        <button class="me-md-4 filter-apply-btn" id="meditationsSearch" type="button">
                                            {{trans('buttons.general.apply')}}
                                        </button>
                                        <a class="filter-cancel-icon" href="javaScript:;" id="resetMeditationsSearch">
                                            <i class="far fa-times">
                                            </i>
                                            <span class="d-md-none ms-2 ms-md-0">
                                                {{trans('labels.buttons.reset')}}
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer" id="meditationsReportListTable-wrap">
                                <div class="dt-buttons">
                                    <button class="btn btn-primary exportUserActivityMeditationReport" data-end="" data-start="" data-tab="meditation" id="exportUserActivityReport" data-title="Export Meditation Summary" type="button">
                                        <span>
                                            <i class="far fa-envelope me-3 align-middle">
                                            </i>
                                            {{trans('buttons.general.export')}}
                                        </span>
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table custom-table" id="meditationsReportListTable">
                                        <thead>
                                            <tr>
                                                <th style="display: none">
                                                    {{trans('useractivity.table.id')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.name')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.email')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.track')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.duration')}}
                                                </th>
                                                <th>
                                                    {{trans('useractivity.table.log_date')}}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@include('admin.report.export_user_activity_modal')
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
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
    date_format = `{{ $date_format }}`,
    loginemail = '{{ $loginemail }}';
var url = {
    getUserStepsData: `{{ route('admin.reports.getUserStepsData') }}`,
    getUserExercisesData: `{{ route('admin.reports.getUserExercisesData') }}`,
    getUserMeditationsData: `{{ route('admin.reports.getUserMeditationsData') }}`,
    userActivityExportUrl:`{{route('admin.reports.exportUserActivityReport')}}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
    entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
};
</script>
<script src="{{ mix('js/useractivity/index.js') }}" type="text/javascript">
</script>
@endsection

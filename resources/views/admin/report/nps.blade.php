@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
{{-- <link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/> --}}
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>

<style type="text/css">
    .hidden{
        display: none;
    }
</style>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.breadcrumb',[
    'mainTitle'  => trans('customersatisfaction.title.nps_feedback'),
    'breadcrumb' => 'npssurvey.index',
    'back'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="nav-tabs-wrap">
            <!-- Left col -->
            <ul class="nav nav-tabs tabs-line-style" id="myTab" role="tablist">
                @permission('view-nps-feedbacks')
                <li class="nav-item">
                    <a aria-controls="App" aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#appTab" id="enrolledCourse-tab" role="tab">
                        {{ trans('customersatisfaction.title.app') }}
                    </a>
                </li>
                @endauth
            @permission('manage-portal-survey')
                <li class="nav-item">
                    <a aria-controls="Portal" aria-selected="true" class="nav-link {{ (!access()->allow('view-nps-feedbacks') && access()->allow('manage-portal-survey'))? 'active' : '' }}" data-bs-toggle="tab" href="#portalTab" id="enrolledCourse-tab" role="tab">
                        {{ trans('customersatisfaction.title.portal') }}
                    </a>
                </li>
                @endauth
            @permission('manage-project-survey')
                <li class="nav-item">
                    <a aria-controls="Project" aria-selected="false" class="nav-link {{ (!access()->allow('view-nps-feedbacks'))? 'active' : '' }}" data-bs-toggle="tab" href="#projectTab" id="completedCourse-tab" role="tab">
                        {{ trans('customersatisfaction.title.project') }}
                    </a>
                </li>
                @endauth
            </ul>
            <div class="tab-content" id="myTabContent">
                @permission('view-nps-feedbacks')
                <div aria-labelledby="enrolledCourse-tab" class="tab-pane fade show active" id="appTab" role="tabpanel">
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <h4 class="d-md-none">
                                {{ trans('customersatisfaction.title.filter') }}
                            </h4>
                            <form class="npsAppForm">
                                <div class="search-outer d-md-flex justify-content-between">
                                    <div>
                                        <div class="form-group">
                                            {{ Form::select('company', $companies, null , ['class' => 'form-control select2','id'=>'company',"style"=>"width: 100%;", 'placeholder' => trans('customersatisfaction.filter.select_company'), 'data-placeholder'=>trans('customersatisfaction.filter.select_company'), 'autocomplete' => 'off', 'target-data' => 'company'] ) }}
                                        </div>
                                        <div class="form-group">
                                            {{ Form::select('feedBackType', $feedBackType, null , ['class' => 'form-control select2','id'=>'feedBackType',"style"=>"width: 100%;", 'placeholder' => trans('customersatisfaction.filter.select_feedback_type'), 'data-placeholder'=>trans('customersatisfaction.filter.select_feedback_type'), 'autocomplete' => 'off', 'target-data' => 'feedBackType'] ) }}
                                        </div>
                                    </div>
                                    <div class="search-actions align-self-start">
                                        <button class="me-md-4 filter-apply-btn" id="npsSearch" type="button">
                                            {{trans('buttons.general.apply')}}
                                        </button>
                                        <a class="filter-cancel-icon" href="javascript:;" id="resetNPSSearch">
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
                    <div class="card" id="app-tab-result-block">
                        <div class="card-body">
                            <div class="text-center" id="app-graph-loader">
                                <i class="fa fa-spinner fa-spin">
                                </i>
                                {{trans('customersatisfaction.message.loadinggraph')}}
                            </div>
                            <div id="app-graph-area">
                            </div>
                            <div class="card-table-outer" id="NPSFeedBackTable-wrap">
                                {{-- <div class="text-end">
                                    <a class="action-icon btn btn-primary" data-end="" data-start="" href="javaScript:void(0)" id="appExport" data-title="Export App FeedBack" data-isportal="0" title="{{ trans('challenges.buttons.tooltips.export') }}">
                                        <i aria-hidden="true" class="far fa-download">Export Excel
                                        </i>
                                    </a>
                                </div> --}}
                                <div class="dt-buttons">
                                    <button class="btn btn-primary appExport" data-end="" data-start="" id="appExport" data-isportal="0" data-title="Export App FeedBack" type="button">
                                        <span>
                                            <i class="far fa-envelope me-3 align-middle">
                                            </i>
                                            {{trans('buttons.general.export')}}
                                        </span>
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table custom-table" id="NPSFeedBackTable">
                                        <thead>
                                            <tr>
                                                <th style="display: none">
                                                    {{ trans('customersatisfaction.table.id') }}
                                                </th>
                                                <th>
                                                    {{ trans('customersatisfaction.table.company_name') }}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.logo')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.feedback_type')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.notes')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.date')}}
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
                </div>
                @endauth
            @permission('manage-portal-survey')
                <div aria-labelledby="enrolledCourse-tab" class="tab-pane fade {{ (!access()->allow('view-nps-feedbacks') && access()->allow('manage-portal-survey'))? 'show active' : '' }}" id="portalTab" role="tabpanel">
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <form class="npsPortalForm">
                                <h4 class="d-md-none">
                                    {{ trans('customersatisfaction.title.filter') }}
                                </h4>
                                <div class="search-outer d-md-flex justify-content-between">
                                    <div>
                                        <div class="form-group">
                                            {{ Form::select('company', $portalCompanies, null , ['class' => 'form-control select2','id'=>'companyPortal',"style"=>"width: 100%;", 'placeholder' => trans('customersatisfaction.filter.select_company'), 'data-placeholder'=>trans('customersatisfaction.filter.select_company'), 'autocomplete' => 'off', 'target-data' => 'company'] ) }}
                                        </div>
                                        <div class="form-group">
                                            {{ Form::select('feedBackType', $feedBackType, null , ['class' => 'form-control select2','id'=>'feedBackTypePortal',"style"=>"width: 100%;", 'placeholder' => trans('customersatisfaction.filter.select_feedback_type'), 'data-placeholder'=>trans('customersatisfaction.filter.select_feedback_type'), 'autocomplete' => 'off', 'target-data' => 'feedBackType'] ) }}
                                        </div>
                                    </div>
                                    <div class="search-actions align-self-start">
                                        <button class="me-md-4 filter-apply-btn" id="npsSearchPortal" type="button">
                                            {{trans('buttons.general.apply')}}
                                        </button>
                                        <a class="filter-cancel-icon" href="javascript:;" id="resetNPSSearchPortal">
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
                    <div class="card" id="portal-tab-result-block">
                        <div class="card-body">
                            <div class="text-center" id="graph-loader">
                                <i class="fa fa-spinner fa-spin">
                                </i>
                                {{trans('customersatisfaction.message.loadinggraph')}}
                            </div>
                            <div id="portal-graph-area">
                            </div>
                            <div class="card-table-outer" id="NPSFeedBackTablePortal-wrap">
                                {{-- <div class="text-end">
                                    <a class="action-icon btn btn-primary" data-end="" data-start="" href="javaScript:void(0)" id="appExport" data-title="Export Portal FeedBack" data-isportal="1" title="{{ trans('challenges.buttons.tooltips.export') }}">
                                        <i aria-hidden="true" class="far fa-download">Export Excel
                                        </i>
                                    </a>
                                </div> --}}
                                <div class="dt-buttons">
                                    <button class="btn btn-primary portalExport" data-end="" data-start="" id="appExport" data-isportal="1" data-title="Export Portal FeedBack" type="button">
                                        <span>
                                            <i class="far fa-envelope me-3 align-middle">
                                            </i>
                                            {{trans('buttons.general.export')}}
                                        </span>
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table custom-table" id="NPSFeedBackTablePortal">
                                        <thead>
                                            <tr>
                                                <th style="display: none">
                                                    {{ trans('customersatisfaction.table.id') }}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.company_name')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.logo')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.feedback_type')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.notes')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.date')}}
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
                </div>
                @endauth
            @permission('manage-project-survey')
                <div aria-labelledby="enrolledCourse-tab" class="tab-pane fade {{ (!access()->allow('view-nps-feedbacks'))? 'show active' : '' }}" id="projectTab" role="tabpanel">
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <form class="npsProjectForm">
                                <h4 class="d-md-none">
                                    {{ trans('customersatisfaction.title.filter') }}
                                </h4>
                                <div class="search-outer d-md-flex justify-content-between">
                                    <div>
                                        @if($isSuperAdmin)
                                        <div class="form-group">
                                            {{ Form::select('projectcompany', $companies, null , ['class' => 'form-control select2','id'=>'projectcompany',"style"=>"width: 100%;", 'placeholder' => trans('customersatisfaction.filter.select_company'), 'data-placeholder'=>trans('customersatisfaction.filter.select_company'), 'autocomplete' => 'off', 'target-data' => 'projectcompany'] ) }}
                                        </div>
                                        @endif
                                        <div class="form-group">
                                            {{ Form::select('projectStatus', $projectStatus, null , ['class' => 'form-control select2','id'=>'projectStatus',"style"=>"width: 100%;", 'placeholder' => trans('customersatisfaction.filter.select_status'), 'data-placeholder'=>trans('customersatisfaction.filter.select_status'), 'autocomplete' => 'off', 'target-data' => 'projectStatus'] ) }}
                                        </div>
                                        <div class="form-group">
                                            {{ Form::text('projecttextSearch', request()->get('projecttextSearch'), ['class' => 'form-control', 'placeholder' => trans('customersatisfaction.filter.search_by_project'), 'id' => 'projecttextSearch', 'autocomplete' => 'off']) }}
                                        </div>
                                        <div class="form-group">
                                            {{ Form::text('start_date', request()->get('start_date'), ['class' => 'form-control','id' => 'start_date', 'placeholder' => trans('customersatisfaction.filter.select_from_start_date'),'autocomplete' => 'off']) }}
                                        </div>
                                        <div class="form-group">
                                            {{ Form::text('end_date', request()->get('end_date'), ['class' => 'form-control','id' => 'end_date', 'placeholder' => trans('customersatisfaction.filter.select_from_end_date'),'autocomplete' => 'off']) }}
                                        </div>
                                    </div>
                                    <div class="search-actions align-self-start">
                                        <button class="me-md-4 filter-apply-btn" id="projectSearch" type="button">
                                            {{ trans('buttons.general.apply') }}
                                        </button>
                                        <a class="filter-cancel-icon" href="javascript:;" id="resetprojectSearch">
                                            <i class="far fa-times">
                                            </i>
                                            <span class="d-md-none ms-2 ms-md-0">
                                                {{ trans('buttons.general.reset') }}
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @permission('create-project-survey')
                    <div class="text-end mb-4 tab-button">
                        <a href="{!! route('admin.projectsurvey.create') !!}" class="btn btn-primary" title="Export to Excel"> <i class="fal fa-plus me-2"></i> <span class="align-middle">
                            {{trans('customersatisfaction.buttons.add_project')}}
                        </span></a>
                    </div>
                    @endauth
                    <div class="card" id="project-tab-result-block">
                        <div class="card-header detailed-header">
                            <div class="d-flex flex-wrap">
                                <div class="col-md-6 p-0 m-0">
                                    {{ Form::select('projectList', $projectList, null , ['class' => 'form-control select2','id'=>'projectList',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=>'Select Project', 'autocomplete' => 'off', 'target-data' => 'projectList'] ) }}
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="text-center" id="project-graph-loader" style="display: none">
                                <i class="fa fa-spinner fa-spin">
                                </i>
                                {{trans('customersatisfaction.message.loadinggraph')}}
                            </div>
                            <div class="text-center" id="project-graph-area">
                                <p>
                                    {{trans('customersatisfaction.message.select_project_message')}}
                                </p>
                            </div>
                            <div class="card-table-outer" id="ProjectFeedBackTable-wrap">
                                <div class="table-responsive">
                                    {{-- <div class="text-end">
                                        <a class="action-icon btn btn-primary" data-end="" data-start="" href="javaScript:void(0)" data-tab="project" id="appExport" data-title="Export Project FeedBack" title="{{ trans('challenges.buttons.tooltips.export') }}">
                                            <i aria-hidden="true" class="far fa-download">Export Excel
                                            </i>
                                        </a>
                                    </div> --}}
                                    <div class="dt-buttons">
                                        <button class="btn btn-primary projectExport" data-end="" data-start="" data-tab="project" id="appExport" data-title="Export Project FeedBack" type="button">
                                            <span>
                                                <i class="far fa-envelope me-3 align-middle">
                                                </i>
                                                {{trans('buttons.general.export')}}
                                            </span>
                                        </button>
                                    </div>
                                    <table class="table custom-table" id="ProjectFeedBackTable">
                                        <thead>
                                            <tr>
                                                <th style="display: none">
                                                    {{trans('customersatisfaction.table.id')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.name')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.type')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.start_date')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.end_date')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.responses')}}
                                                </th>
                                                <th>
                                                    {{trans('customersatisfaction.table.status')}}
                                                </th>
                                                <th class="no-sort {{ (access()->allow('view-nps-feedbacks'))? 'th-btn-1' : 'th-btn-3' }}">
                                                    {{trans('customersatisfaction.table.action')}}
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
                </div>
                @endauth
            </div>
        </div>
        <!-- /.row (main row) -->
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- Record Delete Model Popup -->
@include('admin.report.delete-project-modal')
@include('admin.report.export_modal')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/highcharts/highcharts.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<!-- App Graph Script Code -->
@include('admin.report.app-graph')
<!-- Portal Graph Script Code -->
@include('admin.report.portal-graph')
<!-- Project Graph Script Code -->
@include('admin.report.project-graph')
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        loginemail = '{{ $loginemail }}',
        startDate = '{{ config('zevolifesettings.challenge_set_date.before') }}',
        endDate = '{{ config('zevolifesettings.challenge_set_date.after') }}';
    var chartJson =  {!! $chartJson !!};
    var chartJsonPortal = {!! $chartJsonPortal !!};
    var url = {
        getNpsData: `{{ route('admin.reports.getNpsData') }}`,
        getProjectData: `{{ route('admin.projectsurvey.getProjectData') }}`,
        getGraphData: `{{route('admin.projectsurvey.getGraphData','/')}}`,
        projectSurveyDelete: `{{route('admin.projectsurvey.delete','/')}}`,
        npsProjectExportUrl:`{{route('admin.projectsurvey.exportNpsProjectData')}}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
    },
    data = {
        isSuperAdmin: `{{ $isSuperAdmin }}`,
        viewNpsFeedbacks: `{{ access()->allow('view-nps-feedbacks') }} `,
        managePortalSurvey: `{{ access()->allow('manage-portal-survey') }}`,
    },
    button = {
        export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('customersatisfaction.buttons.export_to_excel') }}',
    },
    message = {
        project_survey_deleted: `{{ trans('customersatisfaction.message.project_survey_deleted') }}`,
        project_survey_is_use: `{{ trans('customersatisfaction.message.project_survey_is_use') }}`,
        unable_delete_project_data: `{{ trans('customersatisfaction.message.unable_delete_project_data') }}`,
        survey_link_copied: `{{ trans('customersatisfaction.message.survey_link_copied') }}`,
        graph_data_get_successfully: `{{ trans('customersatisfaction.message.graph_data_get_successfully') }}`,
        unable_get_graph_data: `{{ trans('customersatisfaction.message.unable_get_graph_data') }}`,
        select_project_message: `{{trans('customersatisfaction.message.select_project_message')}}`,
    };
</script>
<script src="{{ mix('js/customersatisfaction/index.js') }}" type="text/javascript">
</script>
@endsection

@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('dashboard.question-report-breadcrumb', [
    'mainTitle' => trans('dashboard.title.question_report'),
    'breadcrumb' => Breadcrumbs::render('dashboard.question_report'),
    'back' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="dashboard-card-filter" style="margin-bottom:10px">
                        <div class="mw-100 me-4 w-25">
                            @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
                                {{ Form::select('company_id', $companies, $company_id, ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'target-data' => 'department_id', 'target-location-data'=>'location_id']) }}
                            @else
                            <div style="display: none">
                                {{ Form::select('company_id', $companies, $company_id, ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'disabled'=>true, 'target-data' => 'department_id', 'target-location-data'=>'location_id']) }}
                            </div>
                            @endif
                        </div>
                        <div class="mw-100 me-4 w-25">
                            @if($role->slug != 'counsellor' && $role->slug != 'wellbeing_specialist')
                                @if($role->group == 'zevo' || $role->group == 'company' || $role->group == 'reseller')
                                    <div id="department">
                                        @if($role->group == 'zevo')
                                        {{ Form::select('department_id', [], null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true', 'disabled' => true]) }}
                                        @else
                                        {{ Form::select('department_id', $departments, null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true', 'disabled' => (($role->group == 'company' || ($role->group == 'reseller' && !is_null($company->parent_id))) ? false : true)]) }}
                                        @endif
                                    </div>
                                @else
                                    <div style="display: none">
                                        {{ Form::select('department_id', $departments, null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true']) }}
                                    </div>
                                @endif
                            @else
                                <div style="display: none">
                                    {{ Form::select('department_id', $departments, null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true']) }}
                                </div>
                            @endif
                        </div>
                        <div class="mw-100 me-4 w-25">
                            @if($role->slug != 'counsellor' && $role->slug != 'wellbeing_specialist')
                                @if($role->group == 'zevo' || $role->group == 'company' || $role->group == 'reseller')
                                    <div id="location">
                                        @if($role->group == 'zevo')
                                            {{ Form::select('location_id', [], null,['class' => 'form-control select2', 'id'=>'location_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.location'), 'data-placeholder' => trans('labels.dashboard.location'), 'data-allow-clear' => 'true', 'disabled' => true]) }}
                                        @else
                                            {{ Form::select('location_id', $locations, null,['class' => 'form-control select2', 'id'=>'location_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.location'), 'data-placeholder' => trans('labels.dashboard.location'), 'data-allow-clear' => 'true', 'disabled' => true]) }}
                                        @endif
                                    </div>
                                @else
                                    <div style="display: none">
                                        {{ Form::select('location_id', $locations, null,['class' => 'form-control select2', 'id'=>'location_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.location'), 'data-placeholder' => trans('labels.dashboard.location'), 'data-allow-clear' => 'true']) }}
                                    </div>
                                @endif
                            @else
                                <div style="display: none">
                                    {{ Form::select('location_id', $locations, null,['class' => 'form-control select2', 'id'=>'location_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.location'), 'data-placeholder' => trans('labels.dashboard.location'), 'data-allow-clear' => 'true']) }}
                                </div>
                            @endif
                        </div>
                        <div class="mw-100  me-4 w-25">
                            @include('newDashboard.partials.month-range-picker', ['tier' => 1, 'parentId' => 'monthrangeGlobal', 'fromId' => 'globalFromMonth', 'toId' => 'globalToMonth'])
                        </div>
                        {{ Form::hidden('companiesId', $companiesId, ['id' => 'companiesId']) }}
                        {{ Form::hidden('roleType', $resellerType, ['id' => 'roleType']) }}
                    </div>
                    <div id="report_wrapper" style="display: block;">
                        <div class="tabs-wraper" style="display: {{ $categoriesVisibility }};">
                            <div class="owl-carousel arrow-theme owl-theme p-0" id="question-report-category-tabs">
                                @foreach($categories as $key => $category)
                                <div class="item text-center" data-id="{{ $key }}">
                                    <a href="javascript:void(0);">
                                        <img class="ms-auto me-auto" src="{{ $category['image'] }}">
                                            <span class="d-block category-name">
                                                {{ $category['name'] }}
                                            </span>
                                        </img>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-lg-3 border-lg-right">
                                <div class="d-flex justify-content-around h-100 flex-column">
                                    <div class="chart-height donut-chart-height small">
                                        <div class="canvas-wrap">
                                            <canvas class="canvas" data-categorygaugechart="">
                                            </canvas>
                                            <div class="canvas-center-data" data-categorygaugechart-value="">
                                                0%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-9 mt-4 mt-lg-0 align-self-center">
                                <div class="subcategory-list-outer">
                                    <div class="text-center" id="subcategory_progressbars_loader">
                                        <i class="fas fa-spinner fa-lg fa-spin">
                                        </i>
                                        <span class="ms-1">
                                            {{ trans('labels.dashboard.audit.loading_graphs') }}
                                        </span>
                                    </div>
                                    <div class="text-center" id="subcategory_progressbars_no_data" style="display: none;">
                                        <span class="ms-1">
                                            {{ trans('labels.dashboard.audit.no_data_category_graphs') }}
                                        </span>
                                    </div>
                                    <ul class="subcatgory-list" data-subcategory-progressbars="" style="display: none;">
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div id="subcategory_wrapper">
                            <div class="tabs-wraper">
                                <div class="owl-carousel arrow-theme owl-theme" id="question-report-subcategory-tabs">
                                </div>
                            </div>
                            <div class="tab-content" id="subcategory_question_block" style="display: none;">
                                <div class="tab-pane fade show active" role="tabpanel">
                                    <div class="card-table-outer">
                                        <table class="table custom-table" id="subcategory_questions_tbl">
                                            <thead>
                                                <tr>
                                                    <th class="th-btn-sm no-sort text-center">
                                                        {{ trans('dashboard.audit.question_report_tbl.sr_no') }}
                                                    </th>
                                                    <th class="th-btn-4">
                                                        {{ trans('dashboard.audit.question_report_tbl.question_type') }}
                                                    </th>
                                                    <th>
                                                        {{ trans('dashboard.audit.question_report_tbl.question') }}
                                                    </th>
                                                    <th class="text-center th-btn-4">
                                                        {{ trans('dashboard.audit.question_report_tbl.response') }}
                                                    </th>
                                                    <th class="text-center th-btn-4 no-sort">
                                                        {{ trans('dashboard.audit.question_report_tbl.percentage') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center" colspan="5">
                                                        <i class="fas fa-spinner fa-lg fa-spin">
                                                        </i>
                                                        <span class="ms-1">
                                                            {{ trans('dashboard.audit.messages.loading_questions') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4" id="subcategory_progress" style="display: block;">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-lg fa-spin">
                                    </i>
                                    <span class="ms-1">
                                        {{ trans('dashboard.audit.messages.loading_questions') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4" id="report_no_data" style="display: none;">
                        <div class="text-center">
                            <span class="ms-1">
                                {{ trans('dashboard.audit.messages.no_data_to_display') }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-4" id="report_data_process" style="display: none;">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-lg fa-spin">
                            </i>
                            <span class="ms-1">
                                {{ trans('dashboard.audit.messages.loading_graphs') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('after-scripts')
<!-- Datatable -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<!-- jQuery chart -->
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js?var='.rand()) }}">
</script>
<!-- DateRangePicker -->
<script src="{{ asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js?var='.rand()) }}">
</script>
<!-- Datepicker -->
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<!-- Carousel -->
<script src="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.js?var='.rand()) }}">
</script>
<script id="subcategory_progressbars_template" type="text/html">
    <li>
        <div class="d-flex justify-content-between">
            <span>#name#</span>
            <span style="color: #color_code#;">#percetage#%</span>
        </div>
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: #percetage#%; background-color: #color_code#;" aria-valuenow="#percetage#%" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </li>
</script>
<script id="category_tabs_template" type="text/html">
    <div class="item text-center #active_class#" data-id="#id#">
        <a href="javascript:void(0);">
            <img class="ms-auto me-auto" src="#image#">
            <span class="d-block category-name">
                #name#
            </span>
        </a>
    </div>
</script>
<script id="subcategory_tabs_template" type="text/html">
    <div class="item text-center #active_class#" data-id="#id#">
        <a>
            <span class="d-block category-name">#name#</span>
        </a>
    </div>
</script>
<script type="text/javascript">
    var urls = {
            reportDataUrl: "{{ route('dashboard.getQuestionReportData') }}",
            getDept: '{{ route('admin.ajax.companyDepartment', ':id') }}',
            getLoc: '{{ route('admin.ajax.departmentLocation', ':id') }}',
        },
        defaultImage = "{{ asset('assets/dist/img/user1-128x128.jpg') }}",
        companyScoreColorCode = {
            red: "{{ config("zevolifesettings.zc_survey_score_color_code.red") }}",
            yellow: "{{ config('zevolifesettings.zc_survey_score_color_code.yellow') }}",
            green: "{{ config('zevolifesettings.zc_survey_score_color_code.green') }}",
            grey: "{{ config('zevolifesettings.zc_survey_score_color_code.grey') }}",
        },
        requestParams = {!! $requestParams !!},
        pagination = {
            value: {{ config('zevolifesettings.datatable.pagination.long') }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        };
</script>
<script src="{{ mix('js/question-report.js') }}">
</script>
@endsection

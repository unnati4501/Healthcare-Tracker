@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')
<div class="content-header content-header-bg-color">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-5">
                <h1 class="m-0 text-dark">
                    Question report
                </h1>
            </div>
            <div class="col-md-7 dashboard-breadcrumb-right">
                <div class="row">
                    <div class="form-group col">
                        @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
                        {{ Form::select('company_id', $companies, $company_id, ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'target-data' => 'department_id']) }}
                        @else
                        <div style="display: none">
                            {{ Form::select('company_id', $companies, $company_id, ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'disabled'=>true, 'target-data' => 'department_id']) }}
                        </div>
                        @endif
                    </div>
                    <div class="form-group col">
                        @include('newDashboard.partials.month-range-picker', ['tier' => 1, 'parentId' => 'monthrangeGlobal', 'fromId' => 'globalFromMonth', 'toId' => 'globalToMonth'])
                    </div>
                    <input id="companiesId" name="companiesId" type="hidden" value="{{$companiesId}}">
                    <input id="roleType" name="roleType" type="hidden" value="{{$resellerType}}">
                </div>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div id="report_wrapper" style="display: block;">
            <div class="tabs-wraper">
                <div class="owl-carousel owl-theme" id="question-report-category-tabs">
                    @foreach($categories as $key => $category)
                    <div class="item text-center" data-id="{{ $key }}">
                        <div class="pb-2 pt-2">
                            <a>
                                {{ $category }}
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="tabInline-pane">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 d-flex align-items-center justify-content-center">
                                <div class="speed-chart-area p-b-20 p-t-5">
                                    <canvas class="chart-top-servey" data-categorygaugechart="">
                                    </canvas>
                                    <div class="speed-chart-text">
                                        <span class="score-counter color-green" data-categorygaugechart-value="">
                                            0%
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8 d-flex align-items-center justify-content-center">
                                <div class="w-100 m-t-15 m-t-sm-0">
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
                                    <div class="progress-group" data-subcategory-progressbars="" style="display: none;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" id="subcategory_wrapper" style="display: none;">
                    <div class="card-body">
                        <div id="tabui">
                            <div class="tabs-wraper">
                                <div class="owl-carousel owl-theme" id="question-report-subcategory-tabs">
                                </div>
                            </div>
                            <div class="tabInline-pane" id="subcategory_question_block" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="subcategory_questions_tbl">
                                        <thead>
                                            <tr>
                                                <th width="10%">
                                                    {{ trans('labels.dashboard.audit.sr_no') }}
                                                </th>
                                                <th width="15%">
                                                    {{ trans('labels.dashboard.audit.question_type') }}
                                                </th>
                                                <th>
                                                    {{ trans('labels.dashboard.audit.question') }}
                                                </th>
                                                <th class="text-center" width="15%">
                                                    {{ trans('labels.dashboard.audit.response') }}
                                                </th>
                                                <th class="text-center" width="15%">
                                                    {{ trans('labels.dashboard.audit.percentage') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center" colspan="5">
                                                    <i class="fas fa-spinner fa-lg fa-spin">
                                                    </i>
                                                    <span class="ms-1">
                                                        {{ trans('labels.dashboard.audit.loading_questions') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="mt-4" id="subcategory_progress" style="display: block;">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-lg fa-spin">
                                    </i>
                                    <span class="ms-1">
                                        {{ trans('labels.dashboard.audit.loading_questions') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4" id="report_no_data" style="display: none;">
            <div class="text-center">
                <span class="ms-1">
                    {{ trans('labels.dashboard.audit.no_data_to_display') }}
                </span>
            </div>
        </div>
        <div class="mt-4" id="report_data_process" style="display: none;">
            <div class="text-center">
                <i class="fas fa-spinner fa-lg fa-spin">
                </i>
                <span class="ms-1">
                    {{ trans('labels.dashboard.audit.loading_graphs') }}
                </span>
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
<!-- jQuery Knob -->
<script src="{{ asset('assets/plugins/knob/jquery.knob.js?var='.rand()) }}">
</script>
<!-- jQuery chart -->
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/chart.js/chartjs-plugin-labels.js?var='.rand()) }}">
</script>
<!-- Gauge chart -->
<script src="{{ asset('assets/plugins/gauge/gauge.min.js?var='.rand()) }}">
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
    <div>
        <span>#name#</span>
        <span class="float-end">
            <span class="progress-numb" style="color: #color_code#;">#percetage#%</span>
        </span>
        <div class="progress progress-sm mb-3 c-b">
            <div class="progress-bar progress-bar-striped" role="progressbar" style="width: #percetage#%; background-color: #color_code#;"></div>
        </div>
    </div>
</script>
<script id="category_tabs_template" type="text/html">
    <div class="item text-center #active_class#" data-id="#id#">
    <div class="pb-2 pt-2">
        <a>#name#</a>
    </div>
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
        },
        defaultImage = "{{ asset('assets/dist/img/user1-128x128.jpg') }}",
        companyScoreColorCode = {
            red: "{{ config("zevolifesettings.zc_survey_score_color_code.red") }}",
            yellow: "{{ config('zevolifesettings.zc_survey_score_color_code.yellow') }}",
            green: "{{ config('zevolifesettings.zc_survey_score_color_code.green') }}",
            grey: "{{ config('zevolifesettings.zc_survey_score_color_code.grey') }}",
        },
        requestParams = {!! $requestParams !!},
        pagination = {{ config('zevolifesettings.datatable.pagination.long') }};
</script>
<script src="{{ mix('js/question-report.js') }}">
</script>
@endsection

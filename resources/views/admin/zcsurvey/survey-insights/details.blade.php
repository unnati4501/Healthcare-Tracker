@extends('layouts.app')

@section('after-styles')
<style type="text/css">
    .status-expired { background-color: #f44436 !important; }
</style>
{{--
<link href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700&display=swap?var=<?= rand() ?>" rel="stylesheet"/>
--}}
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcsurvey.survey-insights.breadcrumb', [
    'mainTitle' => trans('survey.insights.title.details'),
    'breadcrumb' => Breadcrumbs::render('survey.insights.details'),
    'back' => true,
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="survey-list owl-carousel owl-theme arrow-theme" id="categoryscoreCarousel">
                    @foreach($categories as $category)
                    <div class="item text">
                        <img src="{{ $category['image'] }}"/>
                        <p class="mb-0 mt-2 d-block">
                            {{ $category['category_name'] }}
                        </p>
                        <p class="gray-900 value" style="color: {{ $category['color_code'] }};">
                            {{ $category['percentage'] }}%
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header detailed-header">
                <div class="d-flex flex-wrap">
                    <div class="survey-heading">
                        <h4>
                            {{ $data->survey_title }}
                        </h4>
                        <p>
                            {{ $data->company_name }}
                        </p>
                        <span class="{{ (($data->status == true) ? 'text-success' : 'text-danger') }}">
                            {{ (($data->status == true) ? trans('survey.insights.labels.progress') : trans('survey.insights.labels.expired')) }}
                        </span>
                    </div>
                    <div>
                        <p>
                            {{ trans('survey.insights.labels.publish_date') }}
                        </p>
                        <span>
                            <i class="far fa-calendar me-2">
                            </i>
                            {{ Carbon\Carbon::parse($data->roll_out_date)->format($date_format) }}
                        </span>
                    </div>
                    <div>
                        <p>
                            {{ trans('survey.insights.labels.expiry_date') }}
                        </p>
                        <span>
                            <i class="far fa-calendar me-2">
                            </i>
                            {{ Carbon\Carbon::parse($data->expire_date)->format($date_format) }}
                        </span>
                    </div>
                    @if(!is_null($upcoming_date))
                    <div>
                        <p>
                            {{ trans('survey.insights.labels.upcoming_date') }}
                        </p>
                        <span>
                            <i class="far fa-calendar me-2">
                            </i>
                            {{ $upcoming_date->format($date_format) }}
                        </span>
                    </div>
                    @endif
                    <div>
                        <div class="chart-wrap">
                            <canvas class="chart-top-servey chart-graph" height="75" id="surveyChart" width="150">
                            </canvas>
                            <span class="chart-value" style="color: {{ $survey_chart_color_code }};">
                                {{ $data->percentage }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-0">
                @foreach($categories AS $key => $category)
                <div class="card-table-outer">
                    <div class="row">
                        <div class="col-xl-3 col-lg-4 align-self-center">
                            <div class="text-center">
                                <span class="badge badge-pill chart-badge" style="background-color: {{ $category['color_code'] }};">
                                    {{ $category['category_name'] }}
                                </span>
                            </div>
                            <div class="chart-wrap survey-chart-gap">
                                <canvas class="chart-top-servey chart-graph-lg surveyCategoryChart" data-color-code="{{ $category['color_code'] }}" data-value="{{ $category['percentage'] }}" height="75" width="150">
                                </canvas>
                                <span class="chart-value chart-value-lg" style="color: {{ $category['color_code'] }};">
                                    {{ $category['percentage'] }}%
                                </span>
                            </div>
                        </div>
                        <div class="col-xl-9 col-lg-8 border-lg-left">
                            <div class="table-responsive">
                                <table class="table custom-table surveyCategoryQuestions" data-id="{{ $category['category_id'] }}" id="surveyCategoryQuestionsManagement{{ $category['category_id'] }}">
                                    <thead>
                                        <tr>
                                            <th class="no-sort th-btn-sm text-center">
                                                {{ trans('survey.insights.insight_table.sr_no') }}
                                            </th>
                                            <th>
                                                {{ trans('survey.insights.insight_table.question_type') }}
                                            </th>
                                            <th class="th-btn-3">
                                                {{ trans('survey.insights.insight_table.question') }}
                                            </th>
                                            <th>
                                                {{ trans('survey.insights.insight_table.category') }}
                                            </th>
                                            <th>
                                                {{ trans('survey.insights.insight_table.sub_category') }}
                                            </th>
                                            <th>
                                                {{ trans('survey.insights.insight_table.responses') }}
                                            </th>
                                            <th>
                                                {{ trans('survey.insights.insight_table.options') }}
                                            </th>
                                            <th>
                                                {{ trans('survey.insights.insight_table.percentage') }}
                                            </th>
                                            <th class="no-sort th-btn-sm">
                                                {{ trans('survey.insights.insight_table.action') }}
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
                @endforeach
            </div>
        </div>
    </div>
</section>
@include('admin.zcsurvey.survey-insights.common.free-text.modal-box')
@include('admin.zcsurvey.survey-insights.common.choice.modal-box')
@endsection
@section('after-scripts')
<script src="{{ asset('assets/plugins/gauge/gauge.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var gaugeChartOptions = {
        animationSpeed: 32,
        angle: 0.0,
        lineWidth: 0.25,
        pointer: {
            length: 0.6,
            strokeWidth: 0.025,
            color: '#000000'
        },
        strokeColor: '#f2f4f4',
        highDpiSupport: true
    },
    surveyCharts = {
        mainChart: undefined,
        subcharts: [],
    },
    urls = {
        surveyQs: `{{ route('admin.surveyInsights.getSurveyInsightQuestionsTableData', [$data->id, ":category_id"]) }}`,
        question: `{{ route('admin.zcquestionbank.showQuestion', ':id') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    datatables = [],
    mainChartColorCode = `{{ $survey_chart_color_code }}`,
    mainChartPercentage = `{{ $data->percentage }}`;
</script>
<script src="{{ mix('js/survey_insights/details.js') }}" type="text/javascript">
</script>
@endsection

@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/easy-responsive-tabs/css/easy-responsive-tabs.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')
<div class="content-header no-default-select2">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md">
                <h1 class="m-0 text-dark">
                    Wellbeing Survey Board
                </h1>
            </div>
            <div class="col-md-auto dashboard-breadcrumb-right">
                <div class="row">
                    <div class="form-group col">
                        {{ Form::select('company_id', $company, $company_id, ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true']) }}
                    </div>
                    {{--
                    <div class="form-group col">
                        {{ Form::select('department_id', $department, null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true', 'data-containerCssClass' => 'd-none']) }}
                    </div>
                    --}}
                    {{--
                    <div class="form-group col">
                        {{ Form::select('age', $age, null,['class' => 'form-control select2', 'id'=>'age', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.age'), 'data-placeholder' => trans('labels.dashboard.age'), 'data-allow-clear' => 'true', 'data-containerCssClass' => 'd-none']) }}
                    </div>
                    --}}
                </div>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="card h-100 m-0">
                    <div class="card-header">
                        <h3 class="card-title d-flex align-items-center">
                            <span>
                                Users
                            </span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="health-score-survey-list">
                            <li>
                                <div class="health-score-survey-list-text color-dot primary-color-dot">
                                    {{ trans('labels.healthscore_dashboard.health_score_survey.ch_lg_1') }}
                                </div>
                                <span data-totaluser="">
                                    0
                                </span>
                            </li>
                            <li>
                                <div class="health-score-survey-list-text color-dot yellow-color-dot">
                                    {{ trans('labels.healthscore_dashboard.health_score_survey.ch_lg_2') }}
                                </div>
                                <span data-completerallsurveys="">
                                    0
                                </span>
                            </li>
                            <li>
                                <div class="health-score-survey-list-text color-dot gray-color-dot">
                                    {{ trans('labels.healthscore_dashboard.health_score_survey.ch_lg_3') }}
                                </div>
                                <span data-notattempt="">
                                    0
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100 m-0">
                    <div class="card-header">
                        <h3 class="card-title d-flex align-items-center">
                            <span>
                                Physical Survey
                            </span>
                        </h3>
                    </div>
                    <div class="align-items-center card-body d-flex h-100 justify-content-center" data-physical-survey-knob="">
                        <input class="knob-chart knob-chart-font-18" data-fgcolor="#FFB35E" data-height="150" data-bs-html="true" data-linecap="round" data-placement="top" data-readonly="true" data-thickness=".15" data-bs-toggle="tooltip" data-width="150" id="physical_survey_knob" readonly="readonly" title="" type="text" value="0"/>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100 m-0">
                    <div class="card-header">
                        <h3 class="card-title d-flex align-items-center">
                            <span>
                                Psychological Survey
                            </span>
                        </h3>
                    </div>
                    <div class="align-items-center card-body d-flex h-100 justify-content-center" data-psychological-survey-knob="">
                        <input class="knob-chart knob-chart-font-18" data-fgcolor="#FFB35E" data-height="150" data-bs-html="true" data-linecap="round" data-placement="top" data-readonly="true" data-thickness=".15" data-bs-toggle="tooltip" data-width="150" id="psychological_survey_knob" readonly="readonly" title="" type="text" value="0"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Wellbeing Survey
                </h3>
            </div>
            <div class="card-body" data-healthscoresurveychart="">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <ul class="side-tabbing-list" id="hsCategoryList">
                            <li class="active" data-hex="#8D3921" data-id="1" data-type="healthScoreSurvey">
                                <img src="{{ asset('assets/dist/img/health-score/physical.png') }}"/>
                                <div class="side-tabbing-list-text">
                                    Physical
                                </div>
                            </li>
                            <li data-hex="#00173E" data-id="2" data-type="healthScoreSurvey">
                                <img src="{{ asset('assets/dist/img/health-score/psychological.png') }}"/>
                                <div class="">
                                    Psychological
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-10">
                        <div class="exercise-hours-chart-area wellbeing-survey">
                            <canvas height="150" id="healthScoreSurveyChart">
                            </canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{ trans('labels.healthscore_dashboard.physical.label') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <ul class="side-tabbing-list" id="physicalCategoryList">
                                    <li class="active" data-hex="#39476F" data-id="1" data-type="healthScorePhysicalCatWise">
                                        <img src="{{ asset('assets/dist/img/health-score/physical-activity.png') }}"/>
                                        <div class="side-tabbing-list-text">
                                            Physical activity
                                        </div>
                                    </li>
                                    <li data-hex="#C9DD03" data-id="2" data-type="healthScorePhysicalCatWise">
                                        <img src="{{ asset('assets/dist/img/health-score/sleep.png') }}"/>
                                        <div class="side-tabbing-list-text">
                                            Sleep
                                        </div>
                                    </li>
                                    <li data-hex="#FF6C2E" data-id="3" data-type="healthScorePhysicalCatWise">
                                        <img src="{{ asset('assets/dist/img/health-score/nutrition.png') }}"/>
                                        <div class="side-tabbing-list-text">
                                            Nutrition
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-10">
                                <div class="exercise-hours-chart-area wellbeing-survey">
                                    <canvas height="150" id="physicalScoreChart">
                                    </canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{ trans('labels.healthscore_dashboard.psychological.label') }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <ul class="side-tabbing-list" id="psychologicalCategoryList">
                                    <li class="active" data-hex="#EAAB00" data-id="5" data-type="healthScorePsychologicalCatWise">
                                        <img src="{{ asset('assets/dist/img/health-score/positive emotion.png') }}"/>
                                        <div class="side-tabbing-list-text">
                                            Positive emotion
                                        </div>
                                    </li>
                                    <li data-hex="#675C53" data-id="6" data-type="healthScorePsychologicalCatWise">
                                        <img src="{{ asset('assets/dist/img/health-score/engagement.png') }}"/>
                                        <div class="side-tabbing-list-text">
                                            Engagement
                                        </div>
                                    </li>
                                    <li data-hex="#3E9A92" data-id="7" data-type="healthScorePsychologicalCatWise">
                                        <img src="{{ asset('assets/dist/img/health-score/relationship.png') }}"/>
                                        <div class="side-tabbing-list-text">
                                            Relationships
                                        </div>
                                    </li>
                                    <li data-hex="#C9DD03" data-id="8" data-type="healthScorePsychologicalCatWise">
                                        <img src="{{ asset('assets/dist/img/health-score/meaning.png') }}"/>
                                        <div class="side-tabbing-list-text">
                                            Meaning
                                        </div>
                                    </li>
                                    <li data-hex="#FF6C2E" data-id="9" data-type="healthScorePsychologicalCatWise">
                                        <img src="{{ asset('assets/dist/img/health-score/achievement.png') }}"/>
                                        <div class="side-tabbing-list-text">
                                            Achievement
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-10">
                                <div class="exercise-hours-chart-area wellbeing-survey">
                                    <canvas height="150" id="psychologicalScoreChart">
                                    </canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/knob/jquery.knob.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var _token = $('input[name="_token"]').val(),
        urls = {
            getDept: '{{ route("admin.ajax.companyDepartment", ":id") }}',
            chartData: "{{ route('admin.wellbeingSurveyBoard.getChartData') }}",
        };

        $(document).ready(function() {
            var comapany_visibility = "{{ $visibility }}";
            $('#company_id').select2({
                containerCssClass: comapany_visibility
            });
        });
</script>
<script src="{{ mix('js/hsDashboard.js') }}">
</script>
@endsection

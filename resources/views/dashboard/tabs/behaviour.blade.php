<div class="row">
    <div class="{{ $firstBlockClass }}">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Exercise Ranges
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        @include('dashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeExerciseRanges', 'fromId' => 'daterangeExerciseRangesFrom', 'toId' => 'daterangeExerciseRangesFromTo', 'tooltip' => 'Range of app users based on exercise hours.'])
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxl-6 col-lg-5 align-self-center">
                        <div class="chart-height donut-chart-height">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="doughnutExerciseRanges">
                                </canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-lg-7 mt-4 mt-lg-0 align-self-center">
                        <div class="chart-legend" id="exerciseRange-legend">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-5 {{ $teamSessionBlock }}">
        <div class="card dashboard-card has-bg">
            <div class="card-body" data-team-block="">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Teams
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        <div class="dashboard-card-filter">
                            <div>
                                <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="#" title="New Teams (last 30 Days) - Newly added teams in the last 30 days, must be associated with users. Total Teams - Total registered teams, must be associated with users.">
                                    <i class="fal fa-info-circle" data-placement="bottom" data-bs-toggle="tooltip">
                                    </i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="info-block mw-100 no-border">
                    <span>
                        New Teams (last 30 Days)
                    </span>
                    <span class="ms-4 info-block-value" data-new-teams="">
                    </span>
                </div>
                <div class="info-block mw-100 no-border">
                    <span>
                        Total Teams
                    </span>
                    <span class="ms-4 info-block-value" data-total-teams="">
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4 mt-xl-5">
    <div class="col-xl-7">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Steps Ranges
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        @include('dashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeStepRanges', 'fromId' => 'daterangeStepRangesFrom', 'toId' => 'daterangeStepRangesFromTo', 'tooltip'=>'Range of app users based on steps.'])
                    </div>
                </div>
                <div class="row">
                    <div class="col-xxl-6 col-lg-5 align-self-center">
                        <div class="chart-height donut-chart-height">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="doughnutStepsRanges">
                                </canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-lg-7 mt-4 mt-lg-0 align-self-center">
                        <div class="chart-legend" id="stepRange-legend">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card dashboard-card has-bg">
            <div class="card-body" data-challenge-block="">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Challenges
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        <div class="dashboard-card-filter">
                            <div>
                                <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="#" title="Total count of all challenges.">
                                    <i class="fal fa-info-circle" data-placement="bottom" data-bs-toggle="tooltip">
                                    </i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="info-block mw-100 no-border">
                    <span>
                        Ongoing Challenges
                    </span>
                    <span class="ms-4 info-block-value" data-ongoing-challenge="">
                    </span>
                </div>
                <div class="info-block mw-100 no-border">
                    <span>
                        Upcoming Challenges
                    </span>
                    <span class="ms-4 info-block-value" data-upcoming-challenge="">
                    </span>
                </div>
                <div class="info-block mw-100 no-border">
                    <span>
                        Completed Challenges
                    </span>
                    <span class="ms-4 info-block-value" data-completed-challenge="">
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4 mt-xl-5">
    <div class="col-xl-6">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Most Popular Exercise - Tracker
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        @include('dashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeMostPopularExTracker', 'fromId' => 'daterangeMostPopularExTrackerFrom', 'toId' => 'daterangeMostPopularExTrackerTo', 'tooltip'=>'Most popular exercise based on maximum number of sessions -Tracker.'])
                    </div>
                </div>
                <div class="chart-height">
                    <div class="canvas-wrap">
                        <canvas class="canvas" id="chartExerciseTracker">
                        </canvas>
                    </div>
                </div>
                {{-- <div class="">
                    <div class="">
                        <div class="chart-height donut-chart-height small">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="chartExerciseTracker">
                                </canvas>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="chart-legend" id="exerciseTracker-legend">
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Most Popular Exercise -Manual
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        @include('dashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeMostPopularExManual', 'fromId' => 'daterangeMostPopularExManualFrom', 'toId' => 'daterangeMostPopularExManualTo', 'tooltip'=>'Most popular exercise based on maximum number of sessions -Manual.'])
                    </div>
                </div>
                <div class="chart-height">
                    <div class="canvas-wrap">
                        <canvas class="canvas" id="chartExerciseManual">
                        </canvas>
                    </div>
                </div>
                {{-- <div class="">
                    <div class="">
                        <div class="chart-height donut-chart-height small">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="chartExerciseManual">
                                </canvas>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="chart-legend" id="exerciseManual-legend">
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
</div>
<div class="row mt-4 mt-xl-5">
    <div class="col-xl-6">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Average Steps
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        @include('dashboard.partials.month-range-picker', ['tier' => 3, 'parentId' => 'daterangeStepsPeriod', 'fromId' => 'daterangeStepsPeriodFrom', 'toId' => 'daterangeStepsPeriodFromTo', 'tooltip' => 'The categorisation of users based on their step activity for the period.'])
                    </div>
                </div>
                <div class="chart-height">
                    <div class="canvas-wrap">
                        <canvas class="canvas" id="chartStepsPeriod">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Average Calories
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        @include('dashboard.partials.month-range-picker', ['tier' => 3, 'parentId' => 'daterangeCaloriesPeriod', 'fromId' => 'daterangeCaloriesPeriodFrom', 'toId' => 'daterangeCaloriesPeriodFromTo', 'tooltip' => 'The categorisation of users based on their calorie burn for the period.'])
                    </div>
                </div>
                <div class="chart-height">
                    <div class="canvas-wrap">
                        <canvas class="canvas" id="chartCaloriesPeriods">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4 mt-xl-5">
    <div class="col-xl-5">
        <div class="card dashboard-card">
            <div class="card-body data-centered">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Sync last 3 days
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        <div class="dashboard-card-filter">
                            <div>
                                <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="#" title="Users who have synced over the past 3 days.">
                                    <i class="fal fa-info-circle" data-placement="bottom" data-bs-toggle="tooltip">
                                    </i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="chart-height donut-chart-height">
                    <div class="canvas-wrap">
                        <canvas class="canvas" id="syncInfo">
                        </canvas>
                        <div class="canvas-center-data" data-sync-percent="">
                        </div>
                    </div>
                </div>
                <div class="chart-legend legend-two-col" id="syncInfo-legend">
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            BMI
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center text-end">
                        <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="#" title="BMI ranges of app users.">
                            <i class="fal fa-info-circle">
                            </i>
                        </a>
                    </div>
                </div>
                <ul class="nav nav-tabs tabs-line-style male-female-list" data-gender-filter="">
                    <li class="nav-item active" style="cursor:pointer;">
                        <a class="nav-link active" data-gender="null">
                            All
                        </a>
                    </li>
                    @foreach($genders as $key => $gender)
                    <li class="nav-item" style="cursor:pointer;">
                        <a class="nav-link" data-gender="{{ $key }}">
                            {{ __($gender) }}
                        </a>
                    </li>
                    @endforeach
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active">
                        <div class="row">
                            <div class="col-xxl-6 col-lg-5 align-self-center">
                                <div class="chart-height donut-chart-height">
                                    <div class="canvas-wrap">
                                        <canvas class="canvas" id="doughnutBMI">
                                        </canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xxl-6 col-lg-7 col-md-6 mt-4 mt-lg-0 align-self-center" data-bmi-block="">
                                <div class="info-block mw-100 stacked">
                                    <div>
                                        <span>
                                            Total Users
                                        </span>
                                        <br/>
                                        <div class="ms-4 info-block-value" data-bmi-users="">
                                            0
                                        </div>
                                    </div>
                                    <div>
                                        <span>
                                            Average Weight
                                        </span>
                                        <br/>
                                        <div class="ms-4 info-block-value" data-bmi-weight="">
                                            0
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chart-legend legend-two-col" id="bmiAll-legend">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4 mt-xl-5">
    <div class="col-xl-12">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Moods Analysis
                            <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="{{ trans('dashboard.behaviour.tooltips.moods_analysis') }}">
                            </i>
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center text-end">
                        <ul class="week-month-year-list justify-content-end d-inline-flex" data-mood-analysis-from-duration="">
                            <li class="active" data-mood-analysis-duration="7">
                                Week
                            </li>
                            <li data-mood-analysis-duration="30">
                                Month
                            </li>
                            <li data-mood-analysis-duration="365">
                                Year
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="chart-height">
                    <div class="canvas-wrap">
                        <canvas class="canvas" id="chartMoodsAnalysis">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4 mt-xl-5">
    <div class="col-xl-12">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Superstars
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        @include('dashboard.partials.month-range-picker', ['tier' => 4, 'parentId' => 'daterangeSuperstars', 'fromId' => 'daterangeSuperstarsFrom', 'toId' => 'daterangeSuperstarsFromTo'])
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="superstars-box" data-active-team="">
                            <div class="superstars-box-title active-team">
                                <span>
                                    Active Teams
                                </span>
                                <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="#" title="5 most active teams based on the average hours of exercise activity over the selected month range.">
                                    <i class="fal fa-info-circle">
                                    </i>
                                </a>
                            </div>
                            <ul class="sx-list">
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="superstars-box" data-active-individual="">
                            <div class="superstars-box-title active-individual">
                                <span>
                                    Active Individual
                                </span>
                                <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="#" title="Top 5 active app users based on the total hours of exercise activity over the selected month range.">
                                    <i class="fal fa-info-circle">
                                    </i>
                                </a>
                            </div>
                            <ul class="sx-list">
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="superstars-box" data-badges-earned="">
                            <div class="superstars-box-title badges-earned">
                                <span>
                                    Badges Earned
                                </span>
                                <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="#" title="Top 5 users based on earned badges over the selected month range.">
                                    <i class="fal fa-info-circle">
                                    </i>
                                </a>
                            </div>
                            <ul class="sx-list">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div>
    <!-- card -->
    {{-- <div class="card h-100 m-0">
        <div class="card-header">
            <h3 class="card-title d-flex align-items-center">
                <span>
                    Physical Wellbeing Score
                </span>
                <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Physical wellbeing survey score based on each individuals wellbeing score.">
                </i>
            </h3>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-5 col-xl-6">
                    <div class="row align-items-center">                        <div class="col-sm-6">
                            <div class="doughnut-number-of-users">
                                <canvas height="40" id="doughnutPhysicalScore" width="40">
                                </canvas>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <table class="table circle-colorfull-list">
                                <tbody>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-obese">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            Low- 0-60%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-overweight">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            Moderate - 60- 80%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-normal">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            High - 80- 100%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-xl-6">
                    <div class="d-sm-flex justify-content-end">
                        @include('newDashboard.partials.month-range-picker', ['tier' => 1, 'parentId' => 'daterangeHsPhysical', 'fromId' => 'daterangeHsPhysicalFrom', 'toId' => 'daterangeHsPhysicalFromTo'])
                    </div>
                    <div class="row" data-sub-category-block="">
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    <!-- ./card -->
    <!-- card -->
    {{-- <div class="card">
        <div class="card-header">
            <h3 class="card-title d-flex align-items-center">
                <span>
                    Psychological Wellbeing Score
                </span>
                <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Psychological wellbeing survey score based on each individuals wellbeing score.">
                </i>
            </h3>
        </div>
        <div class="card-body">
            <div class="d-sm-flex justify-content-end">
                <div class="form-group">
                    @include('newDashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeHsPsychological', 'fromId' => 'daterangeHsPsychologicalFrom', 'toId' => 'daterangeHsPsychologicalFromTo'])
                </div>
            </div>
            <div class="row ">
                <div class="col-lg-5 col-xl-6">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <div class="doughnut-number-of-users">
                                <canvas height="40" id="doughnutPsychologicalScore" width="40">
                                </canvas>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <table class="table circle-colorfull-list">
                                <tbody>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-obese">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            Low- 0-60%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-overweight">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            Moderate - 60- 80%
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-normal">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            High - 80- 100%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-xl-6">
                    <div class="row" data-pws-sub-category-block="">
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    <!-- ./card -->
    <div class="row">
        <div class="col-lg-6">
            <!-- card -->
            <div class="card m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Exercise Ranges
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Range of app users based on exercise hours.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body">
                    <div class="d-sm-flex justify-content-end">
                        @include('newDashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeExerciseRanges', 'fromId' => 'daterangeExerciseRangesFrom', 'toId' => 'daterangeExerciseRangesFromTo'])
                    </div>
                    <div class="row h-100 align-items-center">
                        <div class="col-md-6">
                            <div class="doughnut-number-of-users">
                                <canvas height="40" id="doughnutExerciseRanges" width="40">
                                </canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table class="table circle-colorfull-list">
                                <tbody>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-obese">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            Low
                                        </td>
                                        <td class="circle-count">
                                            (0-1 Hours)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-overweight">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            Moderate
                                        </td>
                                        <td class="circle-count">
                                            (1-4 Hours)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-normal">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            High
                                        </td>
                                        <td class="circle-count">
                                            (4-10 Hours)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-underweight">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            Very High
                                        </td>
                                        <td class="circle-count">
                                            (10+ Hours)
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>
        <div class="col-lg-6">
            <!-- card -->
            <div class="card m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Steps Ranges
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Range of app users based on steps.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body">
                    <div class="d-sm-flex justify-content-end">
                        @include('newDashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeStepRanges', 'fromId' => 'daterangeStepRangesFrom', 'toId' => 'daterangeStepRangesFromTo'])
                    </div>
                    <div class="row h-100 align-items-center">
                        <div class="col-md-6">
                            <div class="doughnut-number-of-users">
                                <canvas height="40" id="doughnutStepsRanges" width="40">
                                </canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table class="table circle-colorfull-list">
                                <tbody>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-obese">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            Low
                                        </td>
                                        <td class="circle-count">
                                            (0-8k steps)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-overweight">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            Moderate
                                        </td>
                                        <td class="circle-count">
                                            (8k- 12K steps)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="singel-circle">
                                            <div class="singel-circle-normal">
                                            </div>
                                        </td>
                                        <td class="circle-text">
                                            High
                                        </td>
                                        <td class="circle-count">
                                            (12k + steps)
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <!-- card -->
            <div class="card h-100 m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Teams
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Total Team-Total registered teams, must be associated with users. New Teams- Newly added teams in the last 30 days, must be associated with users.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body" data-team-block="">
                    <table class="dash-info-list-table">
                        <tbody>
                            <tr>
                                <td>
                                    New Teams
                                    <small>
                                        (last 30 Days)
                                    </small>
                                </td>
                                <td class="dash-info-count text-end" data-new-teams="">
                                    0
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Total Teams
                                </td>
                                <td class="dash-info-count text-end" data-total-teams="">
                                    0
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>
        <div class="col-md-6 mb-3">
            <!-- card -->
            <div class="card h-100 m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Challenges
                        </span>
                        @if($role->group == 'zevo')
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Total count of all challenges">
                        </i>
                        @else
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Total count of all challenges based on the status (Individual challenge, Team challenge, Intercompany challenge, and Company Goal)">
                        </i>
                        @endif
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body" data-challenge-block="">
                    <table class="dash-info-list-table dash-info-r-space-table">
                        <tbody>
                            <tr>
                                <td>
                                    Ongoing Challenges
                                </td>
                                <td class="dash-info-count text-end" data-ongoing-challenge="">
                                    0
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Upcoming Challenges
                                </td>
                                <td class="dash-info-count text-end" data-upcoming-challenge="">
                                    0
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Completed Challenges
                                </td>
                                <td class="dash-info-count text-end" data-completed-challenge="">
                                    0
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>
    </div>
    <!-- Steps Period -->
    <div class="row">
        <div class="col-lg-6 mb-3">
            <!-- card -->
            <div class="card h-100 m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Average Steps
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="The categorisation of users based on their step activity for the period.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body">
                    <div class="d-sm-flex justify-content-end">
                        <div class="d-sm-flex justify-content-end">
                            @include('newDashboard.partials.month-range-picker', ['tier' => 3, 'parentId' => 'daterangeStepsPeriod', 'fromId' => 'daterangeStepsPeriodFrom', 'toId' => 'daterangeStepsPeriodFromTo'])
                        </div>
                    </div>
                    <div class="exercise-hours-chart-area">
                        <canvas height="250" id="chartStepsPeriod" width="400">
                        </canvas>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>
        <div class="col-lg-6 mb-3">
            <!-- card -->
            <div class="card h-100 m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Average Calories
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="The categorisation of users based on their calorie burn for the period.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body">
                    <div class="d-sm-flex justify-content-end">
                        @include('newDashboard.partials.month-range-picker', ['tier' => 3, 'parentId' => 'daterangeCaloriesPeriod', 'fromId' => 'daterangeCaloriesPeriodFrom', 'toId' => 'daterangeCaloriesPeriodFromTo'])
                    </div>
                    <div class="exercise-hours-chart-area">
                        <canvas height="250" id="chartCaloriesPeriods" width="400">
                        </canvas>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>
    </div>
    <!-- ./Steps Period -->
    {{-- <div class="card">
        <div class="card-header">
            <h3 class="card-title d-flex align-items-center">
                <span>
                    Popular Exercises
                </span>
                <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Top exercise based on the activity of users.">
                </i>
            </h3>
        </div>
        <div class="card-body">
            <div class="text-end">
                <ul class="week-month-year-list justify-content-end" data-popular-exercises-from-duration="">
                    <li class="active" data-popular-exercises-duration="7">
                        Week
                    </li>
                    <li data-popular-exercises-duration="30">
                        Month
                    </li>
                    <li data-popular-exercises-duration="365">
                        Year
                    </li>
                </ul>
            </div>
            <div class="popular-exercises-chart-area">
                <canvas height="110" id="chartPopularExercises" width="300">
                </canvas>
            </div>
        </div>
    </div> --}}
    <div class="row d-flex mb-3">
        <div class="col-xl-5">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Sync last 3 days
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Users who have synced over the past 3 days.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body d-flex flex-column" data-sync-details="">
                    <div class="total-recipes-chart mt-auto mb-3" data-knob="">
                        <input class="knob-chart knob-chart-font-18" data-fgcolor="#ffb35e" data-height="180" data-linecap="round" data-readonly="true" data-thickness=".1" data-width="180" readonly="readonly" type="text" value="0"/>
                    </div>
                    <div class="row mb-auto">
                        <div class="col">
                            <div class=" total d-sync-counter">
                                <div class="d-sync-counter-per" data-sync-percent="">
                                    0
                                </div>
                                <div class="d-sync-counter-text">
                                    Synced
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class=" active d-sync-counter">
                                <div class="d-sync-counter-per" data-notsync-percent="">
                                    0
                                </div>
                                <div class="d-sync-counter-text">
                                    Not Synced
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col-12 col-sm">
                            <h3 class="card-title d-flex align-items-center">
                                <span>
                                    BMI
                                </span>
                                <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="BMI ranges of app users.">
                                </i>
                            </h3>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body pb-0 pt-1">
                    <div class="row align-items-center">
                        <div class="col-md-12 text-center">
                            <ul class="male-female-list" data-gender-filter="">
                                <li class="active" data-gender="null">
                                    All
                                </li>
                                @foreach($genders as $key => $gender)
                                <li data-gender="{{ $key }}">
                                    {{ __($gender) }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-lg-8">
                            <div class="doughnut-number-of-users">
                                <canvas height="40" id="doughnutBMI" width="40">
                                </canvas>
                            </div>
                            <div class="row d-flex align-items-center">
                                <div class="col-md-6">
                                    <table class="table circle-colorfull-list">
                                        <tbody>
                                            <tr>
                                                <td class="singel-circle">
                                                    <div class="singel-circle-underweight">
                                                    </div>
                                                </td>
                                                <td class="circle-text">
                                                    Underweight
                                                </td>
                                                <td class="circle-count">
                                                    Less than 18.5
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="singel-circle">
                                                    <div class="singel-circle-normal">
                                                    </div>
                                                </td>
                                                <td class="circle-text">
                                                    Normal
                                                </td>
                                                <td class="circle-count">
                                                    18.5 to 25
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table circle-colorfull-list">
                                        <tbody>
                                            <tr>
                                                <td class="singel-circle">
                                                    <div class="singel-circle-overweight">
                                                    </div>
                                                </td>
                                                <td class="circle-text">
                                                    Overweight
                                                </td>
                                                <td class="circle-count">
                                                    25 to 30
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="singel-circle">
                                                    <div class="singel-circle-obese">
                                                    </div>
                                                </td>
                                                <td class="circle-text">
                                                    Obese
                                                </td>
                                                <td class="circle-count">
                                                    More than 30
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4" data-bmi-block="">
                            <div class="bmi-count-area">
                                <div class="bmi-count-area-text">
                                    <span>
                                        Total Users
                                    </span>
                                    <br/>
                                    <div data-bmi-users="">
                                        0
                                    </div>
                                </div>
                                <div class="bmi-count-area-text">
                                    <span>
                                        Average Weight
                                    </span>
                                    <br/>
                                    <div data-bmi-weight="">
                                        0
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    <!-- Moods analysis -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-12 col-sm">
                    <h3 class="card-title">
                        Moods Analysis
                    </h3>
                </div>
                <div class="col col-sm-auto">
                    <ul class="week-month-year-list justify-content-end" data-mood-analysis-from-duration="">
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
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="exercise-hours-chart-area">
                <canvas height="300" id="chartMoodsAnalysis" width="100%">
                </canvas>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- ./Moods analysis -->
    <!-- Exercise hours -->
    <div class="card">
        <div class="card-header border-">
            <div class="row align-items-center">
                <div class="col-12 col-sm">
                    <h3 class="card-title">
                        Superstars
                    </h3>
                </div>
                <div class="col col-sm-auto">
                    <div class="d-sm-flex justify-content-end">
                        @include('newDashboard.partials.month-range-picker', ['tier' => 4, 'parentId' => 'daterangeSuperstars', 'fromId' => 'daterangeSuperstarsFrom', 'toId' => 'daterangeSuperstarsFromTo'])
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0" data-active-team="">
                    <div class="superstars-box">
                        <div class="superstars-box-title active-team">
                            <span class="me-2">
                                Active Teams
                            </span>
                            <i class="fas fa-info-circle ms-auto info-icon " data-original-title="5 most active teams based on the hours of exercise activity over the last 7 days." data-placement="bottom" data-bs-toggle="tooltip" title="">
                            </i>
                        </div>
                        <div class="superstars-box-img" data-first-team-logo="">
                            <img alt="icon" class="" src="{{asset('assets/dist/img/user1-128x128.jpg')}}"/>
                        </div>
                        <div class="superstars-box-textarea">
                            <h4 class="sx-name" data-first-team-name="">
                            </h4>
                            <small data-first-team-company="">
                            </small>
                            <div class="sx-count" data-first-team-hours="">
                            </div>
                            <div class="sx-text" data-text="">
                            </div>
                        </div>
                        <ul class="sx-list">
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0" data-active-individual="">
                    <div class="superstars-box">
                        <div class="superstars-box-title active-individual">
                            <span class="me-2">
                                Active Individuals
                            </span>
                            <i class="fas fa-info-circle ms-auto info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Top 5 active app users based on the hours of exercise activity over the past 7 days.">
                            </i>
                        </div>
                        <div class="superstars-box-img" data-first-user-logo="">
                            <img alt="icon" class="" src="{{asset('assets/dist/img/user1-128x128.jpg')}}"/>
                        </div>
                        <div class="superstars-box-textarea">
                            <h4 class="sx-name" data-first-user-name="">
                            </h4>
                            <small data-first-user-company="">
                            </small>
                            <div class="sx-count" data-first-user-hours="">
                            </div>
                            <div class="sx-text" data-text="">
                            </div>
                        </div>
                        <ul class="sx-list">
                        </ul>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0" data-badges-earned="">
                    <div class="superstars-box">
                        <div class="superstars-box-title badges-earned">
                            <span class="me-2">
                                Badges Earned
                            </span>
                            <i class="fas fa-info-circle ms-auto info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Top 5 app users based on earned badges over the last 7 days.">
                            </i>
                        </div>
                        <div class="superstars-box-img" data-first-user-logo="">
                            <img alt="icon" class="" src="{{asset('assets/dist/img/user1-128x128.jpg')}}"/>
                        </div>
                        <div class="superstars-box-textarea">
                            <h4 class="sx-name" data-first-user-name="">
                            </h4>
                            <small data-first-user-company="">
                            </small>
                            <div class="sx-count" data-first-user-badges="">
                            </div>
                            <div class="sx-text" data-text="">
                            </div>
                        </div>
                        <ul class="sx-list">
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
</div>
<div>
    <!-- card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title d-flex align-items-center">
                <span>
                    Psychological Wellbeing Score
                </span>
                <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Psychological wellbeing survey score based on each individuals wellbeing score.">
                </i>
            </h3>
        </div>
        <!-- /.card-header -->
        <!-- card-body -->
        <div class="card-body">
            <div class="d-sm-flex justify-content-end">
                <div class="form-group">
                    @include('newDashboard.partials.month-range-picker', ['tier' => 1, 'parentId' => 'daterangeHsPsychological', 'fromId' => 'daterangeHsPsychologicalFrom', 'toId' => 'daterangeHsPsychologicalFromTo'])
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
                    <div class="row" data-sub-category-block="">
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- ./card -->
    <!-- Meditation hours -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-12 col-sm">
                    <h3 class="card-title">
                        Meditation hours
                    </h3>
                </div>
                <div class="col col-sm-auto">
                    <ul class="week-month-year-list justify-content-end" data-meditiation-hours-from-duration="">
                        <li class="active" data-meditiation-hours-duration="7">
                            Week
                        </li>
                        <li data-meditiation-hours-duration="30">
                            Month
                        </li>
                        <li data-meditiation-hours-duration="365">
                            Year
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-10">
                    <div class="exercise-hours-chart-area">
                        <canvas height="300" id="chartMeditationHours" width="400">
                        </canvas>
                    </div>
                </div>
                <div class="col-md-2 text-center" data-meditiation-hours-block="">
                    <div class="exercise-hours-text">
                        <span>
                            Total Users
                        </span>
                        <br/>
                        <div data-meditation-hours-total-users="">
                            0
                        </div>
                    </div>
                    <br/>
                    <div class="exercise-hours-text">
                        <span>
                            Avg Meditation Time
                        </span>
                        <br/>
                        <div data-meditation-hours-avg-hours="">
                            0
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- ./Meditation hours -->
    <!-- Steps Period -->
    <div class="row">
        <div class="col-lg-6 mb-3">
            <!-- card -->
            <div class="card h-100 m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Popular Meditation Category
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Popular meditation categories based on the views.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body">
                    <div class="mt-lg-5">
                        <canvas height="300" id="chartPopularMeditationCategory" width="300">
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
                            Top 10 Track
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Top 10 meditation track based on the views.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body">
                    <div class="d-sm-flex justify-content-end">
                        @include('newDashboard.partials.month-range-picker', ['tier' => 3, 'parentId' => 'daterangeTopMeditationTracks', 'fromId' => 'daterangeTopMeditationTracksFrom', 'toId' => 'daterangeTopMeditationTracksFromTo'])
                    </div>
                    <div class="">
                        <canvas height="300" id="chartTopTrack" width="300">
                        </canvas>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>
    </div>
    <!-- ./Steps Period -->
    <!-- Moods analysis -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-12 col-sm">
                    <h3 class="card-title">
                        Moods analysis
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
</div>
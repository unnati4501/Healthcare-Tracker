
    <div class="row">
        <div class="col-md-12 mb-3">
            <!-- card -->
            <div class="card h-100 m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Users
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Active Users - Those users who have logged in over the past 30 days from the current date. Total User - The total registered system users.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body" data-user-block="">
                    <table class="dash-info-list-table">
                        <tbody>
                            {{-- <tr>
                                <td>
                                    Total Active Users
                                </td>
                                <td class="dash-info-count text-end" data-active-total-users="">
                                    0
                                </td>
                            </tr> --}}
                            <tr>
                                <td>
                                    Active Users
                                    <small>
                                        (last 30 Days)
                                    </small>
                                </td>
                                <td class="dash-info-count text-end" data-active-users="">
                                    0
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Total Users
                                </td>
                                <td class="dash-info-count text-end" data-total-users="">
                                    0%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- progress -->
                    <div class="progress-wrap">
                        <div class="progressbar">
                            <div class="per" data-active-percent="">
                            </div>
                        </div>
                    </div>
                    <!-- ./progress -->
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>

    </div>
    @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->allow_app == 1))
    <div class="row" id="meditationhours">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-12 col-sm">
                            <h3 class="card-title">
                                Meditation Hours
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
        </div>
    </div>
    @endif
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
                        @include('newDashboard.partials.month-range-picker', ['tier' => 4, 'parentId' => 'daterangeTopMeditationTracks', 'fromId' => 'daterangeTopMeditationTracksFrom', 'toId' => 'daterangeTopMeditationTracksFromTo'])
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
    <div class="row d-flex">
        <div class="col-md-8 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Popular Feed Categories
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Popular feed categories based on the views.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="text-end">
                        <ul class="week-month-year-list justify-content-end" data-popular-feeds-from-duration="">
                            <li class="active" data-popular-feeds-duration="7">
                                Week
                            </li>
                            <li data-popular-feeds-duration="30">
                                Month
                            </li>
                            <li data-popular-feeds-duration="365">
                                Year
                            </li>
                        </ul>
                    </div>
                    <div class="popular-exercises-chart-area">
                        <canvas height="110" id="popularFeedCategories" width="300">
                        </canvas>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Recipe Views
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Top 5 recipes based on views.">
                        </i>
                    </h3>
                </div>
                <div class="card-body pt-1" data-recipe-block="">
                        {{-- <div class="superstars-box-title active-team">
                            <span class="me-2">
                                Recipe views
                            </span>
                            <i class="fas fa-info-circle ms-auto info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Top 5 recipes based on views.">
                            </i>
                        </div> --}}
                        <div class="superstars-box">
                        <div class="superstars-box-img" data-recipe-logo="">
                            <img alt="icon" class="" src="{{asset('assets/dist/img/user1-128x128.jpg')}}"/>
                        </div>
                        <div class="superstars-box-textarea">
                            <h4 class="sx-name" data-recipe-name="">
                            </h4>
                            <small data-recipe-cook="">
                            </small>
                            <div class="sx-count" data-recipe-views="">
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
    </div>
    <!-- Steps Period -->
    <!-- Webinar Graph / Top 10 Webinar Graph -->
    <div class="row d-flex">
        <div class="col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Popular Webinar Categories
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Popular Webinar categories based on the views.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="text-end">
                        <ul class="week-month-year-list justify-content-end" data-popular-webinar-from-duration="">
                            <li class="active" data-popular-webinar-duration="7">
                                Week
                            </li>
                            <li data-popular-webinar-duration="30">
                                Month
                            </li>
                            <li data-popular-webinar-duration="365">
                                Year
                            </li>
                        </ul>
                    </div>
                    <div class="popular-exercises-chart-area">
                        <canvas height="110" id="popularWebinarCategories" width="300">
                        </canvas>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <!-- card -->
            <div class="card h-100 m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Top 10 Webinar
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Top 10 Webinar tracks based on the views.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body">
                    <div class="d-sm-flex justify-content-end">
                        @include('newDashboard.partials.month-range-picker', ['tier' => 4, 'parentId' => 'daterangeTopWebinarTracks', 'fromId' => 'daterangeTopWebinarTracksFrom', 'toId' => 'daterangeTopWebinarTracksFromTo'])
                    </div>
                    <div class="">
                        <canvas height="300" id="chartTopWebinar" width="300">
                        </canvas>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>
    </div>
    <!-- ./Webinar Graph / Top 10 Webinar Graph -->
    <!-- Masterclass / Top 10 Masterclass Graph -->
    <div class="row d-flex">
        <div class="col-md-6 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Popular Masterclass Categories
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Popular Masterclass categories based on the enrollment.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="text-end">
                        <ul class="week-month-year-list justify-content-end" data-popular-masterclass-from-duration="">
                            <li class="active" data-popular-masterclass-duration="7">
                                Week
                            </li>
                            <li data-popular-masterclass-duration="30">
                                Month
                            </li>
                            <li data-popular-masterclass-duration="365">
                                Year
                            </li>
                        </ul>
                    </div>
                    <div class="popular-exercises-chart-area">
                        <canvas height="110" id="popularMasterclassCategories" width="300">
                        </canvas>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <!-- card -->
            <div class="card h-100 m-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Top 10 Masterclass
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Top 10 Masterclass based on the enrollment.">
                        </i>
                    </h3>
                </div>
                <!-- /.card-header -->
                <!-- card-body -->
                <div class="card-body">
                    <div class="text-end">
                        <ul class="week-month-year-list justify-content-end" data-top-masterclass-from-duration="">
                            <li class="active" data-top-masterclass-duration="">
                                All
                            </li>
                            <li data-top-masterclass-duration="7">
                                Week
                            </li>
                            <li data-top-masterclass-duration="30">
                                Month
                            </li>
                            <li data-top-masterclass-duration="365">
                                Year
                            </li>
                        </ul>
                    </div>
                    <div class="popular-exercises-chart-area">
                        <canvas height="300" id="chartTopMasterclass" width="300">
                        </canvas>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- ./card -->
        </div>
    </div>
    <!-- /Masterclass / Top 10 Masterclass Graph -->
    {{--
    <div class="row">
        <div class="col-lg-6 mb-3">
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
                <div class="card-body">
                    <div class="d-sm-flex justify-content-end">
                        <div class="d-sm-flex justify-content-end">
                            @include('newDashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeStepsPeriod', 'fromId' => 'daterangeStepsPeriodFrom', 'toId' => 'daterangeStepsPeriodFromTo'])
                        </div>
                    </div>
                    <div class="exercise-hours-chart-area">
                        <canvas height="250" id="chartStepsPeriod" width="400">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
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
                <div class="card-body">
                    <div class="d-sm-flex justify-content-end">
                        @include('newDashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeCaloriesPeriod', 'fromId' => 'daterangeCaloriesPeriodFrom', 'toId' => 'daterangeCaloriesPeriodFromTo'])
                    </div>
                    <div class="exercise-hours-chart-area">
                        <canvas height="250" id="chartCaloriesPeriods" width="400">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    --}}
    <!-- ./Steps Period -->
    {{--
    <div class="row d-flex">
        <div class="col-md-8 mb-3">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h3 class="card-title d-flex align-items-center">
                        <span>
                            Popular Feed Categories
                        </span>
                        <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Popular feed categories based on the views.">
                        </i>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-end">
                        <ul class="week-month-year-list justify-content-end" data-popular-feeds-from-duration="">
                            <li class="active" data-popular-feeds-duration="7">
                                Week
                            </li>
                            <li data-popular-feeds-duration="30">
                                Month
                            </li>
                            <li data-popular-feeds-duration="365">
                                Year
                            </li>
                        </ul>
                    </div>
                    <div class="popular-exercises-chart-area">
                        <canvas height="110" id="popularFeedCategories" width="300">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
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
            </div>
        </div>
    </div>
    --}}
    <!-- Exercise hours -->
    {{--
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
    </div>
    --}}
</div>
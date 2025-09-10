<div class="row">
    <div class="col-xl-4">
        <div class="card dashboard-card has-bg">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-5 align-self-center col-auto mb-4">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Users
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        <div class="dashboard-card-filter">
                            <div>
                                <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="#" title="Registered - The total registered system users. Active (last 30 Days) - Those users who have logged in over the past 30 days from the current date.">
                                    <i class="fal fa-info-circle" data-placement="bottom" data-bs-toggle="tooltip">
                                    </i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-user-block="">
                    <div class="info-block mw-100">
                        <span>
                            Registered
                        </span>
                        <span class="ms-4 info-block-value" data-total-users="">
                        </span>
                    </div>
                    <div class="info-block mw-100">
                        <span>
                            Active (last 30 Days)
                        </span>
                        <span class="ms-4 info-block-value" data-active-users="">
                        </span>
                    </div>
                    @if($role->group == 'zevo')
                    <div class="info-block mw-100">
                        <span>
                            Active (last 7 Days)
                        </span>
                        <span class="ms-4 info-block-value" data-active-last-seven-days-users="">
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        @if($usageTabVisibility)
        <div class="card dashboard-card has-bg">
            <div class="card-body" id="meditationhours">
                <div class="row mb-4">
                    <div class="col-md-7 align-self-center">
                        <h3 class="card-inner-title border-0 pb-0 mb-0">
                            Meditation Minutes
                            <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="{{ trans('dashboard.usage.tooltips.meditation_minutes') }}">
                            </i>
                        </h3>
                    </div>
                    <div class="col-md-5">
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
                <div class="row">
                    <div class="col-xxl-8 border-xxl-right">
                        <div class="chart-height">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="chartMeditationHours">
                                </canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-4 mt-4 mt-xxxl-0">
                        <div class="row" data-meditiation-hours-block="">
                            <div class="col-xxl-12 col-xl-6 align-self-center">
                                <div class="info-block no-border">
                                    <span>
                                        Total Users
                                    </span>
                                    <span class="ms-4 info-block-value" data-meditation-hours-total-users="">
                                    </span>
                                </div>
                            </div>
                            <div class="col-xxl-12 col-xl-6 align-self-center">
                                <div class="info-block no-border">
                                    <span>
                                        Avg Meditation Time
                                    </span>
                                    <span class="ms-4 info-block-value" data-meditation-hours-avg-hours="">
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@if($usageTabVisibility)
<div class="mt-4 mt-xl-5">
    <div class="card dashboard-card">
        <div class="card-body">
            <h3 class="card-inner-title border-0 pb-0 mb-4">
                Meditation
            </h3>
            <div class="position-relative">
                <ul class="nav nav-tabs tabs-line-style" id="meditationTabs" role="tablist">
                    <li class="nav-item">
                        <a aria-controls="meditationTop10" aria-selected="false" class="nav-link active" data-bs-toggle="tab" href="#meditationTop10" id="meditationTop10-tab" role="tab">
                            Top 10
                            <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="Top 10 meditation tracks based on views.">
                            </i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a aria-controls="meditationCategories" aria-selected="true" class="nav-link" data-bs-toggle="tab" href="#meditationCategories" id="meditationCategories-tab" role="tab">
                            Popular Categories
                            <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="Popular meditation categories based on views.">
                            </i>
                        </a>
                    </li>
                    
                </ul>
            </div>
            <div class="tab-content" id="meditationTabsContent">
                <div aria-labelledby="meditationCategories-tab" class="tab-pane fade" id="meditationCategories" role="tabpanel">
                    <ul class="week-month-year-list justify-content-end" data-popular-meditation-from-duration="">
                        <li class="active" data-popular-meditation-duration="7">
                            Week
                        </li>
                        <li data-popular-meditation-duration="30">
                            Month
                        </li>
                        <li data-popular-meditation-duration="365">
                            Year
                        </li>
                    </ul>
                    <div class="chart-height">
                        <div class="canvas-wrap">
                            <canvas class="canvas" id="chartPopularMeditationCategory">
                            </canvas>
                        </div>
                    </div>
                </div>
                <div aria-labelledby="meditationTop10-tab" class="tab-pane fade show active" id="meditationTop10" role="tabpanel">
                    @include('dashboard.partials.month-range-picker', ['tier' => 2, 'parentId' => 'daterangeTopMeditationTracks', 'fromId' => 'daterangeTopMeditationTracksFrom', 'toId' => 'daterangeTopMeditationTracksFromTo'])
                    <div class="chart-height">
                        <div class="canvas-wrap">
                            <canvas class="canvas" id="chartTopTrack">
                            </canvas>
                        </div>
                    </div>
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
                            Top 5 Recipes
                        </h3>
                    </div>
                    <div class="col-md-7 col-auto mb-4 mw-100 align-self-center">
                        <div class="dashboard-card-filter">
                            <div>
                                <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="#" title="Top 5 recipes based on views.">
                                    <i class="fal fa-info-circle" data-placement="bottom" data-bs-toggle="tooltip">
                                    </i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dashboard-table-height">
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>
                                        Recipe Name
                                    </th>
                                    <th class="text-nowrap">
                                        No. of Views
                                    </th>
                                </tr>
                            </thead>
                            <tbody data-recipe-block="">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card dashboard-card">
            <div class="card-body">
                <h3 class="card-inner-title border-0 pb-0 mb-4">
                    Webinar
                </h3>
                <div class="position-relative">
                    <ul class="nav nav-tabs tabs-line-style" id="meditationTabs" role="tablist">
                        <li class="nav-item">
                            <a aria-controls="webinarTop10" aria-selected="false" class="nav-link active" data-bs-toggle="tab" href="#webinarTop10" id="webinarTop10-tab" role="tab">
                                Top 10
                                <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="Top 10 Webinar tracks based on views.">
                                </i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a aria-controls="webinarCategories" aria-selected="true" class="nav-link" data-bs-toggle="tab" href="#webinarCategories" id="webinarCategories-tab" role="tab">
                                Popular Categories
                                <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="Popular Webinar categories based on views.">
                                </i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="WebinarTabsContent">
                    <div aria-labelledby="webinarCategories-tab" class="tab-pane fade" id="webinarCategories" role="tabpanel">
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
                        <div class="chart-height">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="popularWebinarCategories">
                                </canvas>
                            </div>
                        </div>
                    </div>
                    <div aria-labelledby="webinarTop10-tab" class="tab-pane fade show active" id="webinarTop10" role="tabpanel">
                        @include('dashboard.partials.month-range-picker', ['tier' => 3, 'parentId' => 'daterangeTopWebinarTracks', 'fromId' => 'daterangeTopWebinarTracksFrom', 'toId' => 'daterangeTopWebinarTracksFromTo'])
                        <div class="chart-height">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="chartTopWebinar">
                                </canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4 mt-xl-5">
    <div class="col-xl-6">
        <div class="card dashboard-card">
            <div class="card-body">
                <h3 class="card-inner-title border-0 pb-0 mb-4">
                    Masterclass
                </h3>
                <div class="position-relative">
                    <ul class="nav nav-tabs tabs-line-style" id="meditationTabs" role="tablist">
                        <li class="nav-item">
                            <a aria-controls="masterclassTop10" aria-selected="false" class="nav-link active" data-bs-toggle="tab" href="#masterclassTop10" id="masterclassTop10-tab" role="tab">
                                Top 10
                                <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="Top 10 Masterclasses based on enrollment.">
                                </i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a aria-controls="masterclassCategories" aria-selected="true" class="nav-link" data-bs-toggle="tab" href="#masterclassCategories" id="masterclassCategories-tab" role="tab">
                                Popular Categories
                                <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="Popular Masterclass categories based on enrollment.">
                                </i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="masterclassTabContent">
                    <div aria-labelledby="masterclassCategories-tab" class="tab-pane fade" id="masterclassCategories" role="tabpanel">
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
                        <div class="chart-height">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="popularMasterclassCategories">
                                </canvas>
                            </div>
                        </div>
                    </div>
                    <div aria-labelledby="masterclassTop10-tab" class="tab-pane fade show active" id="masterclassTop10" role="tabpanel">
                        @include('dashboard.partials.month-range-picker', ['tier' => 4, 'parentId' => 'daterangeTopMasterclass', 'fromId' => 'daterangeTopMasterclassFrom', 'toId' => 'daterangeTopMasterclassFromTo'])
                        <div class="chart-height">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="chartTopMasterclass">
                                </canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card dashboard-card">
            <div class="card-body">
                <h3 class="card-inner-title border-0 pb-0 mb-4">
                    Stories
                </h3>
                <div class="position-relative">
                    <ul class="nav nav-tabs tabs-line-style" id="meditationTabs" role="tablist">
                        <li class="nav-item">
                            <a aria-controls="feedTop10" aria-selected="false" class="nav-link active" data-bs-toggle="tab" href="#feedTop10" id="feedTop10-tab" role="tab">
                                Top 10
                                <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="Top 10 feeds based on views.">
                                </i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a aria-controls="feedCategories" aria-selected="true" class="nav-link" data-bs-toggle="tab" href="#feedCategories" id="feedCategories-tab" role="tab">
                                Popular Categories
                                <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="Popular feed categories based on views.">
                                </i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="feedTabContent">
                    <div aria-labelledby="feedCategories-tab" class="tab-pane fade" id="feedCategories" role="tabpanel">
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
                        <div class="chart-height">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="popularFeedCategories">
                                </canvas>
                            </div>
                        </div>
                    </div>
                    <div aria-labelledby="feedTop10-tab" class="tab-pane fade show active" id="feedTop10" role="tabpanel">
                        @include('dashboard.partials.month-range-picker', ['tier' => 4, 'parentId' => 'daterangeTopFeeds', 'fromId' => 'daterangeTopFeedsFrom', 'toId' => 'daterangeTopFeedsFromTo'])
                        <div class="chart-height">
                            <div class="canvas-wrap">
                                <canvas class="canvas" id="FeedTop10Chart">
                                </canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
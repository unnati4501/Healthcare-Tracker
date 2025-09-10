<div class="card dashboard-card mt-5">
    <div class="card-body pb-2">
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="booking-stats-card stats-card-primary">
                    <div>
                        <p class="booking-stats-title">
                            {{trans('dashboard.booking.today')}}
                        </p>
                        <span id="today-events">
                            0
                        </span>
                    </div>
                    <div>
                        <span class="booking-stats-icon">
                            <i class="fal fa-calendar-day"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="booking-stats-card stats-card-yellow">
                    <div>
                        <p class="booking-stats-title">
                            {{trans('dashboard.booking.upcoming_events')}}
                        </p>
                        <span id="upcoming-events">
                            0
                        </span>
                    </div>
                    <div>
                        <span class="booking-stats-icon">
                            <i class="fal fa-calendar-week">
                            </i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="booking-stats-card stats-card-orange">
                    <div>
                        <p class="booking-stats-title">
                            {{trans('dashboard.booking.completed')}}
                        </p>
                        <span id="completed-events">
                            0
                        </span>
                    </div>
                    <div>
                        <span class="booking-stats-icon">
                            <i class="fal fa-calendar-check"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="booking-stats-card stats-card-green">
                    <div>
                        <p class="booking-stats-title">
                            {{trans('dashboard.booking.cancelled')}}
                        </p>
                        <span id="cancelled-events">
                            0
                        </span>
                    </div>
                    <div>
                        <span class="booking-stats-icon">
                            <i class="fal fa-calendar-times"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card dashboard-card mt-xl-5">
    <div class="card-body">
        <h3 class="card-inner-title border-0 pb-0 mb-4">{{trans('dashboard.booking.issue_trend')}}</h3>

        <div class="chart-height">
            <div class="canvas-wrap">
                <canvas id="eventSkillTrendChart" class="canvas"></canvas>
            </div>
        </div>
    </div>
</div>
@if($role->group == 'zevo' && $role->slug != 'wellbeing_specialist')
<div class="mt-4 mt-xl-5" id="todays-event-calender-parent">
    <div class="d-flex">
        <h5 class="card-inner-title border-0 pb-0 mb-4">
            {{trans('dashboard.booking.todays_event_calendar')}}
        </h5>
        <a class="ms-auto" href="{{ route('admin.bookings.index') }}">
            {{trans('dashboard.booking.view_more')}}
        </a>
    </div>
    <div class="slider-style-1 mb-4 mt-0">
        <div class="owl-carousel owl-theme" id="eventCalendarCarousel">
        </div>
    </div>
</div>
<script id="today-event-calender-list-template" type="text/html">
@include('dashboard.partials.today-event-calender-list', [
    'day' => ':day',
    'eventName' => ':eventName',
    'companyName' => ':companyName',
    'roleName' => $childResellerType,
    'bookingDate' => ':bookingDate',
    'bookingStarttime' => ':bookingStarttime',
    'participantsUsers' => ':participantsUsers',
    'eventImageName' => ':eventImageName'
])
</script>
@endif
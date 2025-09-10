<style type="text/css">
    .dayfilter{
    width: 70%;
    float: right;
}
</style>
<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title d-flex align-items-center">
                <span>
                    Upcoming Events
                </span>
            </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="booking-stats-card">
                        <div>
                            <p class="booking-stats-title">
                                Total
                            </p>
                            <span id="total-upcoming-events">
                                0
                            </span>
                        </div>
                        <div>
                            <span class="booking-stats-icon">
                                <i class="fal fa-calendar-check">
                                </i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="booking-stats-card">
                        <div>
                            <p class="booking-stats-title">
                                Today
                            </p>
                            <span id="today-upcoming-events">
                                0
                            </span>
                        </div>
                        <div>
                            <span class="booking-stats-icon bg-warning">
                                <i class="fal fa-calendar-day">
                                </i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="booking-stats-card">
                        <div>
                            <p class="booking-stats-title">
                                7 Days
                            </p>
                            <span id="sevendays-upcoming-events">
                                0
                            </span>
                        </div>
                        <div>
                            <span class="booking-stats-icon orange">
                                <i class="fal fa-calendar-week">
                                </i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="booking-stats-card">
                        <div>
                            <p class="booking-stats-title">
                                30 Days
                            </p>
                            <span id="thirtydays-upcoming-events">
                                0
                            </span>
                        </div>
                        <div>
                            <span class="booking-stats-icon green">
                                <i class="fal fa-calendar-alt">
                                </i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
                <h3 class="card-title mb-4 d-flex">
                    Events Revenue
                    <div class="col-md-2 ms-auto">
                        <select class="form-control dayfilter" id="dayFilter" name="dayFilter">
                            <option value="7">
                                7 Days
                            </option>
                            <option value="30">
                                30 Days
                            </option>
                            <option value="90">
                                90 Days
                            </option>
                        </select>
                    </div>
                </h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="event-revenue-card">
                            <div>
                                <p class="event-revenue-title">
                                    Completed
                                </p>
                                <span class="event-revenue-badge" id="count-completed-event-revenue">
                                    <i class="fal fa-calendar-check me-2">
                                    </i>
                                    0
                                </span>
                                @if ($childResellerType == null)
                                <span class="text-primary event-revenue-value" id="total-completed-event-revenue">
                                    0
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="event-revenue-card">
                            <div>
                                <p class="event-revenue-title">
                                    Booked
                                </p>
                                <span class="event-revenue-badge" id="count-booked-event-revenue">
                                    <i class="fal fa-calendar-minus me-2">
                                    </i>
                                    0
                                </span>
                                @if ($childResellerType == null)
                                <span class="text-primary event-revenue-value" id="total-booked-event-revenue">
                                    0
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="event-revenue-card">
                            <div>
                                <p class="event-revenue-title">
                                    Cancelled
                                </p>
                                <span class="event-revenue-badge" id="count-cancelled-event-revenue">
                                    <i class="fal fa-calendar-times me-2">
                                    </i>
                                    0
                                </span>
                                @if ($childResellerType == null)
                                <span class="text-primary event-revenue-value" id="total-cancelled-event-revenue">
                                    0
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </hr>
        </div>
        <!-- /.card-body -->
    </div>
    <div class="card" id="todays-event-calender-parent">
        <div class="card-header d-flex">
            <h3 class="card-title d-flex align-items-center text-start">
                <span>
                    Today's Event calendar
                </span>
            </h3>
            <a class="ms-auto" href="{{ route('admin.marketplace.index','#booked-tab') }}">
                View More
            </a>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="slider-style-1 mb-4 mt-4">
                <div class="owl-carousel owl-theme" id="todays-event-calender-list">
                </div>
            </div>
        </div>
        <!-- /.card-body -->
    </div>
</div>
<script id="today-event-calender-list-template" type="text/html">
    @include('newDashboard.partials.today-event-calender-list', [
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
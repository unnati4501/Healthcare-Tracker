<div class="card dashboard-card mt-5">
    <div class="card-body pb-2">
        <!-- <h5 class="card-inner-title border-0 pb-0 mb-4">Upcoming Events</h5> -->
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="booking-stats-card stats-card-primary">
                    <div>
                        <p class="booking-stats-title">{{trans('dashboard.eapactivity.headings.todays')}} <br/>{{trans('dashboard.eapactivity.headings.sessions')}}</p>
                        <span id="today-sessions">0</span>
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
                        <p class="booking-stats-title">{{trans('dashboard.eapactivity.headings.upcoming_sessions')}}</p>
                        <span id="upcoming-sessions">0</span>
                    </div>
                    <div>
                        <span class="booking-stats-icon">
                <i class="fal fa-calendar-week"></i>
             </span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="booking-stats-card stats-card-orange">
                    <div>
                        <p class="booking-stats-title">{{trans('dashboard.eapactivity.headings.completed_sessions')}}</p>
                        <span id="completed-sessions">0</span>
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
                        <p class="booking-stats-title">{{trans('dashboard.eapactivity.headings.cancelled_sessions')}}</p>
                        <span id="cancelled-sessions">0</span>
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
        <div class="row mb-4">
            <div class="col-md-5 align-self-center">
                <h3 class="card-inner-title border-0 pb-0 mb-0">{{trans('dashboard.eapactivity.headings.appointment_trend')}}</h3>
            </div>
        </div>
        <div class="chart-height">
            <div class="canvas-wrap">
                <canvas id="appointmentTrend" class="canvas"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="card dashboard-card mt-xl-5">
    <div class="card-body">
        <h3 class="card-inner-title border-0 pb-0 mb-4">{{trans('dashboard.eapactivity.headings.skill_trend')}}</h3>

        <div class="chart-height">
            <div class="canvas-wrap">
                <canvas id="SkillTrendChart" class="canvas"></canvas>
            </div>
        </div>
    </div>
</div>
@if($role->slug != 'counsellor')
<div class="row mt-xl-5">
    <div class="col-xl-4">
        <div class="card dashboard-card has-bg">
            <div class="card-body">
                <h3 class="card-inner-title border-0 pb-0 mb-4">{{trans('dashboard.eapactivity.headings.therapist')}}</h3>
                <div class="info-block mw-100">
                    <span>{{trans('dashboard.eapactivity.headings.total_therapist')}}</span>
                    <span class="ms-4 info-block-value" id="total-counsellors">0</span>
                </div>
                <div class="info-block mw-100">
                    <span>{{trans('dashboard.eapactivity.headings.active_therapist')}}</span>
                    <span class="ms-4 info-block-value" id="active-counsellors">0</span>
                </div>

            </div>

        </div>
    </div>
    <div class="col-xl-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h3 class="card-inner-title border-0 ">{{trans('dashboard.eapactivity.headings.utilization')}}</h3>
                <div class="chart-height donut-chart-height">
                    <div class="canvas-wrap">
                        <canvas id="UtilisationChart1" class="canvas"></canvas>
                    </div>
                </div>
                <div id="UtilisationChart1-legend" class="chart-legend">
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h3 class="card-inner-title border-0 ">{{trans('dashboard.eapactivity.headings.referral_rate')}}</h3>
                <div class="chart-height donut-chart-height">
                    <div class="canvas-wrap">
                        <canvas id="UtilisationChart2" class="canvas"></canvas>
                    </div>
                </div>
                <div id="UtilisationChart2-legend" class="chart-legend">
                </div>
            </div>
        </div>
    </div>
</div>
@endif
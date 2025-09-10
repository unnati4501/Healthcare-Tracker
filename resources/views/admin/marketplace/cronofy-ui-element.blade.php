@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.marketplace.cronofy-breadcrumb', [
  'mainTitle' => trans('marketplace.title.booking'),
  'breadcrumb' => Breadcrumbs::render('marketplace.book_session.index'),
  'eventId' => $event_id,
  'reschedule' => $reschedule,
  'eventbooking_id' => isset($eventbooking_id) ? $eventbooking_id : 0,
  'eventbookinglogsId' => isset($eventbookinglogsId) ? $eventbookinglogsId : 0
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            <div class="card-body">
                <div class="stepwizard-wrapper vertical-stepwizard">
                    <div class="stepwizard align-self-center">
                        <div class="stepwizard-panel">
                            <div class="wizard flex-grow-1">
                                <div class="wizard-inner flex-grow-1">
                                    <ul class="nav nav-tabs mw-100" role="tablist">
                                        <li class="completed event-details-content disabled">
                                            <a href="#step-1" data-bs-toggle="tab" role="tab" aria-expanded="true" class="active show" aria-selected="true"><span class="round-tab"></span> <i>Event Details</i></a>
                                        </li>
                                        <li class="completed registration-details-content disabled">
                                            <a href="#step-2" data-bs-toggle="tab" role="tab" aria-expanded="false" class="" aria-selected="false"><span class="round-tab"></span> <i>Registration Details</i></a>
                                        </li>
                                        <li class="active event-booked-content">
                                            <a href="#step-3" data-bs-toggle="tab" role="tab" aria-expanded="false" class="" aria-selected="false"><span class="round-tab"></span> <i>Date/Time</i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="stepwizard-content-wrapper">
                		    <div id="cronofy-date-time-picker">
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
<script src="https://elements.cronofy.com/js/CronofyElements.v1.54.5.js">
</script>
<script type="text/javascript">
    const ajaxUrl = {
        createEventSlot: '{{ route("admin.marketplace.create-event-slot", [$event_id]) }}',
        cronofyException: '{{ route("admin.cronofy.sessions.cronofy-exception") }}',
    },
    url = {
        cronofyIndex: `{{ route('admin.bookings.index') }}`,
    },
    data = {
        token: "{{ $token }}",
        dataCenter: "{{ env('CRONOFY_DATA_CENTER') }}",
        availableRuleId: `{{ config('cronofy.availableRuleId') }}`,
        eventId: `{{ $event_id }}`,
        wsId: `{{ $wc_id }}`,
        scheduleId: `{{ $scheduling_id }}`,
        name: `{{ $name }}`,
        companyId: `{{ $company_id }}`,
        timezone: `{{ $timezone }}`,
        subId: `{{ $subId }}`,
        duration: `{{ $duration }}`,
        eventbooking_id: `{{ $eventbooking_id }}`,
        reschedule: `{{ $reschedule }}`,
        eventbookinglogsId: `{{ isset($eventbookinglogsId) ? $eventbookinglogsId : 0 }}`,
        queryPeriods: <?php echo json_encode($queryPeriod); ?>,
        payload: <?php echo json_encode($payload); ?>,
        availableColor: `{{ config('cronofy.colors.available') }}`,
        unavailableColor: `{{ config('cronofy.colors.unavailable') }}`,
        schedulingId: `{{ $scheduling_id }}`,
        buttonActive: `{{ config('cronofy.colors.buttonActive') }}`,
        buttonTextHover: `{{ config('cronofy.colors.buttonTextHover') }}`,
        buttonHover: `{{ config('cronofy.colors.buttonHover') }}`,
        buttonText: `{{ config('cronofy.colors.buttonText') }}`,
        buttonConfirm: `{{ config('cronofy.colors.buttonConfirm') }}`,
        buttonActiveText: `{{ config('cronofy.colors.buttonActiveText') }}`,
    },
    errormessage = {
        uielementnotfound: `{!! trans('marketplace.messages.uielementnotfound') !!}`
    };
</script>
<script src="{{ mix('js/marketplace/uielement.js') }}">
</script>
@endsection

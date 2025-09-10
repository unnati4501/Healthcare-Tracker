@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.groupsession.breadcrumb', [
  'mainTitle' => trans('calendly.title.add_session'),
  'breadcrumb' => 'cronofy.groupsession.addsession',
  'book' => false,
  'back' => true,
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
    		    <div id="cronofy-date-time-picker">
    		    </div>
            </div>
        </div>
	</div>
</section>
<div class="modal fade has-solid-bg" data-backdrop="static" data-keyboard="false" id="availibility-error-popup" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body position-relative text-center">
                <a class="close" type="button" href="{{route('admin.cronofy.sessions.index')}}">
                    <i class="fal fa-times"></i>
                </a>
                <img class="not-found-img" src="{{ config('zevolifesettings.fallback_image_url.cronofy-availability') }}" alt="image"/>
                <p><br/></p>
                <h3>Oops..!</h3>
                <h6 class="mb-4">We apologise for the inconvenience as we are facing some technical problem, please try again in some time. Please let us know at <a href="mailto:support@zevohealth.zendesk.com">support@zevohealth.zendesk.com</a> if you have any concerns. </h5>
                <a class="btn btn-primary" type="button" href="{{route('admin.cronofy.sessions.index')}}">
                    {{ trans('buttons.general.okay') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script src="https://elements.cronofy.com/js/CronofyElements.v1.54.5.js">
</script>
<script type="text/javascript">
    const ajaxUrl = {
        createEventSlot: '{{ route("admin.cronofy.sessions.create-event-slot") }}',
        cronofyException: '{{ route("admin.cronofy.sessions.cronofy-exception") }}',
    },
    url = {
        cronofyIndex: `{{ route('admin.cronofy.sessions.index') }}`,
    },
    data = {
        token: "{{ $token }}",
        dataCenter: "{{ env('CRONOFY_DATA_CENTER') }}",
        availableRuleId: `{{ config('cronofy.availableRuleId') }}`,
        eventId: `{{ $event_id }}`,
        wsId: `{{ $ws_id }}`,
        scheduleId: `{{ $scheduleId }}`,
        name: `{{ $name }}`,
        companyId: `{{ $company_id }}`,
        timezone: `{{ $timezone }}`,
        subId: `{{ $subId }}`,
        duration: `{{ $duration }}`,
        reschedule: `{{ $reschedule }}`,
        queryPeriods: <?php echo json_encode($queryPeriod); ?>,
        availableColor: `{{ config('cronofy.colors.available') }}`,
        unavailableColor: `{{ config('cronofy.colors.unavailable') }}`,
        schedulingId: `{{ $scheduling_id }}`,
        buttonActive: `{{ config('cronofy.colors.buttonActive') }}`,
        buttonTextHover: `{{ config('cronofy.colors.buttonTextHover') }}`,
        buttonHover: `{{ config('cronofy.colors.buttonHover') }}`,
        buttonText: `{{ config('cronofy.colors.buttonText') }}`,
        buttonConfirm: `{{ config('cronofy.colors.buttonConfirm') }}`,
        buttonActiveText: `{{ config('cronofy.colors.buttonActiveText') }}`,
        startInterval: `{{ config('zevolifesettings.start_interval') }}`,
    },
    errormessage = {
        uielementnotfound: `{!! trans('Cronofy.session_details.messages.uielementnotfound') !!}`
    };
</script>
<script src="{{ mix('js/cronofy/groupsession/uielementslot.js') }}">
</script>
@endsection

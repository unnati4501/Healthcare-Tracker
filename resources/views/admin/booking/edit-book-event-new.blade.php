@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    .datetimepicker{ padding: 4px !important; }
    .datetimepicker .table-condensed th,.datetimepicker .table-condensed td { padding: 4px 5px; }
    .presenter-avatar { height: 50px; width: 50px !important; display: inline-block !important; border-radius: 50%; margin-right: 20px; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.booking.breadcrumb', [
    'mainTitle' => trans('marketplace.booking_details.buttons.edit_event'),
    'breadcrumb' => Breadcrumbs::render('booking.book_event.edit', $bookingLogId),
    'backToBookingTab' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Nav tabs -->
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.bookings.edit-booked-event', $bookingLogId, $eventBookingLogsTemp->id], 'class' => 'form-horizontal book_event_from_submit', 'method' => 'POST', 'role' => 'form', 'id' => 'bookEvent']) }}
            <div class="card-body">
            <div class="stepwizard-wrapper vertical-stepwizard">
                <div class="stepwizard align-self-center">
                    <div class="stepwizard-panel">
                        <div class="wizard flex-grow-1">
                            <div class="wizard-inner flex-grow-1">
                                <ul class="nav nav-tabs mw-100" role="tablist">
                                    <li class="active event-details-content disabled">
                                        <a href="#step-1" data-bs-toggle="tab" role="tab" aria-expanded="true" class="active show" aria-selected="true"><span class="round-tab"></span> <i>Event Details</i></a>
                                    </li>
                                    <li class="registration-details-content disabled">
                                        <a href="#step-2" data-bs-toggle="tab" role="tab" aria-expanded="false" class="" aria-selected="false"><span class="round-tab"></span> <i>Registration Details</i></a>
                                    </li>
                                    <li class="event-booked-content disabled">
                                        <a href="#step-3" data-bs-toggle="tab" role="tab" aria-expanded="false" class="" aria-selected="false"><span class="round-tab"></span> <i>Date/Time</i></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="stepwizard-content-wrapper">
                        <div class="tab-content" id="bookEventAddSteps">
                            <h3 style="display: none">Step 1</h3> 
                            <div class="tab-pane active booking-steps event-details-content" role="tabpanel" id="step-1" data-step="0">
                                <div class="card-inner">
                                    <div class="row justify-content-center justify-content-md-start">
                                        <div class="col-auto basic-file-upload">
                                            <div class="edit-profile-wrapper">
                                                <div class="profile-image user-img edit-photo">
                                                    <img src="{{ $event->logo }}"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-9 col-md-8">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label(null, trans('marketplace.book_event.form.labels.event_name')) }}
                                                        {{ Form::text(null, $event->name, ['disabled' => true, 'class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label(null, trans('marketplace.book_event.form.labels.duration')) }}
                                                        {{ Form::text(null, convertToHoursMins(timeToDecimal($event->duration), false, '%s %s'), ['disabled' => true, 'class' => 'form-control', 'data-duration']) }}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {{ Form::label(null, trans('marketplace.book_event.form.labels.location')) }}
                                                        {{ Form::text(null, $locationTypes[$event->location_type], ['disabled' => true, 'class' => 'form-control']) }}
                                                    </div>
                                                </div>
                                                <div class="col-lg-6" data-capacity="{{ $bookingLog->capacity_log }}" data-capacity-block="" data-totalusers="0">
                                                    <div class="form-group">
                                                        {{ Form::label(null, trans('marketplace.book_event.form.labels.capacity')) }}
                                                        {{ Form::text('capacity', $bookingLog->capacity_log, ['disabled' => true, 'class' => 'form-control', 'id' => 'capacity']) }}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        {{ Form::label('company', trans('marketplace.book_event.form.labels.company')) }}
                                                        @if($disableCompany)
                                                        {{ Form::select('company', $companies, $company->id, ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => 'Select company', 'disabled' => true, 'data-placeholder' => 'Select company', 'data-disabled' => 'readonly'], $companiesAttr) }}
                                                        @else
                                                        {{ Form::select('company', $companies, null, ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => 'Select company', 'data-placeholder' => 'Select company', 'data-disabled' => 'readonly'], $companiesAttr) }}
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner">
                                    <h3 class="card-inner-title">{{ trans('marketplace.book_event.form.labels.description') }}</h3>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                {{ Form::textarea('description', old('description', (isset($bookingLog->description) ? htmlspecialchars_decode($bookingLog->description) : null)), ['class' => 'form-control article-ckeditor', 'id' => 'description', 'data-errplaceholder' => '#description-error-cstm', 'data-formid' => "bookEvent", 'data-upload-path' => route('admin.ckeditor-upload.feed-description', ['_token' => csrf_token() ])]) }}
                                                <span id="description-required-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">The Description field is Required</span>
                                                <span id="description-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">The maximum capacity can be 2500</span>
                                                <span id="description-format-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">Please enter valid description</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <h3 style="display: none">Step 2</h3> 
                            <div class="tab-pane booking-steps registration-details-content" role="tabpanel" id="step-2" data-step="1">
                                <div class="card-inner">
                                    <h3 class="card-inner-title">
                                        {{ trans('marketplace.book_event.form.labels.additional_notes') }}
                                    </h3>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                {{ Form::textarea('notes', old('notes', (isset($bookingLog->notes) ? htmlspecialchars_decode($bookingLog->notes) : null)), ['id' => 'notes', 'rows' => 3, 'class' => 'form-control basic-format-ckeditor ignore-validation', 'placeholder' => 'Enter additional notes']) }}
                                                <span id="notes-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">The notes field may not be greater than 500 characters.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner">
                                    <h3 class="card-inner-title">
                                        {{ trans('marketplace.book_event.form.labels.email_notes') }}
                                    </h3>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                {{ Form::textarea('email_notes', old('email_notes', (isset($bookingLog->email_notes) ? htmlspecialchars_decode($bookingLog->email_notes) : null)), ['id' => 'email_notes', 'rows' => 3, 'class' => 'form-control basic-format-ckeditor ignore-validation', 'placeholder' => 'Enter email notes']) }}
                                                <div id="email_notes-error-cstm" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #dc3545;">
                                                    The email notes field may not be greater than 500 characters.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner">
                                    <h3 class="card-inner-title">
                                        {{ trans('marketplace.book_event.form.labels.cc_emails') }}
                                    </h3>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                @if(!empty($customEmails))
                                                    @include('admin.booking.edit-cc-emails')  
                                                @else
                                                    @include('admin.booking.edit-cc-emails')
                                                @endif  
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner">
                                    <h3 class="card-inner-title">Registration Details</h3>
                                    <div class="row">
                                        <div class="col-lg-6 col-xl-4 form-group">
                                            <label>
                                                {{ Form::label('registrationdate', 'Registration Date') }}
                                                <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ trans('marketplace.book_event.form.tooltip.registration_date') }}">
                                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                                    </i>
                                                </span>
                                            </label>
                                            {{ Form::text('registrationdate', old('registrationdate', $registrationDate) , ['class' => 'form-control bg-white', 'placeholder' => 'Select Date and Time', 'id' => 'registrationdate', 'autocomplete' => 'off']) }}
                                        </div>
                                        <div class="col-lg-6 col-xl-8">
                                            
                                            <label class="custom-checkbox no-label">{{ trans('marketplace.book_event.form.labels.complementary') }}
                                                {{ Form::checkbox('is_complementary', true, old('is_complementary', $bookingLog->is_complementary), ['class' => 'form-control', 'id' => 'is_complementary']) }}
                                                <span class="checkmark">
                                                </span>
                                                <span class="box-line">
                                                </span>
                                            </label>
                                            <label class="custom-checkbox no-label">
                                                {{ trans('marketplace.book_event.form.labels.add_to_story') }}
                                                {{ Form::checkbox('add_to_story', true, old('add_to_story', $bookingLog->add_to_story), ['class' => 'form-control', 'id' => 'add_to_story']) }}
                                                <span class="checkmark">
                                                </span>
                                                <span class="box-line">
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner mt-0 display-presenter">
                                    <h3 class="card-inner-title"></h3>
                                    <div class="row">
                                        <div class="col-xxl-8 col-lg-9">
                                            <div class="row event-row-list">
                                                <div class="col-lg-4 gray-900">
                                                    {{ trans('marketplace.booking_details.form.labels.time_presenter') }}
                                                </div>
                                                <div class="col-lg-7 gray-600">
                                                    {!! $presenterString !!}
                                                </div>
                                                <div class="col-lg-4 gray-900">
                                                    {{ trans('marketplace.booking_details.form.labels.date') }}
                                                </div>
                                                <div class="col-lg-8 gray-600">
                                                    {{ $displayBookingDate }}
                                                </div>
                                                <div class="col-lg-4 gray-900">
                                                    {{ trans('marketplace.booking_details.form.labels.time-from') }}
                                                </div>
                                                <div class="col-lg-8 gray-600">
                                                    {{ $displayStartTime }}
                                                </div>
                                                <div class="col-lg-4 gray-900">
                                                    {{ trans('marketplace.booking_details.form.labels.time-to') }}
                                                </div>
                                                <div class="col-lg-8 gray-600">
                                                    {{ $displayEndTime }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xxl-4 col-lg-3">
                                            <a class="action-icon float-end h3" id="edit-presenter" href="javascript:void(0)"; title="Edit">
                                                <i class="far fa-edit">
                                                </i>
                                            </a>
                                        </div>
                                    </div>  
                                </div>
                                <div class="update-presenter-section d-none">
                                    <div class="card-inner">
                                        <h3 class="card-inner-title">Pick a Presenter</h3>
                                        <div class="owl-carousel owl-theme arrow-theme" id="presenter-owl-carousel">
                                        @php
                                            $wrapDivCount = 8;
                                            $nextDivStart = true;
                                        @endphp
                                        @foreach($eventPresenters as $key => $presenter)
                                        @if($nextDivStart) 
                                            <div style="display: flex;flex-wrap: wrap;">
                                            @php
                                                $nextDivStart = false;
                                            @endphp   
                                        @endif
                                        <div class="presenter-item border-0 pe-2">
                                            <div class="presenter-item-top">
                                                <div class="presenter-top border-0 p-0">
                                                    <label class="custom-radio w-100 mb-0">
                                                    <div class="presenter-top-left border-0 m-0">
                                                        <img src="{{ $presenter->logo }}" alt="">
                                                        <span class="presenter-name">{{ $presenter->text }}</span>
                                                    </div>
                                                    <input type="radio" name="ws_user" class="roleGroup form-control pick-presenter" {{ ($bookingLog->presenter_user_id === $presenter->id) ? 'Checked' : '' }} value="{{ $presenter->id }}" videoLink="{{ $presenter->video_link }}" slotId="{{ $presenter->id }}">
                                                        <span class="checkmark"></span>
                                                        <span class="box-line"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        @if($key == $wrapDivCount || $key == count($eventPresenters))
                                            </div>
                                            @php
                                                $wrapDivCount = $wrapDivCount + 9;
                                                $nextDivStart = true;
                                            @endphp
                                        @endif
                                        @endforeach
                                        </div>
                                    </div>
                                    @if(strtolower($locationTypes[$event->location_type]) == strtolower('Online'))
                                     <div class="card-inner">
                                        <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group d-flex">
                                                <div class="me-3 w-50">
                                                    {{ Form::label(null, trans('marketplace.book_event.form.labels.video_link')) }}
                                                    <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.video_link_message.message') }}">
                                                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                                                        </i>
                                                    </span>
                                                    {{ Form::text('video_link', $bookingLog->video_link, ['class' => 'form-control','placeholder' => config('zevolifesettings.video_link_message.placeholder'), 'readonly' => true, 'id' => 'video_link_field']) }}
                                                </div>
                                                <div class="me-3 w-10" style="margin-top: 2.5rem!important;">
                                                    <a class="action-icon" href="javascript:;" id="edit-video-link" title="{{ trans('marketplace.buttons.edit') }}">
                                                        <i class="far fa-edit">
                                                        </i>
                                                    </a>
                                                    <a class="action-icon d-none" href="javascript:;" id="save-video-link" title="{{ trans('marketplace.buttons.save') }}">
                                                        <i class="far fa-save">
                                                        </i>
                                                    </a>
                                                </div>
                                                
                                                {{ Form::hidden('company_type', $bookingLog->company_type, ['class' => 'form-control', 'id' => 'company_type'] ) }}
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                {{ Form::hidden('action', 'edit', ['id' => 'action', 'class' => 'form-control']) }}
                                {{ Form::hidden('updateflag', 1, ['id' => 'updateflag', 'class' => 'form-control']) }}
                                {{ Form::hidden('selectedslot', null, ['id' => 'selectedslot', 'class' => 'form-control']) }}
                                {{ Form::hidden('selectedslot_start_time', null , ['id' => 'selectedslot_start_time', 'class' => 'form-control']) }}
                                {{ Form::hidden('selectedslot_end_time', null , ['id' => 'selectedslot_end_time', 'class' => 'form-control']) }}
                                {{ Form::hidden('companyType', $companyType, ['id' => 'companyType', 'class' => 'form-control']) }}
                                {{ Form::hidden('createdCompany', ($event->company_id ?? null), ['id' => 'createdCompany', 'class' => 'form-control']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\BookEventRequest','#bookEvent') !!}
<script src="{{ asset('assets/plugins/step/jquery.steps.js?var='.rand()) }}"></script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.datepair.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}"></script>
<script>
// --------------- Initialize Select2 Elements ---------------
$('#presenter-owl-carousel').owlCarousel({
    loop: false,
    margin: 10,
    nav: true,
    navText: ["<i class='far fa-long-arrow-left'></i>", "<i class='far fa-long-arrow-right'></i>"],
    dots: false,
    items: 1,
});
</script>
<script type="text/javascript">
var bookEvent = $("#bookEvent");
var stepCount = 3;
var urls = {
    getSlots: "{{ route('admin.marketplace.get-slot', [':event']) }}",
    marketplace: "{{ route('admin.marketplace.index', '#bookings-tab') }}",
},
_event = "{{ $event->id }}",
_companyType = `{{ $companyType }}`,
_a = moment('2021-01-01 00:00:00'),
_b = moment('2021-01-01 {{ $event->duration }}'),
_minDiff = (_b.diff(_a, 'milliseconds', true) || 9000000),
_maxDuration = moment('2021-01-02 00:00:00').subtract(_minDiff + 1800000, 'ms').format('hh:mm A'),
_slotsCarousel,
today = new Date(),
stepObj,
endDate = new Date(new Date().setYear(today.getFullYear() + 100)),
url = {
    checkCredit: `{{ route('admin.marketplace.check-credit', ':company') }}`,
    cronofyIndex: `{{ route('admin.bookings.index') }}`,
},
ajaxUrl = {
    createEventSlot: '{{ route("admin.marketplace.create-event-slot", [$event->id]) }}',
    cronofyException: '{{ route("admin.cronofy.sessions.cronofy-exception") }}',
},
data = {
    dataCenter: "{{ env('CRONOFY_DATA_CENTER') }}",
    availableRuleId: `{{ config('cronofy.availableRuleId') }}`,
    eventId: `{{ $event->id }}`,
    duration: `{{ $duration }}`,
    reschedule: `{{ $reschedule }}`,
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
},
_trans = {
    bookBtn: "{{ trans('buttons.general.book') }}",
    yesBtn: "{{ trans('buttons.general.yes') }}",
    swr: "{{ trans('marketplace.book_event.messages.something_wrong_try_again') }}",
    capacityError: "{{ trans('marketplace.book_event.messages.capacity_error') }}",
    credit_error: "{{ trans('marketplace.book_event.messages.credit_error') }}",
};
var start = new Date();
start.setHours(start.getHours() + 48);
end = new Date(new Date().setMonth(start.getMonth() + 3));
</script>
<script type="text/javascript">
var urls = {
        getSlots: "{{ route('admin.marketplace.get-slot', [$event->id, $bookingLogId]) }}",
    },
    _companyType = `{{ $companyType }}`,
    _a = moment('2021-01-01 00:00:00'),
    _b = moment('2021-01-01 {{ $event->duration }}'),
    _minDiff = (_b.diff(_a, 'milliseconds', true) || 9000000),
    _maxDuration = moment('2021-01-02 00:00:00').subtract(_minDiff + 1800000, 'ms').format('hh:mm A'),
    _slotsCarousel,
    _trans = {
        yesBtn: "{{ trans('buttons.general.yes') }}",
        swr: "{{ trans('marketplace.book_event.messages.something_wrong_try_again') }}",
        capacityError: "{{ trans('marketplace.book_event.messages.capacity_error') }}",
    },
    data = {
        locationType: `{{ strtolower($locationTypes[$event->location_type]) }}`,
        buttonName: "Update",
    };
</script>
<script type="text/javascript">
$(document).ready(function() {
    var companyFeedSelection = $('#company').find(":selected").attr('data-feed-selection');
    if (companyFeedSelection != 'false') {
        $('#add_to_story').prop('disabled', false).parent().show();
    } else {
        $('#add_to_story').prop('disabled', true).parent().hide();
    }
});
</script>
<script src="{{ mix('js/marketplace/bookEvent.js') }}">
</script>
@endsection

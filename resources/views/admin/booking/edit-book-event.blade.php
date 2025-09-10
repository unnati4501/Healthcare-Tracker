@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.css?var='.rand()) }}" rel="stylesheet"/>
<style type="text/css">
    .presenter-avatar { height: 50px; width: 50px !important; display: inline-block !important; border-radius: 50%; margin-right: 20px; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.booking.breadcrumb', [
    'mainTitle' => trans('marketplace.booking_details.buttons.edit_event'),
    'breadcrumb' => Breadcrumbs::render('booking.book_event.edit', $bookingLog->id),
    'backToBookingDetails' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card event-wrap">
            {{ Form::open(['route' => ['admin.bookings.edit-booked-event', $bookingLog->id], 'class' => 'form-horizontal book_event_from_submit', 'method' => 'POST', 'role' => 'form', 'id' => 'bookEvent']) }}
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-xxl-4 col-lg-3 mb-4">
                            <img src="{{ $event->logo }}"/>
                        </div>
                        <div class="col-xxl-8 col-lg-9">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label(null, trans('marketplace.book_event.form.labels.event_name')) }}
                                        {{ Form::text(null, $event->name, ['disabled' => true, 'class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label(null, trans('marketplace.book_event.form.labels.duration')) }}
                                        {{ Form::text(null, convertToHoursMins(timeToDecimal($event->duration), false, '%s %s'), ['disabled' => true, 'class' => 'form-control', 'data-duration']) }}
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label(null, trans('marketplace.book_event.form.labels.location')) }}
                                        {{ Form::text(null, $locationTypes[$event->location_type], ['disabled' => true, 'class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-lg-6" data-capacity="{{ $event->capacity }}" data-capacity-block="" data-totalusers="0">
                                    <div class="form-group">
                                        @if(!empty($event->capacity))
                                            {{ Form::label(null, trans('marketplace.book_event.form.labels.capacity')) }}
                                            {{ Form::text(null, $event->capacity, ['disabled' => true, 'class' => 'form-control']) }}
                                        @endif
                                    </div>
                                </div>
                                @if(strtolower($locationTypes[$event->location_type]) == strtolower('Online'))
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {{ Form::label('companyType', trans('marketplace.book_event.form.labels.company_type')) }}
                                        {{ Form::select('company_type', config('zevolifesettings.company-type'), $bookingLog->company_type, ['class' => 'form-control select2', 'id' => 'location', 'data-allow-clear' => 'false'] ) }}
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label(null, trans('marketplace.book_event.form.labels.video_link')) }}
                                        <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.video_link_message.message') }}">
                                            <i aria-hidden="true" class="far fa-info-circle text-primary">
                                            </i>
                                        </span>
                                        {{ Form::text('video_link', (!empty($bookingLog->video_link) ? $bookingLog->video_link : null), ['class' => 'form-control','placeholder' => config('zevolifesettings.video_link_message.placeholder'), 'autocomplete'=>'off' ]) }}
                                    </div>
                                </div>
                                @endif
                                {{-- <div class="col-lg-12">
                                    <div class="form-group">
                                        {{ Form::label(null, trans('marketplace.book_event.form.labels.description')) }}
                                        <div>
                                            {!! $event->description !!}
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-inner">
                    <h3 class="card-inner-title">
                        {{ trans('marketplace.book_event.form.labels.description') }}
                    </h3>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                {!! $event->description !!}
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
                                    @include('admin.marketplace.add-cc-emails') 
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-inner">
                    <h3 class="card-inner-title">
                        {{ trans('marketplace.book_event.form.labels.additional_notes') }}
                    </h3>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                {{ Form::textarea('notes', $bookingLog->notes, ['id' => 'notes', 'rows' => 3, 'class' => 'form-control basic-format-ckeditor ignore-validation', 'placeholder' => 'Enter additional notes']) }}
                                <div id="notes-error-cstm" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #dc3545;">
                                    The notes field may not be greater than 500 characters.
                                </div>
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
                                @if($disableEmailNote)
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="Email notes won't be editable before 24 hours of the event start time.">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                @endif
                                {{ Form::textarea('email_notes', $bookingLog->email_notes, ['id' => 'email_notes', 'rows' => 3, 'class' => 'form-control basic-format-ckeditor ignore-validation', 'placeholder' => 'Enter email notes', 'disabled' => $disableEmailNote]) }}
                                <div id="email_notes-error-cstm" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #dc3545;">
                                    The email notes field may not be greater than 500 characters.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-inner">
                    {{-- <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                @if(!empty($customEmails))
                                    @include('admin.booking.edit-cc-emails')  
                                @else
                                    @include('admin.marketplace.add-cc-emails') 
                                @endif
                            </div>
                        </div>
                    </div> --}}
                    @if($statuses)
                    <div class="row">
                        <div class="col-lg-4">
                           <div class="form-group ">
                               {{ Form::label('eventStatus', trans('marketplace.book_event.form.labels.status')) }}
                               {{ Form::select('eventStatus', $statuses, $bookingLog->status, ['class' => 'form-control select2', 'id' => 'eventStatus', 'placeholder' => trans('marketplace.filter.status'), 'data-placeholder' => trans('marketplace.filter.status'), 'data-allow-clear' => 'false']) }}
                           </div>
                       </div>
                    </div>
                    @endif
                    
                    @if($bookingLog->status == 4)
                        @php $class = ""; @endphp
                    @else
                        @php $class = "slot-disabled"; @endphp
                    @endif
                    
                    <div class="row {{$class}}">
                        <div class="col-xl-3 col-lg-6">
                            <div class="form-group">
                                {{ Form::label('company', trans('marketplace.book_event.form.labels.company')) }}
                                {{ Form::text('', $company->name, ['class' => 'form-control', 'disabled' => true]) }}
                                {{ Form::hidden('company', $company->id, ['id' => 'company']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6">
                            <div class="form-group">
                                {{ Form::label('date', trans('marketplace.book_event.form.labels.date')) }}
                                {{ Form::text('date', $bookingDate, ['class' => 'form-control bg-white', 'placeholder' => 'Select date', 'id' => 'date', 'readonly' => true]) }}
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-12" id="rcaEventDates" style="display: none;">
                            <div class="row time-range">
                                <div class="col-xl-6 col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('timeFrom', trans('marketplace.book_event.form.labels.time-from')) }}
                                        {{ Form::text('timeFrom', $fromTime, ['class' => 'form-control bg-white time start', 'id' => 'timeFrom', 'readonly' => true]) }}
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="form-group pe-none">
                                        {{ Form::label('timeTo', trans('marketplace.book_event.form.labels.time-to')) }}
                                        {{ Form::text('timeTo', $toTime, ['class' => 'form-control time end', 'id' => 'timeTo', 'readonly' => true]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6" id="rcaPresenter" style="display: none;">
                            <div class="form-group">
                                {{ Form::label('presenterName', trans('marketplace.book_event.form.labels.presenter_name')) }}
                                {{ Form::text('presenterName', ($presenterName ?? null), ['class' => 'form-control', 'id' => 'presenterName', 'placeholder' => trans('marketplace.book_event.form.placeholder.presenter_name'), 'readonly' => true]) }}
                            </div>
                        </div>
                        <div class="w-100">
                        </div>
                        <div class="col-lg-12" id="mainPresenterList" style="display: block;">
                            <div class="form-group">
                                {{ Form::label('', trans('marketplace.book_event.form.labels.pick_a_time')) }}
                                <div data-hint-block="" style="display: none;">
                                    <span>
                                        {{ trans('marketplace.book_event.messages.slot') }}
                                    </span>
                                </div>
                                <div data-loader-block="" style="display: block;">
                                    <span class="fa fa-spinner fa-spin ms-2">
                                    </span>
                                    {{ trans('marketplace.book_event.messages.loading_slots') }}
                                </div>
                                <div data-no-slots-block="" style="display: none;">
                                    <span>
                                        {{ trans('marketplace.book_event.messages.no_result_found') }}
                                    </span>
                                </div>
                                <div class="owl-carousel owl-theme arrow-theme" data-slots-block="" style="display: none;"  id="time-owl-carousel">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6" style="display: none;">
                            <div class="col-lg-6">
                                <label class="custom-checkbox pe-none">
                                    {{ trans('marketplace.book_event.form.labels.register_all_users') }}
                                    {{ Form::checkbox('register_all_users', true, $bookingLog->register_all_users, ['class' => 'form-control', 'id' => 'register_all_users']) }}
                                    <span class="checkmark">
                                    </span>
                                    <span class="box-line">
                                    </span>
                                </label>
                            </div>
                        </div>
                        @if($showComplementaryOpt)
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="custom-checkbox">
                                    {{ trans('marketplace.book_event.form.labels.complementary') }}
                                    {{ Form::checkbox('is_complementary', true, $bookingLog->is_complementary, ['class' => 'form-control', 'id' => 'is_complementary']) }}
                                    <span class="checkmark">
                                    </span>
                                    <span class="box-line">
                                    </span>
                                </label>
                            </div>
                        </div>
                        @endif
                        @if($addToStroryVisibility)
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="custom-checkbox">
                                    {{ trans('marketplace.book_event.form.labels.add_to_story') }}
                                    {{ Form::checkbox('add_to_story', true, old('add_to_story', $bookingLog->add_to_story), ['class' => 'form-control', 'id' => 'add_to_story']) }}
                                    <span class="checkmark">
                                    </span>
                                    <span class="box-line">
                                    </span>
                                </label>
                            </div>
                        </div>
                        @endif
                    </div>
                    {{ Form::hidden('selectedslot', $bookingLog->slot_id, ['id' => 'selectedslot', 'class' => 'form-control']) }}
                    {{ Form::hidden('selectedslot_start_time', $fromTime , ['id' => 'selectedslot_start_time', 'class' => 'form-control']) }}
                    {{ Form::hidden('selectedslot_end_time', $toTime , ['id' => 'selectedslot_end_time', 'class' => 'form-control']) }}
                    {{ Form::hidden('companyType', $companyType, ['id' => 'companyType', 'class' => 'form-control']) }}
                    {{ Form::hidden('createdCompany', ($event->company_id ?? null), ['id' => 'createdCompany', 'class' => 'form-control']) }}
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <button class="btn btn-primary ms-auto" id="btn-book-event" type="button">
                        {{ trans('marketplace.book_event.buttons.book') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
<div class="modal fade" data-backdrop="static" data-keyboard="false" id="book-event-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('labels.book_event.index_title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    {{ trans('labels.book_event.book_event_confirm') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="book-event-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditBookEventRequest','#bookEvent') !!}
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
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.datepair.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var urls = {
            getSlots: "{{ route('admin.marketplace.get-slot', [$event->id, $bookingLog->id]) }}",
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
        };
</script>
<script src="{{ mix('js/marketplace/edit-bookEvent.js') }}" type="text/javascript">
</script>
@endsection

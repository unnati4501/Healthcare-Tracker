@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.breadcrumb', [
    'appPageTitle' => trans('Cronofy.title.availability'),
    'breadcrumb' => 'cronofy.availability',
    'addCalendar' => false
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.cronofy.store', $userId], 'class' => 'form-horizontal zevo_form_submit', 'role' => 'form', 'id' => 'availabilityAdd']) }}
            <div class="card-body">
                <div class="col-xxl-12">
                    @if(in_array($responsibilities, [1, 3]))
                    <div class="card-inner wbs-availability">
                        <h3 class="card-inner-title">
                            1:1 Digital Therapy Sessions
                            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="Set availability for the 1:1 Digital Therapy Sessions">
                                <i aria-hidden="true" class="far fa-info-circle text-primary">
                                </i>
                            </span>
                        </h3>
                        <div class="table-responsive set-availability-block">
                            @foreach($hc_availability_days as $keyday => $day)
                            <div class="d-flex set-availability-box pb-1 mb-1 align-items-center" data-day-key="{{ $keyday }}">
                                <div class="set-availability-day">
                                    {{ $day }}
                                </div>
                                <div class="w-100 slots-wrapper">
                                    <div class="d-flex align-items-center no-data-block {{ (array_key_exists($keyday, $userSlots) ? 'd-none' : '') }}">
                                        <div class="set-availability-date-time">
                                            {{ trans('labels.user.not_available') }}
                                        </div>
                                        <div class="d-flex set-availability-btn-area justify-content-end">
                                            <a class="add-slot action-icon text-info" href="javascript:;" title="Add Slot">
                                                <i class="far fa-plus">
                                                </i>
                                            </a>
                                        </div>
                                    </div>
                                    @if(array_key_exists($keyday, $userSlots))
                                        @foreach($userSlots[$keyday] as $slot)
                                            @include('admin.user.slot-preview', [
                                                'start_time' => $slot['start_time']->format('H:i'),
                                                'end_time' => $slot['end_time']->format('H:i'),
                                                'time' => $slot['start_time']->format('h:i A') . ' - ' . $slot['end_time']->format('h:i A'),
                                                'key' => $keyday,
                                                'id' => $slot['id'],
                                            ])
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        {{ Form::hidden('slots_exist', ((sizeof($userSlots) > 0) ? '1' : ''), ['id' => 'slots_exist']) }}
                    </div>
                    @endif
                    @if(in_array($responsibilities, [2, 3]))
                    <div class="card-inner event-presenter-availability">
                        <h3 class="card-inner-title">
                            Marketplace Events
                            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="Set availability for the Marketplace Events">
                                <i aria-hidden="true" class="far fa-info-circle text-primary">
                                </i>
                            </span>
                        </h3>
                        <div class="table-responsive set-availability-block">
                            @foreach($hc_availability_days as $keyday => $day)
                            <div class="d-flex set-availability-box pb-1 mb-1 align-items-center" data-day-key="{{ $keyday }}">
                                <div class="set-availability-day">
                                    {{ $day }}
                                </div>
                                <div class="w-100 slots-wrapper">
                                    <div class="d-flex align-items-center no-data-block {{ (array_key_exists($keyday, $presenterSlots) ? 'd-none' : '') }}">
                                        <div class="set-availability-date-time">
                                            {{ trans('labels.user.not_available') }}
                                        </div>
                                        <div class="d-flex set-availability-btn-area justify-content-end">
                                            <a class="add-presenter-slot action-icon text-info" href="javascript:void(0);" title="Add Slot">
                                                <i class="far fa-plus">
                                                </i>
                                            </a>
                                        </div>
                                    </div>
                                    @if(array_key_exists($keyday, $presenterSlots))
                                        @foreach($presenterSlots[$keyday] as $slot)
                                            @include('admin.user.presenter-slot-preview', [
                                                'start_time' => $slot['start_time']->format('H:i'),
                                                'end_time' => $slot['end_time']->format('H:i'),
                                                'time' => $slot['start_time']->format('h:i A') . ' - ' . $slot['end_time']->format('h:i A'),
                                                'key' => $keyday,
                                                'id' => $slot['id'],
                                            ])
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        {{ Form::hidden('presenter_slots_exist', ((sizeof($presenterSlots) > 0) ? '1' : ''), ['id' => 'presenter_slots_exist']) }}
                    </div>
                    @endif
                </div>
                <input type="hidden" name="responsbility" value="{{ $responsibilities }}">
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('dashboard') }}">
                        {{ trans('labels.buttons.cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('labels.buttons.save') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
<script id="add-new-slot-template" type="text/html">
    @include('admin.cronofy.add-new-slot')
</script>
<script id="edit-slot-template" type="text/html">
    @include('admin.cronofy.edit-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'id' => ''
    ])
</script>
<script id="preview-slot-template" type="text/html">
    @include('admin.cronofy.slot-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'time' => '#time#',
        'key' => '#key#',
        'id' => '#id#'
    ])
</script>
<script id="add-new-presenter-slot-template" type="text/html">
    @include('admin.user.add-new-presenter-slot')
</script>
<script id="edit-presenter-slot-template" type="text/html">
    @include('admin.user.edit-presenter-slot-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'id' => ''
    ])
</script>
<script id="preview-presenter-slot-template" type="text/html">
    @include('admin.user.presenter-slot-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'time' => '#time#',
        'key' => '#key#',
        'id' => '#id#'
    ])
</script>
@include('admin.user.remove-presenter-slot-model-box')
@include('admin.cronofy.remove-slot-model-box')
@endsection
<!-- include datatable css -->
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CronofyAvailabilityRequest','#availabilityAdd') !!}
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
{{-- <script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.datepair.min.js?var='.rand()) }}">
</script> --}}
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
var today = new Date(),
endDate = new Date(new Date().setYear(today.getFullYear() + 100)),
url = {
    availabilityUrl: `{{ route('admin.cronofy.availability') }}`,
    dashboad: `{{ route('dashboard') }}`,
},
message = {
    something_wrong: `{{ trans('Cronofy.message.something_wrong') }}`,
    availability_message: `{{ trans('Cronofy.message.calendar_availability') }}`,
};
</script>
<script src="{{ asset('js/cronofy/availability.js') }}" type="text/javascript">
</script>
<script src="{{ mix('js/ws/slots.js') }}"></script>
@if((isset($wsDetails) && !$wsDetails->is_cronofy) || (isset($wcDetails) && !$wcDetails->is_cronofy) )
<script type="text/javascript">
    $(document).ready(function() {
        $('body.sidebar-mini').addClass('sidebar-collapse');
    });
</script>
@endif
@endsection
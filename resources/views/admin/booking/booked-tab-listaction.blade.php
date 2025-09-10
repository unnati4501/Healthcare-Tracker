@if(!empty($record->id) && $eventStatus != 'Cancelled' )
<a class="action-icon" href="{{ route('admin.bookings.booking-details', $record->id) }}" title="{{ trans('marketplace.buttons.booking_details') }}">
    <i class="far fa-eye">
    </i>
</a>

@permission('edit-event')
@if ($record->registration_date > $todayDate)
<a class="action-icon" href="{{ route('admin.bookings.edit-booked-event', $record->id) }}" title="{{ trans('marketplace.buttons.edit_event') }}">
    <i class="far fa-edit">
    </i>
</a>
@endif
@endauth
@permission('cancel-event')
@if(($eventStatus != 'Completed') && ((($eventStartTime > $todayDate) && $role->group == 'zevo')) || (($advanceDate < $eventStartTime) && $role->group != 'zevo'))
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="cancel-book-event" title="{{trans('buttons.general.tooltip.cancel')}}">
    <i aria-hidden="true" class="fa fa-times">
    </i>
</a>
@endif
@endauth
@permission('event-registered-users')
@if($record->users_count > 0)
<a class="action-icon" href="{{ route('admin.bookings.registered-users', $record->id) }}" title="{{ trans('marketplace.buttons.view_event_reg_users') }}">
    <i class="far fa-users">
    </i>
</a>
@endif
@endauth
@php
$bookingDate    = \Carbon\Carbon::parse("{$record->booking_date} {$record->start_time}");
$bookingEndDate = \Carbon\Carbon::parse("{$record->booking_date} {$record->end_time}");
$duration       = $bookingDate->diffInMinutes($bookingEndDate);
$eventType      = $record->location_type;
@endphp
@if($eventStatus == 'Booked' || $eventStatus == 'Paused') 
<a class="action-icon clone" data-eventname = "{{$record->event_name}}" data-eventtype="{{$eventType}}" data-notes="{{$record->additionalNotes}}" data-videolink="{{$record->video_link}}" data-presenter="{{$record->presenter}}" data-eventdate="{{$bookingDate->format('M d, Y h:i A')}}" data-id="{{$record->id}}"  data-duration="{{$duration}}" href="javaScript:void(0)"  title="{{trans('buttons.general.tooltip.copy')}}">
    <i aria-hidden="true" class="fa fa-copy">
    </i>
</a>
@endif
@endif

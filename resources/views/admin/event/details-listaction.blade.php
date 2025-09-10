@permission('cancel-event')
@if($record->startTimeDiff >= 3601)
@if($record->status == '4')
<a class="action-icon cancel-event" data-bid="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('event.details.buttons.cancel_event')}}">
    <i class="far fa-times">
    </i>
</a>
@elseif($record->status == '3')
<a class="action-icon view-cancel-event-details" data-bid="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('event.details.buttons.view_cancel_details')}}">
    <i class="far fa-eye">
    </i>
</a>
@endif
@endif
@endauth

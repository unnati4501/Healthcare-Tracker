@if(!empty($record->id))
@if($record->status == 2)
<a class="action-icon" href="{{ route('admin.event.view', $record->id) }}" title="{{ trans('event.buttons.view')}}">
    <i class="far fa-tasks">
    </i>
</a>
@permission('view-event-feedback')
@if(!is_null($feedBackCount) && $feedBackCount > 0)
<a class="action-icon" href="{{ route('admin.event.feedback', $record->id) }}" title="{{ trans('event.buttons.feedback')}}">
    <i class="far fa-eye">
    </i>
</a>
@endif
@endauth
@endif
@permission('edit-event')
<a class="action-icon" href="{{ route('admin.event.edit', [$record->id]) }}" title="{{ trans('event.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-event')
@if(is_null($openBookingCount) || $openBookingCount == 0)
<a class="action-icon danger" data-id="{{ $record->id }}" href="javascript:void(0);" id="eventDelete" title="{{ trans('event.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth
@endif

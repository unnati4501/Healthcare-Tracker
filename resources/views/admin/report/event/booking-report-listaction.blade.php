@if($record->status == '3')
<a class="action-icon view-cancel-event-details" data-bid="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('labels.event.view_cancel_details')}}">
    <i aria-hidden="true" class="far fa-eye">
    </i>
</a>
@endif

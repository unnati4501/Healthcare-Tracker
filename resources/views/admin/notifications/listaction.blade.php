@if(!empty($record->id))
@permission('view-notification')
<a class="action-icon" href="{{route('admin.notifications.show', $record->id)}}" title="{{trans('buttons.general.tooltip.view')}}">
    <i aria-hidden="true" class="far fa-eye">
    </i>
</a>
@endauth
@endif
	
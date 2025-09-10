@if(!empty($record->id))
@permission('edit-broadcast-message')
@if($allowEdit)
<a class="action-icon" href="{{ route('admin.broadcast-message.edit', $record->id) }}" title="{{ trans('buttons.general.tooltip.edit') }}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endif
@endauth
@permission('delete-broadcast-message')
<a class="action-icon danger delete-broadcast" data-id="{{$record->id}}" href="javascript:void(0);" title="{{trans('buttons.general.tooltip.delete')}}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif

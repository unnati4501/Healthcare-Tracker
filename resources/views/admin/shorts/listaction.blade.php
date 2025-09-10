@if(!empty($record->id))
@permission('update-shorts')
<a class="action-icon" href="{{ route('admin.shorts.edit', $record->id) }}" title="{{ trans('shorts.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-shorts')
<a class="action-icon danger shortsDelete" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('shorts.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif

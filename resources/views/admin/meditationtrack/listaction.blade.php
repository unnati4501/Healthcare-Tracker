@if(!empty($record->id))
@permission('update-meditation-library')
<a class="action-icon" href="{{ route('admin.meditationtracks.edit', $record->id) }}" title="{{ trans('meditationtrack.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-meditation-library')
<a class="action-icon danger trackDelete" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('meditationtrack.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif

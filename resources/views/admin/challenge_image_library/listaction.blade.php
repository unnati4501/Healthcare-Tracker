@permission('update-challenge-image')
<a class="action-icon" href="{{ route('admin.challengeImageLibrary.edit', $record->id) }}" title="{{ trans('challengeLibrary.buttons.tooltips.edit') }}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-challenge-image')
<a class="action-icon danger delete-image" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('challengeLibrary.buttons.tooltips.delete') }}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endauth

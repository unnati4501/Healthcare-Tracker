@permission('edit-category-tags')
<a class="action-icon" href="{{ route('admin.categoryTags.edit', $record->id) }}" title="{{ trans('buttons.general.tooltip.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth

@permission('delete-category-tags')
@if($record->getMappedContentCount() == 0)
<a class="action-icon danger delete-tag" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('buttons.general.tooltip.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth

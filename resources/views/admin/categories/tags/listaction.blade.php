@permission('view-category-tags')
<a class="action-icon" href="{{ route('admin.categoryTags.view', $record->id) }}" title="{{ trans('buttons.general.tooltip.view') }}">
    <i class="far fa-eye">
    </i>
</a>
@endauth

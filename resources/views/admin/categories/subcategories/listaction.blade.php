@permission('update-sub-category')
<a class="action-icon" href="{{route('admin.subcategories.edit', $subCategory->id)}}" title="{{ trans('categories.subcategories.buttons.tooltips.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-sub-category')
@if(!$subCategory->default)
<a class="action-icon danger" data-id="{{$subCategory->id}}" href="javaScript:void(0)" id="subCategoryDelete" title="{{ trans('categories.subcategories.buttons.tooltips.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth

@permission('update-survey-sub-category')
<a class="action-icon" href="{{route('admin.surveysubcategories.edit', [$subCategory->category_id, $subCategory->id]) }}" title="{{ trans('surveysubcategories.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-survey-sub-category')
@if(!$subCategory->default)
<a class="action-icon danger surveysubCategoryDelete" data-id="{{$subCategory->id}}" href="javascript:void(0);" id="" title="{{ trans('surveysubcategories.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth

@permission('update-survey-category')
<a class="action-icon" href="{{ route('admin.surveycategories.edit', $category->id) }}" title="{{ trans('surveycategories.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-survey-category')
@if(!$category->default)
<a class="action-icon danger surveyCategoryDelete" data-id="{{$category->id}}" href="javaScript:void(0)" title="{{ trans('surveycategories.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth
@permission('view-survey-category')
<a class="action-icon" href="{{route('admin.surveysubcategories.index', $category->id)}}" title="{{ trans('surveycategories.buttons.view') }}">
    <i class="far fa-eye">
    </i>
</a>
@endauth

@permission('view-category')
<a class="action-icon" href="{{route('admin.subcategories.index', $category->id)}}" title="{{ trans('categories.buttons.tooltips.view') }}">
    <i class="far fa-eye">
    </i>
</a>
@endauth

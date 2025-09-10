@permission('update-services')
<a class="action-icon" href="{{route('admin.services.edit', $service->id)}}" title="{{ trans('services.subcategories.buttons.tooltips.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-services')
@if(!$service->default)
<a class="action-icon danger" data-id="{{$service->id}}" href="javaScript:void(0)" id="serviceDelete" title="{{ trans('services.subcategories.buttons.tooltips.archive') }}">
    <i class="far fa-archive">
    </i>
</a>
@endif
@endauth


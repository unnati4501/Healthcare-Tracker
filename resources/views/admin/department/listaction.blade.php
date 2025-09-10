@if(!empty($department->id))
@permission('update-department')
<a class="action-icon" href="{{ route('admin.departments.edit', $department->id) }}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endauth
@permission('view-location')
<a class="action-icon danger" href="{{ route('admin.departments.locationList', $department->id) }}" title="{{trans('department.buttons.view_location')}}">
    <i aria-hidden="true" class="far fa-map-marker-alt">
    </i>
</a>
@endauth
@permission('delete-department')
@if(!$department->default && $department->members_count == 0)
<a class="action-icon danger" data-id="{{ $department->id }}" href="javascript:void(0);" id="departmentDelete" title="{{trans('buttons.general.tooltip.delete')}}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth
@endif

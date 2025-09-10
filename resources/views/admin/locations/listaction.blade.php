@permission('update-location')
<a class="action-icon" href="{{route('admin.locations.edit', $record->id)}}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-location')
@if($record->default == 0 && $record->department_location_count == 0 && $record->team_location_count == 0)
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="locationDelete" title="{{trans('buttons.general.tooltip.delete')}}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth

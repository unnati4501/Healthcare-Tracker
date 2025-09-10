@if(!empty($record->id))
@permission('update-exercise')
<a class="action-icon" href="{{route('admin.exercises.edit', $record->id)}}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit"></i>
</a>
@endauth
@permission('delete-exercise')
<a href="javaScript:void(0)" class="action-icon danger" title="{{trans('buttons.general.tooltip.delete')}}" data-id="{{$record->id}}" id="exerciseDelete">
    <i class="far fa-trash-alt" aria-hidden="true" ></i>
</a>
@endauth
@endif
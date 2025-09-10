@if(!empty($team->id))
@permission('update-team')
<a class="action-icon" href="{{route('admin.teams.edit', $team->id)}}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit"></i>
</a>
@endauth
@permission('delete-team')
@if($team->default == 0 && $team->users->count() == 0)
<a href="javaScript:void(0)" class="action-icon danger" title="{{trans('buttons.general.tooltip.delete')}}" data-id="{{$team->id}}" id="teamDelete">
    <i class="far fa-trash-alt" aria-hidden="true" ></i>
</a>
@endif
@endauth
@endif
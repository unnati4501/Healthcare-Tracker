@if(!empty($record->id))
@if($record->is_default == true && $record->type == 'masterclass')
@permission('view-badge')
<a class="action-icon" href="{{route('admin.badges.masterclassbadgelist')}}" title="{{trans('buttons.general.tooltip.view')}}">
    <i aria-hidden="true" class="far fa-eye"></i>
</a>
@endauth
@endif
@if(is_null(Auth::user()->company->first()) || (!is_null(Auth::user()->company->first()) && $record->company_id == Auth::user()->company->first()->id) )
@permission('update-badge')
<a class="action-icon" href="{{route('admin.badges.edit', $record->id)}}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit"></i>
</a>
@endauth
@permission('delete-badge')
@if($record->is_default == false)
<a href="javaScript:void(0)" class="action-icon danger" title="{{trans('buttons.general.tooltip.delete')}}" data-id="{{$record->id}}" id="badgeDelete">
    <i class="far fa-trash-alt" aria-hidden="true" ></i>
</a>
@endif
@endauth
@endif
@endif
@permission('update-story')
@if(($role->group == 'zevo') || ($role->group == 'company' && $record->company_id == $user->company->first()->id) || ($role->group == 'reseller' && ($record->company_id == $user->company->first()->id || $isShowButton == true )))
<a class="action-icon" href="{{route('admin.feeds.edit', $record->id)}}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endif
@endauth
@permission('delete-story')
@if(($role->group == 'zevo') || ($role->group == 'company' && $record->company_id == $user->company->first()->id) || ($role->group == 'reseller' && ($record->company_id == $user->company->first()->id || $isShowButton == true )))
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="feedDelete" title="{{trans('buttons.general.tooltip.delete')}}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth
@if($role->group == 'company' && $storyCreatedBy->slug == 'super_admin' && $record->end_date < NOW())
<a class="action-icon" href="{{route('admin.feeds.clone', $record->id)}}" data-id="{{$record->id}}" href="javaScript:void(0)" id="feedClone" title="{{trans('buttons.general.tooltip.clone')}}">
    <i aria-hidden="true" class="far fa-clone">
    </i>
</a>
@endif

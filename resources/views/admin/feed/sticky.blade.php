@permission('sticky-story')
@if((($role->group == 'zevo' || $role->group == 'company' || $role->group == 'reseller') && $record->company_id == $companyId))
@if($record->is_stick == false && $record->end_date > gmdate("Y-m-d H:i:s"))
<a class="btn btn-primary badge-btn stick-feed" data-action="stick" data-id="{{$record->id}}" href="javascript:void(0);" title="{{ trans('feed.buttons.stick') }}">
    Stick
</a>
@elseif($record->is_stick == true)
<a class="btn btn-outline-secondary badge-btn stick-feed" data-action="unstick" data-id="{{$record->id}}" href="javascript:void(0);" title="{{ trans('feed.buttons.unstick') }}">
    Unstick
</a>
@endif
@elseif($record->company_id == null && $record->is_stick == true && $record->end_date > gmdate("Y-m-d H:i:s"))
<span class="text-secondary" data-action="unstick" data-id="{{$record->id}}" title="{{ trans('feed.buttons.stick') }}">
    Stick
</span>
@elseif($role->group == 'reseller' && count($childcompany) <= 0 && $record->is_stick == true && $record->end_date > gmdate("Y-m-d H:i:s"))
<span class="text-secondary" data-action="unstick" data-id="{{$record->id}}" title="{{ trans('feed.buttons.stick') }}">
    Stick
</span>
@endif
@endauth

@if($role->group == 'zevo') 
@if($ea->is_stick == false)
<a class="btn btn-primary badge-btn stick-support" data-action="stick" data-id="{{$ea->id}}" href="javascript:void(0);" title="{{ trans('eap.buttons.stick') }}">
    Stick
</a>
@elseif($ea->is_stick == true)
<a class="btn btn-outline-secondary badge-btn stick-support" data-action="unstick" data-id="{{$ea->id}}" href="javascript:void(0);" title="{{ trans('eap.buttons.unstick') }}">
    Unstick
</a>
@endif
@else 
@if($ea->is_stick == true)
<span class="text-secondary" data-action="unstick" data-id="{{$ea->id}}" title="{{ trans('feed.buttons.stick') }}">
    Stick
</span>
@endif
@endif

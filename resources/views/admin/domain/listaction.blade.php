@if(!empty($domain->id))
@if($userAssigned->count() == 0)
<a class="action-icon" href="{{route('admin.domains.edit', $domain->id)}}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit"></i>
</a>

<a href="javaScript:void(0)" class="action-icon danger" title="{{trans('buttons.general.tooltip.delete')}}" data-id="{{$domain->id}}" id="domainDelete">
    <i class="far fa-trash-alt" aria-hidden="true" ></i>
</a>
@endif
@endif
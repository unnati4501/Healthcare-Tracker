@if(!$record->default)
<a class="action-icon" href="{{route('admin.company-plan.edit', $record->id)}}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit"></i>
</a>
@endif
@if(!$record->default && $attechCount <= 0)
<a href="javaScript:void(0)" class="action-icon danger" title="{{trans('buttons.general.tooltip.delete')}}" data-id="{{$record->id}}" id="companyplanDelete">
    <i class="far fa-trash-alt" aria-hidden="true" ></i>
</a>
@endif
@if($ea->company_id == $companyId)
@permission('update-support')
<a class="action-icon" href="{{ route('admin.support.edit', $ea->id) }}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endauth

@permission('delete-support')
<a class="action-icon danger" data-id="{{ $ea->id }}" href="javaScript:void(0)" id="companyDelete" title="{{trans('buttons.general.tooltip.delete')}}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif

@if($record->status == 1)
<span class="text-success">
    {{ trans('recipe.buttons.approved') }}
</span>
@else
@if(($role->group == "company" && $record->company_id == $companyId) || ($role->group == "reseller" && ( $record->company_id == $companyId || $record->company_id == $parent_id )))
<a class="btn btn-primary badge-btn recipeApprove" data-id="{{ $record->id }}" href="javascript:void(0);">
    {{ trans('recipe.buttons.approve') }}
</a>
@else
<span class="text-warning">
    {{ trans('labels.recipe.pending') }}
</span>
@endif
@endif

@permission('view-recipe')
<a class="action-icon" href="{{ route('admin.recipe.details', $record->id) }}" title="{{ trans('recipe.buttons.view') }}">
    <i class="far fa-eye">
    </i>
</a>
@endauth
@permission('update-recipe')
@if(($role->group == "zevo" && is_null($record->company_id)) || ($role->group == "company" && ($record->company_id == $companyId) || ($record->creator_id == $user->id)))
<a class="action-icon" href="{{ route('admin.recipe.edit', $record->id) }}" title="{{ trans('recipe.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endif
@endauth
@permission('delete-recipe')
@if(($role->group == "zevo" && is_null($record->company_id)) || ($role->group == "company" && ($record->company_id == $companyId) || ($record->creator_id == $user->id)))
<a class="action-icon danger companyDelete" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('recipe.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth

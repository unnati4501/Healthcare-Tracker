@if(!empty($role->id))
@permission('update-role')
<a class="action-icon" href="{{ route('admin.roles.edit', $role->id) }}" title="{{ trans('roles.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@endif
@if($role->users_count == 0 && $role->associated_companies_count == 0)
@permission('delete-role')
<a class="action-icon danger roleDelete" data-id="{{ $role->id }}" href="javascript:void(0);" title="{{ trans('roles.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif

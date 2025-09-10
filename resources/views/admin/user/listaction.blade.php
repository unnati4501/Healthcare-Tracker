@permission('update-user')
<a class="action-icon" target="_blank" href="{{ route('admin.users.edit', $record->id) }}" title="{{ trans('user.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('mark-user')
@if($record->is_blocked == 1)
<a class="action-icon text-info" href="{{ route('admin.users.status', $record->id) }}" title="{{ trans('user.buttons.unblock') }}">
    <i class="far fa-lock-open-alt">
    </i>
</a>
@else
<a class="action-icon danger" href="{{ route('admin.users.status',$record->id) }}" title="{{ trans('user.buttons.block') }}">
    <i class="far fa-lock-alt">
    </i>
</a>
@endif
@endauth
@if($record->roleSlug == 'wellbeing_specialist' || $record->roleSlug == 'health_coach')
@if((!empty($wsUser) && $wsUser->is_cronofy == true) || (!empty($wcUser) && $wcUser->is_cronofy == true))
<a class="action-icon text-success" href="javascript::void(0)" title="{{ trans('user.buttons.verified') }}">
    <i class="fa fa-check">
    </i>
</a>
@else
<a class="action-icon danger" href="javascript::void(0)" title="{{ trans('user.buttons.not_verified') }}">
    <i class="fa fa-exclamation">
    </i>
</a>
@endif
@endif
@permission('delete-user')
@if($record->roleSlug == 'wellbeing_specialist')
<a class="action-icon danger softDelete" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('user.buttons.archive') }}">
    <i class="far fa-archive">
    </i>
</a>
@else
<a class="action-icon danger userDelete" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('user.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth
@permission('disconnect-user')
@if($record->devices->first() != null)
<a class="action-icon userDisconnect" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('user.buttons.disconnect') }}">
    <i class="far fa-unlink">
    </i>
</a>
@endif
@endauth
@permission('view-tracker-history')
@if($role->group == 'zevo' && $record->step_sync_count > 0)
<a class="action-icon" href="{{ route('admin.users.tracker-history', $record->id) }}" title="{{ trans('user.buttons.tracker_history') }}">
    <i class="far fa-analytics">
    </i>
</a>
@endif
@endauth
{{--
@permission('calendar-wellbeing-specialist')
@if($record->is_coach == 1)
<a class="action-icon" href="javascript:void(0);" title="Check Availability">
    <i class="far fa-calendar-alt">
    </i>
</a>
@endif
@endauth
--}}

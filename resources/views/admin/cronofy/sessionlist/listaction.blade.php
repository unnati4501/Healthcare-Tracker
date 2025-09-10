@permission('view-sessions')
@if(!$record->is_group)
    @if($role->slug == 'wellbeing_specialist' || $role->slug == 'wellbeing_team_lead')
    <a class="action-icon" href="{{ route('admin.cronofy.sessions.show', $record->id) }}" title="{{ trans('Cronofy.session_list.buttons.tooltips.edit') }}">
        <i class="far fa-edit">
        </i>
    </a>
    @elseif($role->slug == 'super_admin')
    <a class="action-icon" href="{{ route('admin.cronofy.sessions.email-logs', $record->id) }}" title="{{ trans('Cronofy.session_list.buttons.tooltips.view') }}">
        <i class="far fa-eye">
        </i>
    </a>
    @endif
@else
@if((auth()->user()->id == $record->created_by) && $role->slug != 'super_admin')
<a class="action-icon" href="{{ route('admin.cronofy.sessions.edit', $record->id) }}" title="{{ trans('Cronofy.session_list.buttons.tooltips.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endif
@endif
@if($status == 'Upcoming' && $role->slug == 'wellbeing_specialist' && !$record->is_group)
<a class="action-icon" href="{{ route('admin.cronofy.sessions.email-logs', $record->id) }}" title="{{ trans('Cronofy.session_list.buttons.tooltips.email') }}">
    <i class="far fa-envelope">
    </i>
</a>
@endif
@endauth
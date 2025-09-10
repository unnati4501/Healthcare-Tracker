@if(!$record->is_group)
@permission('view-clients')
<a class="action-icon" href="{{ route('admin.cronofy.clientlist.details', $record->id) }}" title="{{ trans('buttons.general.tooltip.view') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('add-occupational-health-referral')
<a class="action-icon" href="{{ route('admin.cronofy.clientlist.health-referral', $record->id) }}" title="{{ trans('buttons.general.tooltip.occupation_health_referral') }}">
    <i class="far fa-plus">
    </i>
</a>
@endauth
@endif
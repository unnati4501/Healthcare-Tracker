@permission('view-sessions')
@if(!is_null($record->cancelled_at) && !is_null($record->cancelled_by))
<a class="action-icon view-cancel-details" data-record="{{ json_encode(['cancelled_by' => $record->cancelledBy->full_name, 'cancelled_at' => $record->cancelled_at, 'cancelled_reason' => $record->cancelled_reason]) }}" href="javascript:void(0);" title="{{ trans('buttons.general.tooltip.view') }}">
    <i class="far fa-eye">
    </i>
</a>
@endif
@endauth
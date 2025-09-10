<a class="action-icon" href="{{ route('admin.reports.getUserDailyHistoryData', [
	'logdate' => $logDate,
	'user_id' => $record->user_id,
	'challenge_id' => $challengeInfo->id,
	'type' => $record->type,
	'columnName' => $record->columnName,
	'model_id' => $record->model_id,
	'uom' => $record->uom,
	'challengeStatus' => $challengeStatus,
	]) }}" title="View History">
    <i aria-hidden="true" class="far fa-eye">
    </i>
</a>
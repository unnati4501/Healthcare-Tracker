@permission('update-question-bank')
<a class="action-icon" href="{{ route('admin.zcquestionbank.edit', $record->id) }}" title="{{ trans('survey.zcquestionbank.buttons.edit') }}">
    <i class="far fa-pencil-alt">
    </i>
</a>
@endauth
@permission('view-question-bank')
<a class="action-icon" data-id="{{ $record->id }}" data-order="1" href="javascript:void(0);" id="questionShow" title="{{ trans('survey.zcquestionbank.buttons.view') }}">
    <i class="far fa-eye">
    </i>
</a>
@endauth
@permission('delete-question-bank')
@if($record->status != 1)
<a class="action-icon danger" data-id="{{ $record->id }}" href="javascript:void(0);" id="questionDelete" title="{{ trans('survey.zcquestionbank.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth

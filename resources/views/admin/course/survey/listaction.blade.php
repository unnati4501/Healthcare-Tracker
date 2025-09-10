
<a class="action-icon" href="{{ route('admin.masterclass.editSurvey', $record->id) }}" title="{{ trans('masterclass.survey.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@if($record->status == 1)
<a class="action-icon" href="{{ route('admin.masterclass.viewSurvey', $record->id) }}" title="{{ trans('masterclass.survey.buttons.view') }}">
    <i class="far fa-eye">
    </i>
</a>
@endif

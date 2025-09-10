@if($record->status == 1)
<span class="text-success">
    {{ trans('masterclass.survey.buttons.published') }}
</span>
@elseif($record->surveyCourse->course_status == 0)
<span class="text-info" data-placement="bottom" data-bs-toggle="tooltip" title="{{ trans('masterclass.survey.tooltip.publish') }}">
    {{ trans('masterclass.survey.buttons.unpublish') }}
</span>
@elseif($record->surveyCourse->course_status == 1 && $record->status == 0)
<a class="btn btn-primary badge-btn publishsurvey" data-id="{{ $record->id }}" href="javascript:void(0);">
    {{ trans('masterclass.survey.buttons.publish') }}
</a>
@endif

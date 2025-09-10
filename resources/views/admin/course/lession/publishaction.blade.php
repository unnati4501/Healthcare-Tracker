@if($record->status == 1)
<span class="text-success">
    {{ trans('masterclass.lesson.buttons.published') }}
</span>
@elseif($record->course->course_status == 0)
<span class="text-info" data-placement="bottom" data-bs-toggle="tooltip" title="{{ trans('masterclass.lesson.tooltip.publish') }}">
    {{ trans('masterclass.lesson.buttons.unpublish') }}
</span>
@elseif($record->course->course_status == 1 && $record->status == 0)
<a class="btn btn-primary badge-btn publishlesson" data-id="{{ $record->id }}" href="javascript:void(0);">
    {{ trans('masterclass.lesson.buttons.publish') }}
</a>
@endif

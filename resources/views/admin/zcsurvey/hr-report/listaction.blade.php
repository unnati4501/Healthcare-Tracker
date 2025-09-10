@if(!empty($record->id))
@permission('view-course')
<a class="btn btn-sm btn-outline-primary animated bounceIn slow" href="{{ route('admin.masterclass.view', $record->id) }}" title="{{ trans('labels.course.preview_lessons') }}">
    <i aria-hidden="true" class="fal fa-eye">
    </i>
</a>
@endauth
@permission('update-course')
<a class="btn btn-sm btn-outline-primary animated bounceIn slow" href="{{ route('admin.masterclass.edit', $record->id) }}" title="{{ trans('labels.buttons.edit_record') }}">
    <i aria-hidden="true" class="fal fa-pencil-alt">
    </i>
</a>
@endauth
@permission('manage-course-modules')
<a class="btn btn-sm btn-outline-primary animated bounceIn slow" href="{{ route('admin.masterclass.manageLessions', $record->id) }}" title="{{ trans('labels.course.manage_lesson') }}">
    <i aria-hidden="true" class="fal fa-tasks">
    </i>
</a>
@endauth
@permission('delete-course')
<a class="btn btn-sm btn-outline-danger animated bounceIn slow delete-toast" data-id="{{$record->id}}" href="javascript:void(0);" id="courseDelete" title="{{ trans('labels.buttons.delete_record') }}">
    <i aria-hidden="true" class="fal fa-trash-alt">
    </i>
</a>
@endauth
@endif

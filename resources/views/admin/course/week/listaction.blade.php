<a class="btn btn-sm btn-outline-primary animated bounceIn slow" href="{{route('admin.courses.editModule', $record->id)}}" title="{{trans('labels.buttons.edit_record')}}">
    <i aria-hidden="true" class="fal fa-pencil-alt">
    </i>
</a>
<a class="btn btn-sm btn-outline-primary animated bounceIn slow" href="{{route('admin.courses.manageLessions', [$record->course_id, $record->id])}}" title="{{trans('labels.course.manage_lession')}}">
    <i aria-hidden="true" class="fal fa-tasks">
    </i>
</a>
@if(!$record->is_default)
<a class="btn btn-sm btn-outline-danger animated bounceIn slow delete-toast" data-id="{{$record->id}}" href="javaScript:void(0)" id="courseDelete" title="Delete Record">
    <i aria-hidden="true" class="fal fa-trash-alt">
    </i>
</a>
@endif

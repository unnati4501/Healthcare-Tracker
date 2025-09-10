@if($record->status == 1)
	<span class='badge badge-pill badge-success'>{{ trans('labels.course.published') }}</span>
@else
	@if($courseData->status == 0)
		<a class="btn btn-icon btn-sm btn-outline-secondary animated bounceIn slow delete-toast" href="javaScript:void(0)" title="Publish Module">Publish</a>
	@else
		<a class="btn btn-icon btn-sm btn-outline-primary animated bounceIn slow delete-toast" data-id="{{$record->id}}" href="javaScript:void(0)" id="publishCourseModule" title="Publish Course Module">Publish</a>
	@endif
@endif
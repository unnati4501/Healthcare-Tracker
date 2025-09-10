
@permission('update-project-survey')
@if($stDate > $now)
	<a class="action-icon" href="{{route('admin.projectsurvey.edit', $record->id)}}" title="{{trans('labels.buttons.edit_record')}}">
	   <i aria-hidden="true" class="far fa-edit"></i>
	</a>
@endif
@endauth

@permission('view-project-survey')
	<a class="action-icon" href="{{route('admin.projectsurvey.view', $record->id)}}" title="{{trans('labels.buttons.view_record')}}">
	   <i aria-hidden="true" class="far fa-eye"></i>
	</a>
@endauth


@permission('delete-project-survey')
@if(!($stDate <= $now && $edDate >= $now))
<a href="javaScript:void(0)" class="action-icon danger" title="{{trans('labels.buttons.delete_record')}}" data-id="{{$record->id}}" id="projectSurveyDelete">
    <i class="far fa-trash-alt" aria-hidden="true" ></i>
</a>
@endif
@endauth

@permission('create-project-survey')
@if($record->type == "public" && !empty($record->public_survey_url) && $now <= $edDate)
	<a href="javaScript:void(0)" class="action-icon" title="Copy Link" id="copySurveyLink" data-url="{{ $record->public_survey_url }}">
		<i class="fal fa-link" aria-hidden="true" ></i>
	</a>
@endif
@endauth
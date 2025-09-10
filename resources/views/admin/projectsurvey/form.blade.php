<div class="col-xl-4">
    <div class="form-group">
        {{ Form::label('start_date', trans('customersatisfaction.projectsurvey.form.labels.start_date')) }}
        @if(!empty($surveyData->start_date))
            {{ Form::text('start_date', date('Y-m-d',strtotime($surveyData->start_date)), ['class' => 'form-control ', 'id' => 'start_date','autocomplete'=>'off']) }}
        @else
            {{ Form::text('start_date', old('start_date'), ['class' => 'form-control ', 'id' => 'start_date','autocomplete'=>'off']) }}
        @endif
    </div>
</div>
<div class="col-xl-4">
    <div class="form-group">
        {{ Form::label('end_date', trans('customersatisfaction.projectsurvey.form.labels.end_date')) }}
        @if(!empty($surveyData->end_date))
            {{ Form::text('end_date', date('Y-m-d',strtotime($surveyData->end_date)), ['class' => 'form-control ', 'id' => 'end_date','autocomplete'=>'off']) }}
        @else
            {{ Form::text('end_date', old('end_date'), ['class' => 'form-control ', 'id' => 'end_date','autocomplete'=>'off']) }}
        @endif
    </div>
</div>
<div class="col-xl-4">
    <div class="form-group">
        <label for="">{{trans('customersatisfaction.projectsurvey.form.labels.name')}}</label>
        @if(!empty($surveyData->title))
            {{ Form::text('project_name', old('project_name',$surveyData->title), ['class' => 'form-control', 'placeholder' => trans('customersatisfaction.projectsurvey.form.placeholder.enter_project_name'), 'id' => 'project_name', 'autocomplete' => 'off']) }}
        @else
            {{ Form::text('project_name', old('project_name'), ['class' => 'form-control', 'placeholder' => trans('customersatisfaction.projectsurvey.form.placeholder.enter_project_name'), 'id' => 'project_name', 'autocomplete' => 'off']) }}
        @endif
    </div>
</div>
<div class="col-xl-4">
    <div class="form-group">
        {{ Form::label('project_type', trans('customersatisfaction.projectsurvey.form.labels.type')) }}
        @if(!empty($surveyData->type))
            {{ Form::select('project_type', $projectSurveyType, $surveyData->type, ['class' => 'form-control select2', 'id'=>'project_type', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('customersatisfaction.projectsurvey.form.placeholder.select_project_type'), 'data-placeholder' => trans('customersatisfaction.projectsurvey.form.placeholder.select_project_type'), 'data-allow-clear' => 'true','disabled' => 'true']) }}
        @else
            {{ Form::select('project_type', $projectSurveyType, old('project_type'), ['class' => 'form-control select2', 'id'=>'project_type', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('customersatisfaction.projectsurvey.form.placeholder.select_project_type'), 'data-placeholder' => trans('customersatisfaction.projectsurvey.form.placeholder.select_project_type'), 'data-allow-clear' => 'true']) }}
        @endif
    </div>
</div>
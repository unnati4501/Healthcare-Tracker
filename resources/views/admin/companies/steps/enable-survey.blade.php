<div class="row">
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('is_premium', trans('labels.company.is_premium'))}}
            <div>
                <label class="custom-checkbox">
                    {{ trans('labels.company.is_premium') }}
                    {{ Form::checkbox('is_premium', null, old('is_premium', (!empty($survey) && $survey->is_premium ==  1)), ['class' => 'form-control', 'id' => 'is_premium']) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('survey', trans('labels.company.survey_selection')) }}
            <select id="survey" name="survey" class="form-control select2" data-placeholder="Select survey" placeholder="Select survey" data-allow-clear="true">
                <option value="">Select survey</option>
                @foreach($surveys as $key => $surveyop)
                    @php
                        $selected = ((isset($survey) && $key == $survey->survey_id) ? "selected='selected'" : "");
                    @endphp
                    <option value="{{ $key }}" {{ $selected }}>{{ $surveyop }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('survey_frequency', trans('labels.company.survey_frequency')) }}
            {{ Form::select('survey_frequency', $survey_frequency, old('survey_frequency', ($survey->survey_frequency ?? null)), ['class' => 'form-control select2', 'id' => 'survey_frequency', 'placeholder' => 'Select survey frequency', 'data-placeholder' => 'Select survey frequency', 'data-allow-clear' => 'true']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('zcsurvey_on_email', trans('labels.company.audit_survey_emails'))}}
            <div>
                <label class="custom-radio">
                    {{ trans('buttons.general.yes') }}
                    {{ Form::radio('zcsurvey_on_email', 'on', old('zcsurvey_on_email', (!empty($recordData) && $recordData->zcsurvey_on_email ==  1)), ['id' => 'zcsurvey_on_email_on', 'class' => 'form-control']) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
                <label class="custom-radio">
                    {{ trans('buttons.general.no') }}
                    {{ Form::radio('zcsurvey_on_email', 'off', old('zcsurvey_on_email', ((!empty($recordData) && $recordData->zcsurvey_on_email ==  0) ? true : (!$edit ? true : false))), ['id' => 'zcsurvey_on_email_off', 'class' => 'form-control']) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('survey_roll_out_day', trans('labels.company.survey_roll_out_day')) }}
            {{ Form::select('survey_roll_out_day', $survey_days, old('survey_roll_out_day', ($survey->survey_roll_out_day ?? null)), ['class' => 'form-control select2', 'id'=>'survey_roll_out_day', 'placeholder' => 'Select survey day', 'data-placeholder' => 'Select survey day', 'data-allow-clear' => 'true']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group bootstrap-timepicker">
            {{ Form::label('survey_roll_out_time', trans('labels.company.survey_roll_out_time')) }}
            {{ Form::text('survey_roll_out_time', old('survey_roll_out_time', ($survey->survey_roll_out_time ?? null)), ['id' => 'survey_roll_out_time', 'class' => 'form-control', 'placeholder' => 'Select survey rollout time', 'readonly' => true]) }}
        </div>
    </div>
    {{-- <div class="col-lg-6 col-xl-4">
        <div class="form-group bootstrap-timepicker">
        </div>
    </div> --}}
    @if($edit && !empty($surveyRollOutData['lastSurveyRollOutDay']) && !empty($surveyRollOutData['lastSurveyExpiredDay']))
    <div class="col-lg-6 col-xl-6">
        <div class="callout">
            <div class="m-0">
                <div class="fw-bold upcomingSurveyDetails">
                    <span class="text-primary">
                        Previous Rollout : 
                    </span> 
                    <span style="font-size:13px;">
                        {{ $surveyRollOutData['lastSurveyRollOutDay'] }}
                    </span>
                    <br/>
                    <span class="text-primary">
                        Previous Expiry : 
                    </span> 
                    <span style="font-size:13px;">
                        {{ $surveyRollOutData['lastSurveyExpiredDay'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($edit && !empty($surveyRollOutData) && $surveyRollOutData['upcomingRollOutDay']!=null && $surveyRollOutData['upcomingExpiredDay']!=null)
    <div class="col-lg-6 col-xl-6">
        <div class="callout">
            <div class="m-0">
                <div class="fw-bold upcomingSurveyDetails">
                    <span class="text-primary">
                        Upcoming Rollout : 
                    </span> 
                    <span id="upRollout" style="font-size:13px;">
                        {{ $surveyRollOutData['upcomingRollOutDay'] }}
                    </span>
                    <br/>
                    <span class="text-primary">
                        Upcoming Expiry : 
                    </span> 
                    <span id="upExpire" style="font-size:13px;">
                        {{ $surveyRollOutData['upcomingExpiredDay'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    @elseif(($edit && $recordData->subscription_end_date > now()->setTimezone($appTimezone)) || !($edit))
    <div class="col-lg-6 col-xl-6">
        <div class="callout">
            <div class="m-0">
                <div class="fw-bold upcomingSurveyDetails">
                    <span class="text-primary">
                        Upcoming Rollout : 
                    </span> 
                    <span id="upRollout" style="font-size:13px;"></span>
                    <br/>
                    <span class="text-primary">
                        Upcoming Expiry : 
                    </span>
                    <span id="upExpire" style="font-size:13px;"></span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
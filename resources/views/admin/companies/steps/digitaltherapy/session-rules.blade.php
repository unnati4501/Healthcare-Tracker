<div class="col-xl-12">
    <div class="card-inner">
        <h3 class="card-inner-title">
            Session Rules
        </h3>
        <div class="row">
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                    {{ Form::label('get_user_consent', trans('labels.company.digital_therapy.get_user_consent')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('company.digital_therapy.tooltips.get_user_consent')}}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    <div>
                        <label class="custom-checkbox">
                            {{trans('labels.company.digital_therapy.get_user_consent')}}
                            {{ Form::checkbox('get_user_consent', null, old('get_user_consent', (!empty($dtData) && $dtData->consent == 1)), ['id' => 'get_user_consent', 'disabled' => $disableUserConsent]) }}
                            <span class="checkmark">
                            </span>
                            <span class="box-line">
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-xl-12">
    <div class="card-inner">
        <h3 class="card-inner-title">
            Session Rules
        </h3>
        <div class="row">
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                    {{ Form::label('dt_session_update', trans('labels.company.digital_therapy.session_update')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('company.digital_therapy.tooltips.session_update')}}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    {{ Form::select('dt_session_update', ($dtSessionRulesHrs ?? 0), old('dt_session_update', ($dtData->dt_session_update ?? 0)), ['class' => 'form-control select2', 'id'=>'dt_session_update', 'data-allow-clear'=>'false', 'disabled' => $dt_servicemode]) }}
                </div>
            </div>
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                    {{ Form::label('dt_advanced_booking', trans('labels.company.digital_therapy.advanced_booking')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('company.digital_therapy.tooltips.advanced_booking')}}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    {{ Form::select('dt_advanced_booking', ($dtSessionRulesHrs ?? 0), old('dt_advanced_booking', ($dtData->dt_advanced_booking ?? 0)), ['class' => 'form-control select2', 'id'=>'dt_advanced_booking', 'data-allow-clear'=>'false', 'disabled' => $dt_servicemode]) }}
                </div>
            </div>
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                {{ Form::label('future_booking', trans('labels.company.digital_therapy.future_booking')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('company.digital_therapy.tooltips.future_booking')}}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::select('dt_future_booking', ($dtFutureBookingRules ?? 14), old('dt_future_booking', ($dtData->dt_future_booking ?? 14)), ['class' => 'form-control select2', 'id'=>'dt_future_booking', 'data-allow-clear'=>'false', 'disabled' => $dt_servicemode]) }}
                </div>
            </div>
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                {{ Form::label('max_sessions_user', trans('labels.company.digital_therapy.max_sessions_user')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('company.digital_therapy.tooltips.max_sessions_user')}}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::text('dt_max_sessions_user', old('max_sessions_user', ($dtData->dt_max_sessions_user ?? 0)) , ['class' => 'form-control', 'placeholder' => 'Max session user', 'autocomplete' => 'off', 'maxLength'=>'5', 'disabled' => $dt_servicemode, 'onkeypress'=> "return event.charCode >= 48 && event.charCode <= 57"]) }}
                </div>
            </div>
            <div class="col-lg-6 col-xl-5">
                <div class="form-group">
                {{ Form::label('max_sessions_company', trans('labels.company.digital_therapy.max_sessions_company')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('company.digital_therapy.tooltips.max_sessions_company')}}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::text('dt_max_sessions_company', old('max_sessions_company', ($dtData->dt_max_sessions_company ?? 0)) , ['class' => 'form-control', 'placeholder' => 'Max session company', 'autocomplete' => 'off', 'maxLength'=>'5', 'disabled' => $dt_servicemode, 'onkeypress'=> "return event.charCode >= 48 && event.charCode <= 57"]) }}
                </div>
            </div>
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                {{ Form::label('emergency_contacts', trans('labels.company.digital_therapy.emergency_contacts')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('company.digital_therapy.tooltips.emergency_contacts')}}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div>
                    <label class="custom-checkbox">
                        {{trans('labels.company.digital_therapy.emergency_contacts')}}
                        {{ Form::checkbox('emergency_contacts', null, old('emergency_contacts', (!empty($dtData) && $dtData->emergency_contacts ==  1)), ['id' => 'emergency_contacts', 'disabled'=>$dt_servicemode]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
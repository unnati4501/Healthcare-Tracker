<div class="row">
    @if($edit && $companyType =='normal')
    <div class="col-lg-6 col-xl-4">
        {{ Form::label(null, null) }}
        <div class="callout">
            <div class="m-0">
                {{ trans('labels.company.code') }} :
                <div class="fw-bold">
                    {{ $recordData->code }}
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('logo', trans('labels.company.logo')) }}
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.logo') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file">
                {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-width' => config('zevolifesettings.imageConversions.company.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.company.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.logo') ]) }}
                {{ Form::label('logo', (!empty($recordData->logo) ? $recordData->logo_name : 'Choose File'), ['class' => 'custom-file-label']) }}
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('name', trans('labels.company.name')) }}
            {{ Form::text('name', old('name', ($recordData->name ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter Name', 'id' => 'name', 'autocomplete' => 'off', "onkeyup" => "validateTitle()"]) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('industry', trans('labels.company.industry')) }}
            {{ Form::select('industry', $industries, old('industry', ($recordData->industry_id ?? null)), ['class' => 'form-control select2', 'id' => 'industry', 'placeholder' => 'Select industry', 'data-placeholder' => 'Select industry']) }}
        </div>
    </div>
    
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('registration_restriction', trans('labels.company.has_domain')) }}
            {{ Form::select('registration_restriction', $registration_restriction, old('registration_restriction', ($recordData->has_domain ?? 0)), ['class' => 'form-control select2', 'id' => 'registration_restriction', 'data-allow-clear' => 'false']) }}
        </div>
    </div>
    
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('size', trans('labels.company.size')) }}
            {{ Form::select('size', $companySize, old('size', ($recordData->size ?? null)), ['class' => 'form-control select2', 'id' => 'size', 'placeholder' => 'Select company size', 'data-placeholder' => 'Select company size', 'autocomplete' => 'off'] ) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('assigned_roles', trans('labels.company.assign_roles')) }}
            {{ Form::select('assigned_roles[]', $resellerRoles, (old("assigned_roles[]") ?? ($selectedRoles ?? [])), ['class' => 'form-control select2', 'id' => 'assigned_roles', 'data-placeholder' => 'Select Roles', 'multiple' => true, 'data-close-on-select' => 'false', 'data-allow-clear'=>'false']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('subscription_start_date', trans('labels.company.subscription_start_date')) }}
            <div class="datepicker-wrap">
                {{ Form::text('subscription_start_date', old('subscription_start_date') ?? ($subscription_start_date ?? ""), ['class' => 'form-control datepicker', 'id' => 'subscription_start_date', 'readonly' => true, 'placeholder' => 'Subscription start date']) }}
                <i class="far fa-calendar">
                </i>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('subscription_end_date', trans('labels.company.subscription_end_date')) }}

            @if($edit && !empty($parent_comapnies) && $recordData->parent_id != null)
            <span class="font-16 qus-sign-tooltip" data-placement="auto" id="subscription_end_date_tooltip" data-bs-toggle="tooltip" title="{{ trans('company.messages.subscription_end_date_message') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            @else
            <span class="font-16 qus-sign-tooltip d-none" data-placement="auto" id="subscription_end_date_tooltip" data-bs-toggle="tooltip" title="{{ trans('company.messages.subscription_end_date_message') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            @endif
            <div class="datepicker-wrap">
                {{ Form::text('subscription_end_date', old('subscription_end_date') ?? ($subscription_end_date ?? ""), ['class' => 'form-control datepicker', 'id' => 'subscription_end_date', 'readonly' => true, 'placeholder' => 'Subscription end date']) }}
                <i class="far fa-calendar">
                </i>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4" id="companyplandiv">
        <div class="form-group">
            {{ Form::label('companyplan', trans('company.form.labels.company_plan')) }}
            {{ Form::select('companyplan', $companyplans, old('companyplan', ($selectedPlan ?? 1)), ['class' => 'form-control select2', 'id' => 'companyplan', 'placeholder' => trans('company.form.placeholder.company_plan'), 'data-placeholder' => trans('company.form.placeholder.company_plan'), 'autocomplete' => 'off', 'disabled' => false] ) }}
            {{-- @if($disabledPortalCompanyPlan == true)
            {{ Form::hidden('companyplan', old('companyplan', ($selectedPlan ?? 1)),['id' => 'companyplanHidden', 'disabled' => true]) }}
            @endif --}}
            {{ Form::hidden('companyplanSlug', null,['id' => 'companyplanSlug']) }}
            {{ Form::hidden('dtExistsHidden', false,['id' => 'dtExistsHidden']) }}
            
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('description', trans('labels.company.brief_info')) }}
            {{ Form::textarea('description', old('description', ($recordData->description ?? null)), ['id' => 'description', 'rows' => 5, 'class' => 'form-control', 'placeholder' => 'Enter Brief Information', 'spellcheck'=>'false']) }}
        </div>
    </div>
</div>
<div class="card-inner" id="custom_flags">
    <h3 class="card-inner-title">
        {{ trans('labels.company.custom_settings') }}
    </h3>
    <div class="row">
        @if(($user_role->group == 'zevo' && !$edit) || ($edit && $user_role->group == 'zevo' && (!is_null($recordData->parent_id) || (is_null($recordData->parent_id) && $recordData->is_reseller == false))))
            <div class="col-lg-6 col-xl-4" data-allow-app-wrapper="">
                <div class="form-group">
                    <div>
                        <label class="custom-checkbox prevent-events" for="allow_app">
                            {{trans('labels.company.zevo_health_app')}}
                            {{ Form::checkbox('allow_app', true, old('allow_app', $mobile_app_value), ['class' => 'form-control', 'id' => 'allow_app', 'disabled' => $disable_mobile_app]) }}
                            <span class="checkmark">
                            </span>
                            <span class="box-line">
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        @endif
        {{-- @if($user_role->group == 'zevo')
            <div class="col-lg-6 col-xl-4 companiesplan" {{ ($isShowPlan) ? 'style=display:none' : '' }}>
                <div class="form-group">
                    {{ Form::label('', trans('labels.company.is_zendesk')) }}
                    <div>
                        <label class="custom-checkbox" for="is_intercom">
                            {{ trans('labels.company.is_zendesk') }}
                            {{ Form::checkbox('is_intercom', null, old('is_intercom', ((!empty($recordData) && $recordData->is_intercom ==  1) || $edit == false)), ['class' => 'form-control', 'id' => 'is_intercom']) }}
                            <span class="checkmark">
                            </span>
                            <span class="box-line">
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        @endif --}}
        <div class="col-lg-6 col-xl-4 companiesplan" {{ ($isShowPlan) ? 'style=display:none' : '' }}>
            <div class="form-group">
                <div>
                    <label class="custom-checkbox" for="faqs">
                        {{ trans('labels.company.faqs') }}
                        {{ Form::checkbox('is_faqs', null, old('is_faqs', ((!empty($recordData) && $recordData->is_faqs ==  1) || $edit == false)), ['class' => 'form-control', 'id' => 'faqs']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 companiesplan" {{ ($isShowPlan) ? 'style=display:none' : '' }}>
            <div class="form-group">
                <div>
                    <label class="custom-checkbox" for="support">
                        {{ trans('labels.company.is_support') }}
                        {{ Form::checkbox('is_support', null, old('is_support', ((!empty($recordData) && $recordData->is_eap ==  1) || $edit == false)), ['class' => 'form-control', 'id' => 'support']) }}
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

                <div>
                    <label class="custom-checkbox" for="is_branding">
                        {{ trans('labels.company.is_branding') }}
                        {{ Form::checkbox('is_branding', null, old('is_branding', $has_branding), ['class' => 'form-control', 'id' => 'is_branding', 'disabled' => $has_branding]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @if($edit == true && $has_branding)
                    <span class="font-16 qus-sign-tooltip" data-placement="auto" data-bs-toggle="tooltip" title="This field will not be editable.">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    {{ Form::hidden('is_branding', 'on') }}
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <div>
                    <label class="custom-checkbox" for="enable_survey">
                        {{ trans('labels.company.enable_survey') }}
                        {{ Form::checkbox('enable_survey', null, old('enable_survey', $enable_survey), ['class' => 'form-control', 'id' => 'enable_survey']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    {{-- @if($disable_survey)
                    {{ Form::hidden('enable_survey', 'on') }}
                    @endif --}}
                </div>
            </div>
        </div>
        @if($companyType == 'reseller' || ($companyType == 'normal' && $edit == true))
            <div class="col-lg-6 col-xl-4 d-none" id="eap_tab_counsellor1">
                <div class="form-group">
                    <div>
                        <label class="custom-checkbox" for="eap_tab">
                            {{ trans('labels.company.digital_therapy.title') }}
                            @if($totalSessions > 0)
                            {{ Form::checkbox('eap_tab', null, old('eap_tab', (!empty($recordData) && $recordData->eap_tab ==  1)), ['class' => 'form-control', 'id' => 'eap_tab', 'disabled' => true]) }}
                            @else
                            {{ Form::checkbox('eap_tab', null, old('eap_tab', (!empty($recordData) && $recordData->eap_tab ==  1)), ['class' => 'form-control', 'id' => 'eap_tab', 'disabled' => $dt_servicemode]) }}
                            @endif
                            <span class="checkmark">
                            </span>
                            <span class="box-line">
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        @endif
        @if($companyType == 'reseller' || $companyType == 'normal')
        <div class="col-lg-6 col-xl-4 d-none" id="hide-content">
            <div class="form-group">
                <div>
                    <label class="custom-checkbox" for="hidecontent">
                        {{ trans('labels.company.hide_content') }}
                        {{ Form::checkbox('hidecontent', 1, old('hidecontent', $hide_content), ['class' => 'form-control', 'id' => 'hidecontent', 'disabled' => ($companyType == 'normal')]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
        </div>
        @endif
        @if(($user_role->group == 'zevo' && !$edit) || ($edit && is_null($recordData->parent_id) && !$recordData->is_reseller))
            <div class="col-lg-6 col-xl-4 companiesplan" {{ ($isShowPlan) ? 'style=display:none' : '' }} id="enable_event_wrapper">
                <div class="form-group">
                    
                    <div>
                        <label class="custom-checkbox" for="enable_event">
                            {{ Form::checkbox('enable_event', null, old('enable_event', (!empty($recordData) && $recordData->enable_event ==  1)), ['class' => 'form-control', 'id' => 'enable_event', 'disabled' => $disableEvent]) }}
                            <span class="checkmark">
                            </span>
                            <span class="box-line">
                            </span>
                        </label>
                        @if($edit == true && $disableEvent)
                        <span class="font-16 qus-sign-tooltip" data-placement="auto" data-bs-toggle="tooltip" title="This field will not be editable until all the upcoming event get completed.">
                            <i aria-hidden="true" class="far fa-info-circle text-primary">
                            </i>
                        </span>
                        {{ Form::hidden('is_branding', 'on') }}
                        @endif
                    </div>
                </div>
            </div>
        @endif
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <div>
                    <label class="custom-checkbox" for="disable_sso">
                        {{ trans('company.form.labels.disable_sso') }}
                        {{ Form::checkbox('disable_sso', null, old('disable_sso', $disable_sso), ['class' => 'form-control', 'id' => 'disable_sso']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
        </div>
        @if($companyType == 'reseller' || $companyType == 'normal')
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <div>
                    <label class="custom-checkbox" for="exclude_gender_and_dob">
                        {{ trans('company.form.labels.exclude_gender_and_dob') }}
                        {{ Form::checkbox('exclude_gender_and_dob', null , old('exclude_gender_and_dob', ($branding->exclude_gender_and_dob ?? false)), ['class' => 'form-control', 'id' => 'exclude_gender_and_dob', 'disabled' => $excludeGenderAndDob]) }}
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
                <div>
                    <label class="custom-checkbox" for="manage_the_design_change">
                        {{ trans('company.form.labels.manage_the_design_change') }}
                        {{ Form::checkbox('manage_the_design_change', null , old('manage_the_design_change', ($branding->manage_the_design_change ?? false)), ['class' => 'form-control', 'id' => 'manage_the_design_change', 'disabled' => $manageTheDesignChange]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@if($user_role->group == 'zevo')
<div class="row">
    <div class="col-lg-6 col-xl-4">
        @if(!$edit)
        <div class="form-group">
            {{ Form::label('is_reseller', 'Is Reseller?') }}
            <div>
                <label class="custom-radio" for="is_reseller_yes">
                    Yes
                    {{ Form::radio('is_reseller', 'yes', ('yes' == request()->get('is_reseller')), ['class' => 'custom-control-input', 'id' => 'is_reseller_yes', (($companyType=='zevo') ? "disabled" : "")]) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
                <label class="custom-radio" for="is_reseller_no">
                    No
                    {{ Form::radio('is_reseller', 'no', (('no' == request()->get('is_reseller')) || is_null(request()->get('is_reseller'))), ['class' => 'custom-control-input', 'id' => 'is_reseller_no']) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
                <span id="reseller_loader" style="display: none;">
                    <i class="fas fa-spinner fa-lg fa-spin">
                    </i>
                    <span class="ms-1">
                        Loading data...
                    </span>
                </span>
            </div>
        </div>
        @else
        <div class="callout">
            <div class="m-0">
                {{ __('Is Reseller? ') }}
                <div class="fw-bold">
                    {{ (($recordData->is_reseller) ? "Yes" : "No") }}
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="col-lg-6 col-xl-4">
        @if(!$edit)
        <div class="form-group" data-parent-co-wrapper="" style="display: block;">
            {{ Form::label('parent_company', 'Parent Company') }}
            @if($companyType=='reseller')
            {{ Form::select('parent_company', $parent_comapnies, ($recordData->parent_id ?? request()->get('parent_company')), ['class' => 'form-control select2','placeholder' => 'Select parent company','data-placeholder' => 'Select parent company','id' => 'parent_company', 'data-allow-clear' => 'false',(($companyType=='zevo') ? "disabled" : "")] ) }}
            @endif
            @if($companyType=='zevo')
            {{ Form::select('parent_company', $parent_comapnies, ($recordData->parent_id ?? request()->get('parent_company')), ['class' => 'form-control select2', 'id' => 'parent_company', 'data-allow-clear' => 'false',(($companyType=='zevo') ? "disabled" : "")] ) }}
            {{ Form::hidden('parent_company', 'zevo') }}
            @endif
            <div class="mt-1" id="parent_co_loader" style="display: none;">
                <i class="fas fa-spinner fa-lg fa-spin">
                </i>
                <span class="ms-1">
                    Loading data...
                </span>
            </div>
        </div>
        @elseif($edit && !empty($parent_comapnies))
        <div class="callout">
            <div class="m-0">
                {{ __('Parent Company') }}
                <div class="fw-bold">
                    {{ $parent_comapnies }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@else
{{ Form::hidden('is_reseller', 'no') }}
{{ Form::hidden('parent_company', $parent_comapnies) }}
@endif
<div class="card-inner">
    <h3 class="card-inner-title">
        Company Details
    </h3>
    <div class="row">
        @if($edit)
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
        {{-- @if($user_role->group == 'zevo') --}}
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('email_header', trans('company.form.labels.email_header')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.email_header') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                @if(!empty($recordData) && !empty($recordData->email_header) && $companyType!='normal')
                <span class="font-16 float-end">
                    <a class="badge bg-secondary remove-media" data-action="email_header" data-text="email header" href="javascript:void(0);" title="{{ trans('company.form.placeholder.remove_email_header') }}">
                        <i aria-hidden="true" class="fa fa-times">
                        </i>
                        {{ trans('company.form.labels.remove_email_header') }}
                    </a>
                </span>
                @endif
                <div class="custom-file custom-file-preview">
                    {{ Form::file('email_header', ['class' => 'custom-file-input', 'id' => 'email_header', 'data-width' => config('zevolifesettings.imageConversions.company.email_header.width'), 'data-height' => config('zevolifesettings.imageConversions.company.email_header.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.email_header'), 'data-round' => 'yes', 'data-previewelement' => '#emailheader_preview','disabled'=>$disableEmailHeader]) }}
                    {{-- {{ Form::label('email_header', (!empty($recordData->email_header) ? $recordData->email_header_name : 'Choose File'), ['class' => 'custom-file-label']) }} --}}
                    @if(!empty($parentCompanyEmailHeader) && $companyType == 'normal' && !($edit))
                    <label class="file-preview-img" for="email_header">
                        <img height="200" id="emailheader_preview" src="{{ (!empty($parentCompanyEmailHeaderUrl) ? $parentCompanyEmailHeaderUrl['url'] : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('email_header', (!empty($parentCompanyEmailHeader->name) ? $parentCompanyEmailHeader->name : 'Choose File'), ['class' => 'custom-file-label']) }}
                    @else
                    <label class="file-preview-img" for="email_header">
                        <img height="200" id="emailheader_preview" src="{{ (!empty($recordData) ? $recordData->email_header : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('email_header', (!empty($recordData->email_header) ? $recordData->email_header_name : 'Choose File'), ['class' => 'custom-file-label']) }}
                    @endif
                </div>
            </div>
        </div>
        {{-- @endif --}}
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('registration_restriction', trans('labels.company.has_domain')) }}
                {{ Form::select('registration_restriction', $registration_restriction, old('registration_restriction', ($recordData->has_domain ?? 0)), ['class' => 'form-control select2', 'id' => 'registration_restriction', 'data-allow-clear' => 'false']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('name', trans('labels.company.name')) }}
                {{ Form::text('name', old('name', ($recordData->name ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter Name', 'id' => 'name', 'autocomplete' => 'off']) }}
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
                {{ Form::label('size', trans('labels.company.size')) }}
                {{ Form::select('size', $companySize, old('size', ($recordData->size ?? null)), ['class' => 'form-control select2', 'id' => 'size', 'placeholder' => 'Select company size', 'data-placeholder' => 'Select company size', 'autocomplete' => 'off'] ) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('assigned_roles', trans('labels.company.assign_roles')) }}
                {{ Form::select('assigned_roles[]', $resellerRoles, (old("assigned_roles[]") ?? ($selectedRoles ?? [])), ['class' => 'form-control select2', 'id' => 'assigned_roles', 'data-placeholder' => 'Select Roles', 'multiple' => true, 'data-close-on-select' => 'false']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('description', trans('labels.company.brief_info')) }}
                {{ Form::textarea('description', old('description', ($recordData->description ?? null)), ['id' => 'description', 'rows' => 5, 'class' => 'form-control', 'placeholder' => 'Enter Brief Information', 'spellcheck'=>'false']) }}
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
                <span class="font-16 qus-sign-tooltip hide" data-placement="auto" id="subscription_end_date_tooltip" data-bs-toggle="tooltip" title="{{ trans('company.messages.subscription_end_date_message') }}">
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
        <div class="col-lg-6 col-xl-4" id="companyplandiv" {{ (!$isShowPlan) ? 'style=display:none' : '' }}>
            <div class="form-group">
                {{ Form::label('companyplan', trans('company.form.labels.company_plan')) }}
                {{ Form::select('companyplan', $companyplans, old('companyplan', ($selectedPlan ?? 1)), ['class' => 'form-control select2', 'id' => 'companyplan', 'placeholder' => trans('company.form.placeholder.company_plan'), 'data-placeholder' => trans('company.form.placeholder.company_plan'), 'autocomplete' => 'off'] ) }}
            </div>
        </div>
        @if(($user_role->group == 'zevo' && !$edit) || ($edit && $user_role->group == 'zevo' && !$recordData->is_reseller))
        <div class="col-lg-6 col-xl-4" data-allow-app-wrapper="">
            <div class="form-group">
                {{ Form::label('', 'Allow App') }}
                <div>
                    <label class="custom-checkbox prevent-events" for="allow_app">
                        {{ config('app.name') }} Mobile App
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
        @if($user_role->group == 'zevo')
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
        @endif
        @if($user_role->group == 'reseller')
        <div class="col-lg-6 col-xl-4 companiesplan" }}>
            <div class="form-group">
                {{ Form::label('', trans('labels.company.is_zendesk')) }}
                <div>
                    <label class="custom-checkbox" for="is_intercom">
                        {{ trans('labels.company.is_zendesk') }}
                        {{ Form::checkbox('is_intercom', null ,$isZendesk, ['class' => 'form-control', 'id' => 'is_intercom', "disabled" => ""]) }}
                        @if($isZendesk == 1)
                            {{ Form::hidden('is_intercom', 'on') }}        
                        @else
                            {{ Form::hidden('is_intercom', 'off') }}        
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
        <div class="col-lg-6 col-xl-4 companiesplan" {{ ($isShowPlan) ? 'style=display:none' : '' }}>
            <div class="form-group">
                {{ Form::label('', trans('labels.company.faqs')) }}
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
                {{ Form::label('', trans('labels.company.eap')) }}
                <div>
                    <label class="custom-checkbox" for="support">
                        {{ trans('labels.company.eap') }}
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
                {{ Form::label('', trans('labels.company.is_branding')) }}
                @if($edit == true && $has_branding)
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-bs-toggle="tooltip" title="This field will not be editable.">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::hidden('is_branding', 'on') }}
                @endif
                <div>
                    <label class="custom-checkbox" for="is_branding">
                        {{ trans('labels.company.is_branding') }}
                        {{ Form::checkbox('is_branding', null, old('is_branding', $has_branding), ['class' => 'form-control', 'id' => 'is_branding', 'disabled' => $has_branding]) }}
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
                {{ Form::label('', trans('labels.company.enable_survey')) }}
                <div>
                    <label class="custom-checkbox" for="enable_survey">
                        {{ trans('labels.company.enable_survey') }}
                        {{ Form::checkbox('enable_survey', null, old('enable_survey', $enable_survey), ['class' => 'form-control', 'id' => 'enable_survey', 'disabled' => $disable_survey]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @if($disable_survey)
                    {{ Form::hidden('enable_survey', 'on') }}
                    @endif
                </div>
            </div>
        </div>
        @if($companyType == 'reseller')
        <div class="col-lg-6 col-xl-4" id="eap_tab_counsellor1">
            <div class="form-group">
                {{ Form::label('', trans('company.form.labels.eap_tab')) }}
                <div>
                    <label class="custom-checkbox" for="eap_tab">
                        {{ trans('company.form.labels.eap_tab') }}
                        @if($totalSessions > 0)
                        {{ Form::checkbox('eap_tab', null, old('eap_tab', (!empty($recordData) && $recordData->eap_tab ==  1)), ['class' => 'form-control', 'id' => 'eap_tab', 'disabled' => true]) }}
                        @else
                        {{ Form::checkbox('eap_tab', null, old('eap_tab', (!empty($recordData) && $recordData->eap_tab ==  1)), ['class' => 'form-control', 'id' => 'eap_tab']) }}
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

        @if(($user_role->group == 'zevo' && !$edit) || ($edit && is_null($recordData->parent_id) && !$recordData->is_reseller))
        <div class="col-lg-6 col-xl-4 companiesplan" {{ ($isShowPlan) ? 'style=display:none' : '' }} id="enable_event_wrapper">
            <div class="form-group">
                {{ Form::label('', trans('company.form.labels.enable_event')) }}
                @if($edit == true && $disableEvent)
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-bs-toggle="tooltip" title="This field will not be editable until all the upcoming event get completed.">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::hidden('is_branding', 'on') }}
                @endif
                <div>
                    <label class="custom-checkbox" for="enable_event">
                        {{ trans('company.form.labels.enable_event') }}
                        {{ Form::checkbox('enable_event', null, old('enable_event', (!empty($recordData) && $recordData->enable_event ==  1)), ['class' => 'form-control', 'id' => 'enable_event', 'disabled' => $disableEvent]) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
        </div>
        @endif

        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('', trans('company.form.labels.disable_sso')) }}
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
    </div>
</div>
<div class="card-inner" id="branding_wrapper" style="display: {{ (($has_branding) ? 'block' : 'none') }};">
    <h3 class="card-inner-title">
        {{ trans('labels.company.is_branding') }}
    </h3>
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('sub_domain', trans('labels.company.sub_domain')) }}
                @if(!$edit)
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-bs-toggle="tooltip" title="This field will not be editable.">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                @elseif($edit == true && !empty($branding->sub_domain))
                {{ Form::hidden('sub_domain', $branding->sub_domain) }}
                @endif
                {{ Form::text('sub_domain', old('sub_domain', ($branding->sub_domain ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter sub domain name', 'id' => 'sub_domain', 'autocomplete' => 'off', 'disabled' => $has_branding]) }}
                <div>
                    {{ Form::label('survey_title', trans('labels.company.domain_branding_url')) }}
                    <a class="m-w-100" href="javascript:void(0);" id="subdomainUrl" target="_self">
                        {{ trans('labels.company.domain_preview_note') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('onboarding_title', trans('labels.company.login_screen_title')) }}
                {{ Form::text('onboarding_title', old('onboarding_title', ($branding->onboarding_title ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter login screen title', 'id' => 'onboarding_title', 'autocomplete' => 'off', 'disabled' => $disable_branding]) }}
            </div>
        </div>
{{--         <div class="col-lg-6 col-xl-4" data-portal-domain-wrapper style="display: {{ ((($edit && ($recordData->is_reseller || (!$recordData->is_reseller && !is_null($recordData->parent_id)))) || (!$edit && $user_role->group == 'reseller')) ? "block" : "none") }};">
            <div class="form-group">
                {{ Form::label('portal_domain', trans('labels.company.portal_domain')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-bs-toggle="tooltip" title="The portal domain must be purchased for the reseller company.">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::select('portal_domain', $portalDomain, old('portal_domain', ($branding->portal_domain ?? null)), ['class' => 'form-control select2', 'id' => 'portal_domain', 'placeholder' => 'Select portal domain', 'data-placeholder' => 'Select portal domain',  'disabled' => $disable_portal, 'data-allow-clear' => 'true']) }}
            </div>
        </div> --}}
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('login_screen_logo', trans('labels.company.login_screen_logo')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.branding_logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                @if(!empty($brandingCo->branding_logo && !$disable_branding))
                <span class="font-16 float-end">
                    <a class="badge bg-secondary remove-media" data-action="login_screen_logo" data-text="logo" href="javascript:void(0);" title="Click to remove branding logo and set to default.">
                        <i aria-hidden="true" class="fa fa-times">
                        </i>
                        Remove
                    </a>
                </span>
                @endif
                <div class="custom-file">
                    {{ Form::file('login_screen_logo', ['class' => 'custom-file-input form-control', 'id' => 'login_screen_logo', 'data-width' => config('zevolifesettings.imageConversions.company.branding_logo.width'), 'data-height' => config('zevolifesettings.imageConversions.company.branding_logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.branding_logo'), 'disabled' => $disable_branding]) }}
                    {{ Form::label('login_screen_logo', (!empty($brandingCo->branding_logo_name) ? $brandingCo->branding_logo_name : "Choose File"), ['class' => 'custom-file-label']) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('login_screen_background', trans('labels.company.login_screen_background')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.branding_login_background') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                @if(!empty($brandingCo->branding_login_background) && !$disable_branding)
                <span class="font-16 float-end">
                    <a class="badge bg-secondary remove-media" data-action="login_screen_background" data-text="background" href="javascript:void(0);" title="Click to remove branding background and set to default.">
                        <i aria-hidden="true" class="fa fa-times">
                        </i>
                        Remove
                    </a>
                </span>
                @endif
                <div class="custom-file">
                    {{ Form::file('login_screen_background', ['class' => 'custom-file-input form-control', 'id' => 'login_screen_background', 'data-width' => config('zevolifesettings.imageConversions.company.branding_login_background.width'), 'data-height' => config('zevolifesettings.imageConversions.company.branding_login_background.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.branding_login_background'), 'disabled' => $disable_branding]) }}
                    {{ Form::label('login_screen_background', (!empty($brandingCo->branding_login_background_name) ? $brandingCo->branding_login_background_name : "Choose File"), ['class' => 'custom-file-label']) }}
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-xl-12">
            <div class="form-group">
                {{ Form::label('onboarding_description', trans('labels.company.login_screen_description')) }}
                {{ Form::textarea('onboarding_description', old('onboarding_description', ($branding->onboarding_description ?? null)), ['id' => 'onboarding_description', 'rows' => 3, 'class' => 'form-control', 'placeholder'=>'Enter login screen description', 'disabled' => $disable_branding]) }}
            </div>
        </div>
    </div>
</div>
<div class="card-inner" id="portal_branding_wrapper" style="display: {{ (($has_branding) ? 'block' : 'none') }};">
    <h3 class="card-inner-title">
        Portal branding
    </h3>
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('portal_title', 'Portal title') }}
                {{ Form::text('portal_title', old('portal_title', ($branding->portal_title ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter portal title', 'id' => 'portal_title', 'autocomplete' => 'off', 'disabled' => $disable_portal]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4" data-portal-domain-wrapper style="display: {{ ((($edit && ($recordData->is_reseller || (!$recordData->is_reseller && !is_null($recordData->parent_id)))) || (!$edit && $user_role->group == 'reseller')) ? "block" : "none") }};">
            <div class="form-group">
                {{ Form::label('portal_domain', trans('labels.company.portal_domain')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-bs-toggle="tooltip" title="The portal domain must be purchased for the reseller company.">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::select('portal_domain', $portalDomain, old('portal_domain', ($branding->portal_domain ?? null)), ['class' => 'form-control select2', 'id' => 'portal_domain', 'placeholder' => 'Select portal domain', 'data-placeholder' => 'Select portal domain',  'disabled' => $disable_portal_domain, 'data-allow-clear' => 'true']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4" data-portal-domain-wrapper style="display: {{ ((($edit && ($recordData->is_reseller || (!$recordData->is_reseller && !is_null($recordData->parent_id)))) || (!$edit && $user_role->group == 'reseller')) ? "block" : "none") }};">
            <div class="form-group">
                {{ Form::label('portal_theme', 'Portal theme') }}
                {{ Form::select('portal_theme', $portalTheme, old('portal_theme', ($branding->portal_theme ?? null)), ['class' => 'form-control select2', 'id' => 'portal_theme', 'placeholder' => 'Select portal domain', 'data-placeholder' => 'Select portal domain', 'data-allow-clear' => 'true', 'disabled' => $disable_portal]) }}
            </div>
        </div>        
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('portal_logo_main', 'Portal logo main') }}
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.portal_logo_main') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
               {{-- @if(!empty($brandingCo->portal_logo_main && !$disable_branding) && $branding)
                 <span class="font-16 float-end">
                    <a class="badge bg-secondary remove-media" data-action="portal_logo_main" data-text="logo" href="javascript:void(0);" title="Click to remove main portal logo and set to default.">
                        <i aria-hidden="true" class="fa fa-times">
                        </i>
                        Remove
                    </a>
                </span>
                @endif --}}
                <div class="custom-file">
                    {{ Form::file('portal_logo_main', ['class' => 'custom-file-input form-control', 'id' => 'portal_logo_main', 'disabled' => $disable_portal, 'data-width' => config('zevolifesettings.imageConversions.company.portal_logo_main.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_logo_main.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_logo_main')]) }}
                    {{ Form::label('portal_logo_main', (!empty($brandingCo->portal_logo_main) && $branding ? $brandingCo->portal_logo_main_name : "Choose File"), ['class' => 'custom-file-label']) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('portal_logo_optional', 'Portal logo optional') }}
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.portal_logo_optional') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
               {{--  @if(!empty($brandingCo->portal_logo_optional && !$disable_branding) && $branding)
                <span class="font-16 float-end">
                    <a class="badge bg-secondary remove-media" data-action="portal_logo_optional" data-text="logo" href="javascript:void(0);" title="Click to remove optional portal logo and set to default.">
                        <i aria-hidden="true" class="fa fa-times">
                        </i>
                        Remove
                    </a>
                </span>
                @endif--}}
                <div class="custom-file">
                    {{ Form::file('portal_logo_optional', ['class' => 'custom-file-input form-control', 'id' => 'portal_logo_optional', 'disabled' => $disable_portal, 'data-width' => config('zevolifesettings.imageConversions.company.portal_logo_optional.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_logo_optional.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_logo_optional')]) }}
                    {{ Form::label('portal_logo_optional', (!empty($brandingCo->portal_logo_optional) && $branding ? $brandingCo->portal_logo_optional_name : "Choose File"), ['class' => 'custom-file-label']) }}
                </div>
            </div>
        </div>        
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('portal_background_image', 'Portal background image') }}
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.portal_background_image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
               {{-- @if(!empty($brandingCo->portal_background_image) && !$disable_branding && $branding)
                 <span class="font-16 float-end">
                    <a class="badge bg-secondary remove-media" data-action="portal_background_image" data-text="background" href="javascript:void(0);" title="Click to remove portal background image and set to default.">
                        <i aria-hidden="true" class="fa fa-times">
                        </i>
                        Remove
                    </a>
                </span>
                @endif--}}
                <div class="custom-file">
                    {{ Form::file('portal_background_image', ['class' => 'custom-file-input form-control', 'id' => 'portal_background_image', 'disabled' => $disable_portal, 'data-width' => config('zevolifesettings.imageConversions.company.portal_background_image.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_background_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_background_image')]) }}
                    {{ Form::label('portal_background_image', (isset($brandingCo->portal_background_image) && $branding ? $brandingCo->portal_background_image_name : "Choose File"), ['class' => 'custom-file-label']) }}
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-xl-12">
            <div class="form-group">
                {{ Form::label('portal_description', trans('labels.company.login_screen_description')) }}
                {{ Form::textarea('portal_description', old('portal_description', ($branding->portal_description ?? null)), ['id' => 'portal_description', 'rows' => 3, 'class' => 'form-control', 'placeholder'=>'Enter login screen description', 'disabled' => $disable_portal]) }}
            </div>
        </div>
    </div>
</div>
<div class="card-inner" id="survey_wrapper" style="display: {{ (($enable_survey) ? 'block' : 'none') }};">
    <h3 class="card-inner-title">
        {{ trans('labels.company.survey_details') }}
    </h3>
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
                {{ Form::text('survey_roll_out_time', old('survey_roll_out_time', ($survey->survey_roll_out_time ?? null)), ['id' => 'survey_roll_out_time', 'class' => 'form-control', 'placeholder' => 'Select surevy rollout time', 'readonly' => true]) }}
            </div>
        </div>
    </div>
</div>
@if(!$edit)
<div class="card-inner">
    <h3 class="card-inner-title">
        Company Moderator Details
    </h3>
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('first_name', trans('labels.company.first_name')) }}
                {{ Form::text('first_name', old('first_name'), ['class' => 'form-control', 'placeholder' => 'Enter First Name', 'id' => 'first_name', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('last_name', trans('labels.company.last_name')) }}
                {{ Form::text('last_name', old('last_name'), ['class' => 'form-control', 'placeholder' => 'Enter Last Name', 'id' => 'last_name', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('email', trans('labels.company.email')) }}
                {{ Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => 'Enter Email', 'id' => 'email', 'autocomplete' => 'off']) }}
            </div>
        </div>
    </div>
</div>
@endif
<div class="card-inner">
    <h3 class="card-inner-title">
        Company Location Details
    </h3>
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('location_name', trans('labels.company.location_name')) }}
                {{ Form::text('location_name', old('location_name', ($companyLocData->name ?? null)) , ['class' => 'form-control', 'placeholder' => 'Enter Location Name', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('postal_code', trans('labels.company.postal_code')) }}
                {{ Form::text('postal_code', old('postal_code', ($companyLocData->postal_code ?? null)) , ['class' => 'form-control', 'placeholder' => 'Enter Postal Code', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('address_line1', trans('labels.company.address_line1')) }}
                {{ Form::text('address_line1', old('address_line1', ($companyLocData->address_line1 ?? null)) , ['class' => 'form-control', 'placeholder' => 'Enter Address Line1', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('address_line2', trans('labels.company.address_line2')) }}
                {{ Form::text('address_line2', old('address_line2', ($companyLocData->address_line2 ?? null)) , ['class' => 'form-control', 'placeholder' => 'Enter Address Line2', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('country', trans('labels.company.country_id')) }}
                {{ Form::select('country', $countries, old('country', ($companyLocData->country_id ?? null)), ['class' => 'form-control select2', 'id' => 'country_id', 'placeholder' => 'Select country', 'data-placeholder' => 'Select country', 'data-dependent' => 'state_id', 'target-data' => 'timezone', 'data-allow-clear' => 'true'] ) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('county', trans('labels.company.state_id')) }}
                @php
                    $countryId =   (!empty($companyLocData->country_id)) ? $companyLocData->country_id : ((!empty(old('country'))) ? old('country') : "");
                    $stateId =   (!empty($companyLocData->state_id)) ? $companyLocData->state_id : ((!empty(old('county'))) ? old('county') : "");
                    $states = (!empty($countryId) && !empty($stateId)) ? getStates($countryId) : [];
                @endphp
                {{ Form::select('county', ($states ?? []), old('county', ($stateId ?? null)), ['class' => 'form-control select2', 'id' => 'state_id', 'placeholder' => 'Select county', 'data-placeholder' => 'Select county', 'data-allow-clear' => 'true']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                </div>
                {{ Form::label('timezone', trans('labels.company.timezone')) }}
                @php
                    $countryId =   (!empty($companyLocData->country_id)) ? $companyLocData->country_id : ((!empty(old('country'))) ? old('country') : "");
                    $timezone =   (!empty($companyLocData->timezone)) ? $companyLocData->timezone : ((!empty(old('timezone'))) ? old('timezone') : "");
                    $timezones = (!empty($countryId) && !empty($timezone)) ? getTimezones($countryId) : [];
                @endphp
                {{ Form::select('timezone', ($timezones ?? []), old('timezone', ($timezone ?? null)), ['class' => 'form-control select2', 'id' => 'timezone', 'placeholder' => 'Select timezone', 'data-placeholder' => 'Select timezone']) }}
        </div>
    </div>
</div>
@if($isShowContentType)
<div class="card-inner">
    <h3 class="card-inner-title">
        Manage Content
    </h3>
    <div>
        <div id="setPermissionList" class="tree-multiselect-box">
            @if(isset($selectedContent))
            <select id="group_content" name="group_content" multiple="multiple" class="form-control" >
                @foreach($masterContentType as $masterKey => $masterData)
                    @foreach($masterData['subcategory'] as $subcategoryKey => $subcategoryData)
                        @foreach($subcategoryData[$masterData['categoryName']] as $key => $value)
                            <option value="{{ $masterData['id'].'-'.$subcategoryData['id'].'-'.$key }}" data-section="{{ $masterData['categoryName'] }}/{{ $subcategoryData['subcategoryName'] }}"  {{ (!empty(old('manage_content', $selectedContent)) && in_array($masterData['id'].'-'.$subcategoryData['id'].'-'.$key, old('manage_content', $selectedContent)))? 'selected' : ''   }} >{{ $value }}</option>
                        @endforeach
                    @endforeach
                @endforeach
            </select>
            @else
            <select id="group_content" name="group_content" multiple="multiple" class="form-control" >
                @foreach($masterContentType as $masterKey => $masterData)
                    @foreach($masterData['subcategory'] as $subcategoryKey => $subcategoryData)
                        @foreach($subcategoryData[$masterData['categoryName']] as $key => $value)
                            <option value="{{ $masterData['id'].'-'.$subcategoryData['id'].'-'.$key }}" data-section="{{ $masterData['categoryName'] }}/{{ $subcategoryData['subcategoryName'] }}"  {{ (!empty(old('manage_content')) && in_array($masterData['id'].'-'.$subcategoryData['id'].'-'.$key, old('manage_content')))? 'selected' : ''   }} >{{ $value }}</option>
                        @endforeach
                    @endforeach
                @endforeach
            </select>
            @endif
        </div>
        <span id="group_content-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
            {{trans('labels.group.group_content_required')}}
        </span>
    </div>
</div>
@endif
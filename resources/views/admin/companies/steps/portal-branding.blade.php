<div class="row">
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('portal_title', 'Portal title') }}
            {{ Form::text('portal_title', old('portal_title', ($branding->portal_title ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter portal title', 'id' => 'portal_title', 'autocomplete' => 'off', 'maxLength'=>'50', 'disabled' => $disable_portal]) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group portal_domain">
            {{ Form::label('portal_domain', trans('labels.company.portal_domain')) }}
            <span class="font-16 qus-sign-tooltip" data-placement="auto" data-bs-toggle="tooltip" title="The portal domain must be purchased for the reseller company.">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            {{ Form::select('portal_domain', $portalDomain, old('portal_domain', ($branding->portal_domain ?? null)), ['class' => 'form-control select2 portal_domain_list', 'id' => 'portal_domain', 'placeholder' => 'Select portal domain', 'data-placeholder' => 'Select portal domain',  'disabled' => $disable_portal_domain, 'data-allow-clear' => 'true']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('portal_theme', 'Portal theme') }}
            {{ Form::select('portal_theme', $portalTheme, old('portal_theme', ($branding->portal_theme ?? null)), ['class' => 'form-control select2', 'id' => 'portal_theme', 'placeholder' => 'Select portal domain', 'data-placeholder' => 'Select portal domain', 'data-allow-clear' => 'true', 'disabled' => $disable_portal]) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('portal_logo_optional', 'Portal Login Logo Left') }}
            <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.portal_logo_optional') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file">
                {{ Form::file('portal_logo_optional', ['class' => 'custom-file-input form-control', 'id' => 'portal_logo_optional', 'disabled' => $disable_portal, 'data-width' => config('zevolifesettings.imageConversions.company.portal_logo_optional.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_logo_optional.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_logo_optional')]) }}
                {{ Form::label('portal_logo_optional', (!empty($brandingCo->portal_logo_optional_name) && $branding ? $brandingCo->portal_logo_optional_name : "Choose File"), ['class' => 'custom-file-label']) }}
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('portal_logo_main', 'Portal Login Logo Right') }}
            <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.portal_logo_main') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file">
                {{ Form::file('portal_logo_main', ['class' => 'custom-file-input form-control', 'id' => 'portal_logo_main', 'disabled' => $disable_portal, 'data-width' => config('zevolifesettings.imageConversions.company.portal_logo_main.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_logo_main.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_logo_main')]) }}
                {{ Form::label('portal_logo_main', (!empty($brandingCo->portal_logo_main_name) && $branding ? $brandingCo->portal_logo_main_name : "Choose File"), ['class' => 'custom-file-label']) }}
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
            <div class="custom-file">
                {{ Form::file('portal_background_image', ['class' => 'custom-file-input form-control', 'id' => 'portal_background_image', 'data-width' => config('zevolifesettings.imageConversions.company.portal_background_image.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_background_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_background_image')]) }}
                {{ Form::label('portal_background_image', (isset($brandingCo->portal_background_image) && $branding ? $brandingCo->portal_background_image_name : "Choose File"), ['class' => 'custom-file-label']) }}
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('portal_homepage_logo_left', 'Portal Homepage Logo Left') }}
            <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.portal_homepage_logo_left') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file">
                {{ Form::file('portal_homepage_logo_left', ['class' => 'custom-file-input form-control', 'id' => 'portal_homepage_logo_left', 'disabled' => $disable_portal, 'data-width' => config('zevolifesettings.imageConversions.company.portal_homepage_logo_left.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_homepage_logo_left.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_homepage_logo_left')]) }}
                {{ Form::label('portal_homepage_logo_left', (!empty($brandingCo->portal_homepage_logo_left_name) && $branding ? $brandingCo->portal_homepage_logo_left_name : "Choose File"), ['class' => 'custom-file-label']) }}
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('portal_homepage_logo_right', 'Portal Homepage Logo Right') }}
            <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.portal_homepage_logo_right') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file">
                {{ Form::file('portal_homepage_logo_right', ['class' => 'custom-file-input form-control', 'id' => 'portal_homepage_logo_right', 'disabled' => $disable_portal, 'data-width' => config('zevolifesettings.imageConversions.company.portal_homepage_logo_right.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_homepage_logo_right.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_homepage_logo_right')]) }}
                {{ Form::label('portal_homepage_logo_right', (!empty($brandingCo->portal_homepage_logo_right_name) && $branding ? $brandingCo->portal_homepage_logo_right_name : "Choose File"), ['class' => 'custom-file-label']) }}
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-xl-4">
        <div class="form-group">
            {{ Form::label('terms_url', trans('labels.company.terms_of_use_url')) }}
            @if(!$edit)
            {{ Form::text('terms_url', old('terms_url', ($branding->terms_url ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter terms of use URL', 'id' => 'terms_url', 'autocomplete' => 'off', 'maxLength'=>'100', 'disabled' => $disable_portal]) }}
            @else
            {{ Form::text('terms_url', old('terms_url', ($branding->terms_url ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter terms of use URL', 'id' => 'terms_url', 'autocomplete' => 'off', 'maxLength'=>'100', 'disabled' => $disable_portal]) }}
            @endif
        </div>
    </div>
    <div class="col-lg-4 col-xl-4">
        <div class="form-group">
            {{ Form::label('privacy_policy_url', trans('labels.company.privacy_policy_url')) }}
            @if(!$edit)
            {{ Form::text('privacy_policy_url', old('privacy_policy_url', ($branding->privacy_policy_url ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter Privacy Policy URL', 'id' => 'privacy_policy_url', 'autocomplete' => 'off', 'maxLength'=>'100', 'disabled' => $disable_portal]) }}
            @else
            {{ Form::text('privacy_policy_url', old('privacy_policy_url', ($branding->privacy_policy_url ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter Privacy Policy URL', 'id' => 'privacy_policy_url', 'autocomplete' => 'off', 'maxLength'=>'100', 'disabled' => $disable_portal]) }}
            @endif
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('portal_favicon_icon', 'Portal Favicon') }}
            <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.portal_favicon_icon') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file">
                {{ Form::file('portal_favicon_icon', ['class' => 'custom-file-input form-control', 'id' => 'portal_favicon_icon', 'disabled' => $disable_portal, 'data-width' => config('zevolifesettings.imageConversions.company.portal_favicon_icon.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_favicon_icon.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_favicon_icon')]) }}
                {{ Form::label('portal_favicon_icon', (isset($brandingCo->portal_favicon_icon) && $branding ? $brandingCo->portal_favicon_icon_name : "Choose File"), ['class' => 'custom-file-label']) }}
            </div>
        </div>
    </div>
    <div class="col-lg-12 col-xl-12">
        <div class="form-group">
            {{ Form::label('portal_description', trans('labels.company.login_screen_description')) }}
            {{ Form::textarea('portal_description', old('portal_description', ($branding->portal_description ?? null)), ['id' => 'portal_description', 'rows' => 3, 'class' => 'form-control', 'placeholder'=>'Enter login screen description', 'disabled' => $disable_portal, 'maxLength'=>'500']) }}
        </div>
    </div>
    <div class="col-lg-12 col-xl-12">
        <div class="form-group">
            {{ Form::label('portal_sub_description', trans('labels.company.login_screen_sub_description')) }}
            {{ Form::textarea('portal_sub_description', old('portal_sub_description', ($branding->portal_sub_description ?? null)), ['id' => 'portal_sub_description', 'rows' => 3, 'class' => 'form-control', 'placeholder'=>'Enter login screen sub description', 'disabled' => $disable_portal, 'maxLength'=>'300']) }}
        </div>
    </div>
    <div class="col-lg-12 col-xl-12 dt-banners d-none">
        <div class="form-group">
            {{ Form::label('dt_title', trans('labels.company.dt_banner_title')) }}
            {{ Form::text('dt_title', old('dt_title', ($branding->dt_title ?? null)), ['id' => 'dt_title', 'disabled' => $disable_portal, 'rows' => 3, 'class' => 'form-control', 'placeholder'=>'Enter login screen description', 'maxLength'=>'100']) }}
        </div>
    </div>
    <div class="col-lg-12 col-xl-12 dt-banners d-none">
        <div class="form-group">
            {{ Form::label('dt_description', trans('labels.company.dt_banner_description')) }}
            {{ Form::textarea('dt_description', old('dt_description', ($branding->dt_description ?? null)), ['id' => 'dt_description', 'disabled' => $disable_portal, 'rows' => 3, 'class' => 'form-control', 'placeholder'=>'Enter login screen sub description', 'maxLength'=>'500']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-6">
        <div class="form-group">
            {{ Form::label('appointment_title', trans('labels.company.appointment_title')) }}
            {{ Form::text('appointment_title', old('appointment_title', ($appointmentTitle ?? 'Appointments')), ['id' => 'appointment_title', 'class' => 'form-control', 'placeholder'=>'Enter appointment title', 'maxLength'=>'50', 'disabled' => $disableContactDetails]) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('appointment_image', trans('labels.company.appointment_image')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.contact_us_image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file">
                    {{ Form::file('appointment_image', ['class' => 'custom-file-input form-control', 'disabled' => $disableContactDetails, 'id' => 'appointment_image', 'disabled' => $disableContactDetails, 'data-width' => config('zevolifesettings.imageConversions.company.appointment_image.width'), 'data-height' => config('zevolifesettings.imageConversions.company.appointment_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.appointment_image')]) }}
                    {{ Form::label('appointment_image', (!empty($appointmentImage) ? $appointmentImageName : "Choose File"), ['class' => 'custom-file-label']) }}
                    @if(!$edit)
                    <?php $default = 'data:image/png;base64,'.base64_encode(file_get_contents(config('zevolifesettings.fallback_image_url.company.appointment_image_local'))).''?> 
                    {{ Form::hidden("appointment_image_hidden", $default, ['class' => 'form-control', 'id' => 'appointment_image_hidden']) }}
                    {{ Form::hidden("appointment_image_name_hidden", 'appointment-default.png' , ['class' => 'form-control', 'id' => 'appointment_image_name_hidden']) }}                    <!-- {{ Form::label('appointment_image', (isset($branding->appointment_image) ? $branding->appointment_image_name : "Choose File"), ['class' => 'custom-file-label']) }} -->
                    @endif
                </div>
            </div>
    </div>
    <div class="col-lg-12 col-xl-12">
        <div class="form-group">
            {{ Form::label('appointment_description', trans('labels.company.appointment_description')) }}
            {{ Form::textarea('appointment_description', old('appointment_description', ($appointmentDescription ?? null)), ['id' => 'appointment_description', 'rows' => 3, 'class' => 'form-control', 'placeholder'=>'Enter appointment description', 'maxLength'=>'500', 'disabled' => $disableContactDetails]) }}
        </div>
    </div>
    
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('labels.company.contact_us_settings') }}
    </h3>
    <div class="row">
    <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('contact_us_header', 'Contact Us Header') }}
                {{ Form::text('contact_us_header', old('contact_us_header', ($brandingContactData->contact_us_header ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter contact us header', 'id' => 'contact_us_header', 'autocomplete' => 'off', 'maxLength'=>'50', 'disabled' => $disableContactDetails]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('contact_us_request', 'Contact Us Request') }}
                {{ Form::select('contact_us_request', $contactUsRequest, old('contact_us_request', ($brandingContactData->contact_us_request ?? null)), ['class' => 'form-control select2', 'id' => 'contact_us_request', 'placeholder' => 'Select request', 'data-placeholder' => 'Select request', 'data-allow-clear' => 'false', 'disabled' => $disableContactDetails]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('contact_us_image', 'Contact Us Image') }}
                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.contact_us_image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file">
                    {{ Form::file('contact_us_image', ['class' => 'custom-file-input form-control', 'id' => 'contact_us_image', 'disabled' => $disableContactDetails, 'data-width' => config('zevolifesettings.imageConversions.company.contact_us_image.width'), 'data-height' => config('zevolifesettings.imageConversions.company.contact_us_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.contact_us_image')]) }}
                    {{ Form::label('contact_us_image', (isset($brandingContactData->contact_us_image) && $branding ? $brandingContactData->contact_us_image_name : "Choose File"), ['class' => 'custom-file-label']) }}
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-xl-12">
            <div class="form-group">
                {{ Form::label('contact_us_description', 'Contact Us Description') }}
                <textarea class="form-control" name="contact_us_description" id="contact_us_description" placeholder="Enter description" >{{ old('contact_us_description', htmlspecialchars_decode(@$brandingContactData->contact_us_description)) }}</textarea>
                <span id="contact_us_description-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('company.validation.contact_us_description_max')}}</span>
                <span id="contact_us_description-format-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('company.validation.contact_us_description_format')}}</span>
            </div>
        </div> 
    </div>
</div>

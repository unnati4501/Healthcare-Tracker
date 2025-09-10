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
                <a class="mw-100" href="javascript:void(0);" id="subdomainUrl" target="_self">
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
    <div class="col-lg-6 col-xl-8">
        <div class="form-group">
            {{ Form::label('onboarding_description', trans('labels.company.login_screen_description')) }}
            {{ Form::textarea('onboarding_description', old('onboarding_description', ($branding->onboarding_description ?? null)), ['id' => 'onboarding_description', 'rows' => 3, 'class' => 'form-control', 'placeholder'=>'Enter login screen description', 'disabled' => $disable_branding]) }}
        </div>
    </div>
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
</div>
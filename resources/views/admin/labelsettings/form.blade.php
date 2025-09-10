<div class="card-body">
    <div class="card-inner">
        <h3 class="card-inner-title">
            {{trans('labelsettings.form.labels.home')}}
        </h3>
        <div class="row justify-content-center justify-content-md-start">
            <div class="col-xl-4">
                <div class="form-group">
                    {{ Form::label("recent_stories", trans('labelsettings.form.labels.recent_stories')) }}
                    {{ Form::text('home[recent_stories]', old('home[recent_stories]', ($companyLabelString['recent_stories'] ?? trans('labelsettings.form.placeholder.recent_stories'))), ['class' => 'form-control', 'placeholder' => trans('labelsettings.form.placeholder.recent_stories'), 'id' => 'recent_stories', 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-xl-4">
                <div class="form-group">
                    {{ Form::label("lbl_company", trans('labelsettings.form.labels.lbl_company')) }}
                    {{ Form::text('home[lbl_company]', old('home[lbl_company]', ($companyLabelString['lbl_company'] ?? trans('labelsettings.form.placeholder.lbl_company'))), ['class' => 'form-control', 'placeholder' => trans('labelsettings.form.placeholder.lbl_company'), 'id' => 'lbl_company', 'autocomplete' => 'off']) }}
                </div>
            </div>
        </div>
    </div>
    <div class="card-inner">
        <h3 class="card-inner-title">
            {{trans('labelsettings.form.labels.support')}}
        </h3>
        <div class="row justify-content-center justify-content-md-start">
            <div class="col-xl-4">
                <div class="form-group">
                    {{ Form::label("get_support", trans('labelsettings.form.labels.get_support')) }}
                    {{ Form::text('support[get_support]', old('support[get_support]', ($companyLabelString['get_support'] ?? trans('labelsettings.form.placeholder.get_support'))), ['class' => 'form-control', 'placeholder' => trans('labelsettings.form.placeholder.get_support'), 'id' => 'get_support', 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-xl-4">
                <div class="form-group">
                    {{ Form::label("employee_assistance", trans('labelsettings.form.labels.employee_assistance')) }}
                    {{ Form::text('support[employee_assistance]', old('support[employee_assistance]', ($companyLabelString['employee_assistance'] ?? trans('labelsettings.form.placeholder.employee_assistance'))), ['class' => 'form-control', 'placeholder' => trans('labelsettings.form.placeholder.employee_assistance'), 'id' => 'employee_assistance', 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-xl-4">
                <div class="form-group">
                    {{ Form::label("lbl_faq", trans('labelsettings.form.labels.lbl_faq')) }}
                    {{ Form::text('support[lbl_faq]', old('support[lbl_faq]', ($companyLabelString['lbl_faq'] ?? trans('labelsettings.form.placeholder.lbl_faq'))), ['class' => 'form-control', 'placeholder' => trans('labelsettings.form.placeholder.lbl_faq'), 'id' => 'lbl_faq', 'autocomplete' => 'off']) }}
                </div>
            </div>
        </div>
    </div>
    <div class="card-inner">
        <h3 class="card-inner-title">
            {{trans('labelsettings.form.labels.lbl_onboarding')}}
        </h3>
        <div class="row justify-content-center justify-content-md-start">
            <div class="col-xl-4">
                <div class="form-group">
                    {{ Form::label("lbl_location", trans('labelsettings.form.labels.lbl_location')) }}
                    {{ Form::text('onboarding[lbl_location]', old('onboarding[lbl_location]', ($companyLabelString['lbl_location'] ?? trans('labelsettings.form.placeholder.lbl_location'))), ['class' => 'form-control', 'placeholder' => trans('labelsettings.form.placeholder.lbl_location'), 'id' => 'lbl_location', 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-xl-4">
                <div class="form-group">
                    {{ Form::label("location_logo", trans('labelsettings.form.labels.location_logo')) }}
                    <span class="font-16 qus-sign-tooltip" data-bs-html="true" data-toggle="help-tooltip" title="{{ getHelpTooltipText('label_settings.location_logo') }} <br/> {{ config('zevolifesettings.company_label_string.onboarding.location_logo.support_file_type') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    @if($companyLabelString['location_logo']['remove'])
                    <span class="font-16 float-end">
                        <a class="badge bg-secondary remove-media" data-action="location_logo" href="javascript:void(0);" title="{{ trans('labelsettings.message.default_logo') }}">
                            <i aria-hidden="true" class="fa fa-times">
                            </i>
                            {{ trans('labelsettings.buttons.remove') }}
                        </a>
                    </span>
                    @endif
                    <div class="custom-file custom-file-preview">
                        <label class="file-preview-img">
                            <img class="bg-gray" id="locationLogoPreview" src="{{ $companyLabelString['location_logo']['src'] }}" width="200"/>
                        </label>
                        {{ Form::file('onboarding[location_logo]', ['class' => 'custom-file-input form-control', 'id' => 'location_logo', 'data-previewelement' => '#locationLogoPreview', 'data-width' => config('zevolifesettings.imageConversions.label_settings.location_logo.width'), 'data-height' => config('zevolifesettings.imageConversions.label_settings.location_logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.label_settings.location_logo')]) }}
                        {{ Form::label('location_logo', $companyLabelString['location_logo']['label'], ['class' => 'custom-file-label']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center justify-content-md-start">
            <div class="col-xl-4">
                <div class="form-group">
                    {{ Form::label("lbl_department", trans('labelsettings.form.labels.lbl_department')) }}
                    {{ Form::text('onboarding[lbl_department]', old('onboarding[lbl_department]', ($companyLabelString['lbl_department'] ?? trans('labelsettings.form.placeholder.lbl_department'))), ['class' => 'form-control', 'placeholder' => trans('labelsettings.form.placeholder.lbl_department'), 'id' => 'lbl_department', 'autocomplete' => 'off']) }}
                </div>
            </div>
            <div class="col-xl-4">
                <div class="form-group">
                    {{ Form::label("department_logo", trans('labelsettings.form.labels.department_logo')) }}
                    <span class="font-16 qus-sign-tooltip" data-bs-html="true" data-toggle="help-tooltip" title="{{ getHelpTooltipText('label_settings.department_logo') }} <br/> {{ config('zevolifesettings.company_label_string.onboarding.location_logo.support_file_type') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    @if($companyLabelString['department_logo']['remove'])
                    <span class="font-16 float-end">
                        <a class="badge bg-secondary remove-media" data-action="department_logo" href="javascript:void(0);" title="{{ trans('labelsettings.message.default_logo') }}">
                            <i aria-hidden="true" class="fa fa-times">
                            </i>
                            {{ trans('labelsettings.buttons.remove') }}
                        </a>
                    </span>
                    @endif
                    <div class="custom-file custom-file-preview">
                        <label class="file-preview-img">
                            <img class="bg-gray" id="departmentLogoPreview" src="{{ $companyLabelString['department_logo']['src'] }}" width="200"/>
                        </label>
                        {{ Form::file('onboarding[department_logo]', ['class' => 'custom-file-input form-control', 'id' => 'department_logo', 'data-previewelement' => '#departmentLogoPreview', 'data-width' => config('zevolifesettings.imageConversions.label_settings.department_logo.width'), 'data-height' => config('zevolifesettings.imageConversions.label_settings.department_logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.label_settings.department_logo')]) }}
                        {{ Form::label('department_logo', $companyLabelString['department_logo']['label'], ['class' => 'custom-file-label']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
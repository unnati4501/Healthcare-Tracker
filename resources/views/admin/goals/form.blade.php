<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('logo', trans('goals.form.labels.goal_tag_logo')) }}
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('goals.logo') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file custom-file-preview">
                <label class="file-preview-img" for="profileImage">
                    <img height="200" id="previewImg" src="{{ ($record->logo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                </label>
                {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'data-width' => config('zevolifesettings.imageConversions.goals.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.goals.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.goals.logo'), 'id' => 'logo']) }}
                <label class="custom-file-label" for="logo">
                    {{ trans($record->logo_name ?? 'goals.form.placeholder.choose_file') }}
                </label>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('title', trans('goals.form.labels.goal_tag_name')) }}
            {{ Form::text('title', old('title', ($record->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('goals.form.placeholder.enter_name'), 'id' => 'title', 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>
<div class="col-xl-6">
    <div class="form-group">
        {{ Form::label('name', trans('appthemes.form.labels.theme_name')) }}
        {{ Form::text('name', old('name', ($theme->name ?? null)), ['class' => 'form-control', 'placeholder' => trans('appthemes.form.placeholder.enter_app_theme_name'), 'id' => 'name', 'autocomplete' => 'off']) }}
    </div>
</div>
<div class="col-xl-6">
    <div class="form-group">
        {{ Form::label('theme', trans('appthemes.form.labels.json_file')) }}
        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('app-theme.theme') }}">
            <i aria-hidden="true" class="far fa-info-circle text-primary">
            </i>
        </span>
        <div class="custom-file">
            {{ Form::file('theme', ['class' => 'custom-file-input form-control', 'id' => 'theme']) }}
            {{ Form::label('theme', ($theme->theme_name ?? trans('appthemes.form.placeholder.choose_file')), ['class' => 'custom-file-label']) }}
        </div>
    </div>
</div>
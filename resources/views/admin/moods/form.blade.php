<div class="row">
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('logo', trans('moods.form.labels.logo')) }}
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('moods.logo') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file custom-file-preview">
                <label class="file-preview-img" for="profileImage" style="display: flex;">
                    <img height="200" id="previewImg" src="{{ ($record->mood_logo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                </label>
                {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo','data-width' => config('zevolifesettings.imageConversions.moods.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.moods.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.moods.logo')]) }}
                <label class="custom-file-label" for="logo">
                    {{ trans($record->mood_logo_name ?? trans('moods.form.placeholders.choose')) }}
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('title', trans('moods.form.labels.name')) }}
            {{ Form::text('title', old('title', ($record->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('moods.form.placeholders.name'), 'id' => 'title', 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>
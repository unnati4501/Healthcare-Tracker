<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        {{ Form::label('target_type', trans('challengeLibrary.form.labels.target')) }}
        {{ Form::select('target_type', $target_type, old('target_type', ($record->target_type ?? null)), ['class' => 'form-control select2', 'id'=>'target_type', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => "", 'data-placeholder' => trans('challengeLibrary.form.placeholders.target'), 'data-allow-clear' => 'true', 'disabled' => $edit]) }}
    </div>
</div>
<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        {{ Form::label('image', trans('challengeLibrary.form.labels.image')) }}
        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('challenge_library.image') }}">
            <i aria-hidden="true" class="far fa-info-circle text-primary">
            </i>
        </span>
        <div class="custom-file custom-file-preview">
            {{ Form::file('image', ['class' => 'custom-file-input form-control', 'id' => 'image', 'data-width' => config('zevolifesettings.imageConversions.challenge_library.image.width'), 'data-height' => config('zevolifesettings.imageConversions.challenge_library.image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.challenge_library.image')]) }}
            <label class="file-preview-img" for="image" style="display: flex;">
                <img height="200" id="previewImg" src="{{ ($record->image ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
            </label>
            <label class="custom-file-label" for="image">
                {{ trans($record->image_name ?? trans('challengeLibrary.form.placeholders.image')) }}
            </label>
        </div>
    </div>
</div>
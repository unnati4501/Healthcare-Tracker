<div class="row">
    <div class="col-lg-4 col-xl-6">
        <div class="form-group">
            {{ Form::label('banner_image', trans('company.dt_banners.form.labels.image')) }}
            <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.banner_image') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file custom-file-preview">
                    {{ Form::file('banner_image', ['class' => 'custom-file-input form-control', 'id' => 'banner_image', 'data-width' => config('zevolifesettings.imageConversions.company.banner_image.width'), 'data-height' => config('zevolifesettings.imageConversions.company.banner_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.banner_image'), 'data-previewelement' => '#banner_image_preview', 'accept' => 'image/*'])}}
                    <label class="file-preview-img" for="banner_image_preview">
                        <img id="banner_image_preview" src="{{ ((!empty($record) && !empty($record->getFirstMediaUrl('banner_image'))) ? $record->getFirstMediaUrl('banner_image') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('banner_image', ((!empty($record) && !empty($record->getFirstMediaUrl('banner_image'))) ? $record->getFirstMedia('banner_image')->name : trans('company.dt_banners.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
            </div>
        </div>
    </div>
    <div class="col-lg-12 col-xl-12">
            <div class="form-group">
                {{ Form::label('description', trans('company.dt_banners.form.labels.text')) }}
                <textarea class="form-control" name="description" id="description" placeholder="Enter description" >{{ old('description', htmlspecialchars_decode(@$record->description)) }}</textarea>
                <span id="description-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('company.dt_banners.validation.description_required')}}</span>
                <span id="description-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('company.dt_banners.validation.description_max')}}</span>
                <span id="description-format-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('company.dt_banners.validation.description_format')}}</span>
            </div>
    </div>
</div>
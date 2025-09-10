<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('logo', trans('surveysubcategories.form.labels.logo')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('surveycategory.logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                @if(!empty($subCategoryData) && $subCategoryData->logo)
                <span class="font-16 float-end">
                    <a class="badge bg-secondary remove-logo-media" data-action="logo" href="javascript:void(0);" title="{{ trans('surveysubcategories.form.placeholder.remove_logo') }}">
                        <i aria-hidden="true" class="fa fa-times">
                        </i>
                        {{ trans('surveysubcategories.buttons.remove') }}
                    </a>
                </span>
                @endif
                <div class="custom-file custom-file-preview">
                    {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-previewelement' => '#logo_preview', 'data-width' => config('zevolifesettings.imageConversions.surveycategory.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.surveycategory.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.surveycategory.logo'), 'data-previewelement' => '#logo_preview', 'accept' => 'image/*'])}}
                    <label class="file-preview-img d-flex" for="logo_preview">
                        <img id="logo_preview" src="{{ ($subCategoryData->logo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('logo', ((!empty($subCategoryData) && !empty($subCategoryData->getFirstMediaUrl('logo'))) ? $subCategoryData->getFirstMedia('logo')->name : ((!empty($subCategoryData) && $subCategoryData->logo)? 'zevohealth.png' : trans('surveysubcategories.form.placeholder.choose_file'))), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('display_name', trans('surveysubcategories.form.labels.subcategory')) }}
                {{ Form::text('display_name', old('display_name', ($subCategoryData->display_name ?? null)), ['class' => 'form-control', 'placeholder' => trans('surveysubcategories.form.placeholder.subcategory'), 'id' => 'display_name', 'autocomplete' => 'off']) }}
                {{ Form::hidden('category', (!empty($subCategoryData->category_id) ? $subCategoryData->category_id : request()->surveycategory->id)) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <label class="custom-checkbox no-label">
                    {{ trans('surveysubcategories.form.labels.premium') }}
                    {{ Form::checkbox('is_primum', true, old('is_primum', ((!empty($subCategoryData) && $subCategoryData->is_primum)? true : false)), ['class' => 'form-control', 'id' => 'is_primum']) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>
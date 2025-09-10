<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        {{ Form::label('category', trans('categories.subcategories.form.labels.category')) }}
        @if(!empty($subCategoryData->category_id))
        {{ Form::select('category', $categories, $subCategoryData->category_id, ['class' => 'form-control select2','id'=>'category', 'placeholder' => '', 'data-placeholder' => trans('categories.subcategories.form.placeholders.category'),'autocomplete' => 'off','disabled'=>'true'] ) }}
        {{Form::hidden('category', $subCategoryData->category_id)}}
        @else
        {{ Form::select('category', $categories, null, ['class' => 'form-control select2','id'=>'category', 'placeholder' => '', 'data-placeholder' => trans('categories.subcategories.form.placeholders.category'), 'autocomplete' => 'off'] ) }}
        @endif
    </div>
</div>
<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        {{ Form::label('name', trans('categories.subcategories.form.labels.sub_category')) }}
        @if(!empty($subCategoryData->name))
            {{ Form::text('name', old('name',$subCategoryData->name), ['class' => 'form-control', 'placeholder' => trans('categories.subcategories.form.placeholders.sub_category'), 'id' => 'name', 'autocomplete' => 'off']) }}
        @else
            {{ Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => trans('categories.subcategories.form.placeholders.sub_category'), 'id' => 'name', 'autocomplete' => 'off']) }}
        @endif
    </div>
</div>
<div class="col-lg-6 col-xl-4 type_wrappers" id="background_wrapper" >
    <div class="form-group">
        {{ Form::label('background', trans('categories.subcategories.form.labels.background_image')) }}
        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('subcategories.background') }}">
            <i aria-hidden="true" class="far fa-info-circle text-primary">
            </i>
        </span>
        <div class="custom-file custom-file-preview">
            {{ Form::file('background', ['class' => 'custom-file-input form-control', 'id' => 'background', 'data-width' => config('zevolifesettings.imageConversions.subcategories.background.width'), 'data-height' => config('zevolifesettings.imageConversions.subcategories.background.height'),'data-ratio' => config('zevolifesettings.imageAspectRatio.subcategories.background'), 'data-previewelement' => '#background_preview', 'accept' => 'image/*']) }}
            <label class="file-preview-img" for="background_preview">
                <img id="background_preview" src="{{ ($subCategoryData->background ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
            </label>
            {{ Form::label('background', ((!empty($subCategoryData) && !empty($subCategoryData->getFirstMediaUrl('background'))) ? $subCategoryData->getFirstMedia('background')->name : trans('categories.subcategories.form.placeholders.choose_file')), ["class" => "custom-file-label"]) }}
        </div>
    </div>
</div>

<div class="col-lg-6 col-xl-4 type_wrappers" id="logo_wrapper" >
    <div class="form-group">
        {{ Form::label('logo', trans('categories.subcategories.form.labels.logo')) }}
        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('subcategories.logo') }}">
            <i aria-hidden="true" class="far fa-info-circle text-primary">
            </i>
        </span>
        <div class="custom-file custom-file-preview">
            {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-ratio' => config('zevolifesettings.imageAspectRatio.subcategories.logo'), 'data-width' => config('zevolifesettings.imageConversions.subcategories.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.subcategories.logo.height'), 'data-previewelement' => '#logo_preview', 'accept' => 'image/*']) }}
            <label class="file-preview-img" for="logo_preview">
                <img id="logo_preview" src="{{ ($subCategoryData->logo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
            </label>
            {{ Form::label('logo', ((!empty($subCategoryData) && !empty($subCategoryData->getFirstMediaUrl('logo'))) ? $subCategoryData->getFirstMedia('logo')->name : trans('categories.subcategories.form.placeholders.choose_file')), ["class" => "custom-file-label"]) }}
        </div>
    </div>
</div>
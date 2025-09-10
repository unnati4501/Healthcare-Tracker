<div category="dialog" class="modal fade" data-id="0" id="subcategory-model-box" tabindex="-1">
    <div category="document" class="modal-dialog">
        <div class="modal-content">
            {{ Form::open(['class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'addSubcategoryForm', 'files' => true]) }}
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">
                    {{ trans('services.modal.add_subcategory') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                    <div class="form-group col-xl-12">
                        {{ Form::label('sub_category_logo', trans('services.form.labels.logo')) }}
                        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('services.logo') }}">
                            <i aria-hidden="true" class="far fa-info-circle text-primary">
                            </i>
                        </span>
                        <div class="custom-file custom-file-preview">
                            {{ Form::file('sub_category_logo', ['class' => 'custom-file-input form-control', 'id' => 'sub_category_logo', 'data-ratio' => config('zevolifesettings.imageAspectRatio.services.logo'), 'data-width' => config('zevolifesettings.imageConversions.services.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.services.logo.height'), 'data-previewelement' => '#subcategory_logo_preview', 'accept' => 'image/*']) }}
                            <label class="file-preview-img" for="sub_category_logo_preview">
                                <img id="sub_category_logo_preview" src="{{ ($serviceData->logo ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                            </label>
                            {{ Form::label('sub_category_logo', ((!empty($serviceData) && !empty($serviceData->getFirstMediaUrl('sub_category_logo'))) ? $serviceData->getFirstMedia('sub_category_logo')->name : trans('services.form.placeholders.choose_file')), ["class" => "custom-file-label", "id"=>"subcategory_logo_name"]) }}
                            <a id="convertImg" href=""></a>
                        </div>
                        <span id="subcategoryLogoError" class="text-danger" style="display:none"></span>
                    </div>
                    <div class="form-group col-xl-12">
                        {{ Form::label('name', trans('services.subcategories.form.labels.sub_category')) }}
                        @if(!empty($serviceData->name))
                            {{ Form::text('sub_category_name', old('name',$serviceData->name), ['class' => 'form-control', 'placeholder' => trans('services.form.placeholders.service'), 'id' => 'sub_category_name', 'autocomplete' => 'off', 'maxlength' => 100]) }}
                        @else
                            {{ Form::text('sub_category_name', old('name'), ['class' => 'form-control', 'placeholder' => trans('services.form.placeholders.service'), 'id' => 'sub_category_name', 'autocomplete' => 'off', 'maxlength' => 100]) }}
                        @endif
                        <span id="subcategoryNameError" class="text-danger" style="display:none"></span>
                    </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary subcategory-save" id="subcategorySave" type="button">
                    {{ trans('buttons.general.save') }}
                </button>
                <button class="btn btn-primary subcategory-update" id="subcategoryUpdate" type="button" style="display:none;">
                    {{ trans('buttons.general.save') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
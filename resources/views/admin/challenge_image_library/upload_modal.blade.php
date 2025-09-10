<div class="modal fade" data-backdrop="static" id="bulk-upload-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        {{ Form::open(['route' => 'admin.challengeImageLibrary.storeBulk', 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'bulkUploadImagesFrm', 'files' => true]) }}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('challengeLibrary.modal.upload.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-xl-12">
                    {{ Form::label('target_type', trans('challengeLibrary.modal.upload.form.labels.target')) }}
                    {{ Form::select('upload_target_type', $target_type, old('target_type', ($record->target_type ?? null)), ['class' => 'form-control select2', 'id'=>'upload_target_type', 'autocomplete' => 'off', 'placeholder' => "", 'data-placeholder' => trans('challengeLibrary.modal.upload.form.placeholders.target'), 'data-allow-clear' => 'true']) }}
                </div>
                <div class="form-group col-xl-12">
                    {{ Form::label('images', trans('challengeLibrary.modal.upload.form.labels.images')) }}
                    <div class="custom-file">
                        {{ Form::file('images[]', ['class' => 'custom-file-input form-control', 'id' => 'images', 'data-width' => config('zevolifesettings.imageConversions.challenge_library.image.width'), 'data-height' => config('zevolifesettings.imageConversions.challenge_library.image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.challenge_library.image'), 'accept' => 'image/x-png,image/jpg,image/jpeg', 'multiple' => true]) }}
                        <label class="custom-file-label" for="images">
                            {{ trans('challengeLibrary.modal.upload.form.placeholders.images') }}
                        </label>
                    </div>
                </div>
                <div class="form-grou col-xl-12">
                    <div class="alert alert-info">
                        <div>
                            Maximum {{ config('zevolifesettings.challenge_image_library_max_images_limit') }} images can be uploaded at a time.
                        </div>
                        <div>
                            {{ getDimensionHelpTooltipText('challenge_library.image') }}.
                        </div>
                        <div>
                            {{ getSizeHelpTooltipText('challenge_library.image') }}.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="bulk-upload-confirm" type="submit">
                    {{ trans('buttons.general.upload') }}
                </button>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>
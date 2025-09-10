<div class="modal fade" data-backdrop="static" id="bulk-upload-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        {{ Form::open(['route' => ['admin.cronofy.sessions.store-attachments', $id], 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'bulkUploadAttachmentFrm', 'files' => true]) }}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('Cronofy.session_details.modal.upload.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group col-xl-12">
                    {{ Form::label('attachments', trans('Cronofy.session_details.modal.upload.form.labels.attachments')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ config('zevolifesettings.attachments_tooltip') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    <div class="custom-file">
                        {{ Form::file('attachments[]', ['class' => 'custom-file-input form-control', 'id' => 'attachments', 'accept' => 'image/x-png,image/jpg,image/jpeg,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain', 'multiple' => false]) }}
                        <label class="custom-file-label" for="attachments">
                            {{ trans('Cronofy.session_details.modal.upload.form.placeholders.attachments') }}
                        </label>
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
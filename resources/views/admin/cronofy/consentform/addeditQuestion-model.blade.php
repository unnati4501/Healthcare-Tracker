<div category="dialog" class="modal fade" data-id="0" id="addQuestion-model-box" tabindex="-1">
    <div category="document" class="modal-dialog">
        <div class="modal-content">
            {{ Form::open(['class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'questionForm', 'files' => true]) }}
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">
                    {{ trans('Cronofy.consent_form.model_popup.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                {{ Form::hidden("edit", 0, ['class' => 'form-control', 'id' => 'editflag']) }}
                <div class="form-group col-xl-12">
                    {{ Form::label('question_title', trans('Cronofy.consent_form.form.labels.title')) }}
                    {{ Form::text('question_title', old('question_title', ($consetFormQuestions[0]['title'])), ['class' => 'form-control', 'placeholder' => trans('Cronofy.consent_form.form.placeholder.title'), 'id' => 'question_title', 'autocomplete' => 'off']) }}
                    <div id="question_title-error-cstm" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                        </div>
                </div>
                <div class="form-group col-xl-12">
                    {{ Form::label('question_description', trans('Cronofy.consent_form.form.labels.description')) }}
                    {{ Form::textarea('question_description', old('question_description', ($consetFormQuestions[0]['description'])), ['class' => 'form-control notes-add-ckeditor', 'id' => 'question_description', 'data-errplaceholder' => '#question_description-error-cstm', 'data-formid' => "#question_description", 'data-upload-path' => route('admin.ckeditor-upload.consentform-description', ['_token' => csrf_token() ])]) }}
                        <div>
                            <small>
                                {{ trans('Cronofy.consent_form.message.fullscreen_mode_for_description') }}

                                <i class="fas fa-arrows-alt" style="transform: rotate(45deg);">
                                </i>
                                {{ trans('Cronofy.consent_form.message.from_toolbar') }}
                            </small>
                        </div>
                        <div id="question_description-error-cstm" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                        </div>
                </div>  
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary subcategory-save" id="questionSave" type="button">
                    {{ trans('buttons.general.save') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<div class="modal fade" data-id="0" id="cancel-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('challenges.modal.cancel.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                {{ Form::textarea('cancel_reason', old('cancel_reason'), ['class' => 'form-control', 'placeholder' => trans('challenges.form.placeholders.cancel_reason'), 'id' => 'cancel_reason']) }}
                <span id="cancel_reason-error" class="cancel_reason" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('challenges.messages.cancel_reason') }}</span>
                <span id="cancel_reason-error-max-character" class="cancel_reason" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('challenges.messages.cancel_reason_max_character') }}</span>
            </div>
            <div class="modal-footer justify-content-end">
                {{-- <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button> --}}
                <button class="btn btn-primary" id="cancel-model-box-confirm" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
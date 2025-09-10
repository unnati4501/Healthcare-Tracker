<div class="modal fade" data-id="0" data-notefrom="" id="delete-question-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('Cronofy.consent_form.model_popup.delete_title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('Cronofy.consent_form.model_popup.delete_message') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="delete-question-model-box-confirm" type="button">
                    {{ trans('buttons.general.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
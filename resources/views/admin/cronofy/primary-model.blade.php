<div class="modal fade" data-id="0" id="primary-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('Cronofy.modal.primary') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    {{ trans('Cronofy.modal.primary_message') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{trans('buttons.general.cancel')}}
                </button>
                <button class="btn btn-primary" id="primary-model-box-confirm" type="button">
                    {{trans('buttons.general.make-primary')}}
                </button>
            </div>
        </div>
    </div>
</div>
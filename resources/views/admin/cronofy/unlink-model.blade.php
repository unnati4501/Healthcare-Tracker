<div class="modal fade" data-id="0" id="unlink-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('Cronofy.modal.unlink') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    {{ trans('Cronofy.modal.unlink_message') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{trans('buttons.general.cancel')}}
                </button>
                <button class="btn btn-primary" id="unlink-model-box-confirm" type="button">
                    {{trans('buttons.general.unlink')}}
                </button>
            </div>
        </div>
    </div>
</div>
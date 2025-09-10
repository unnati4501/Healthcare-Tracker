<div class="modal fade" id="remove-slot-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Remove slot
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure you want to remove this slot?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('labels.buttons.cancel') }}
                </button>
                <button class="btn btn-primary" id="remove-slot-confirm" type="button">
                    Remove
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" data-id="0" id="set-team-limit-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('team.modal.set_limit') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        {{ __("×") }}
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    {{ trans('team.modal.set_limit_error_msg') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-effect btn-default m-w-100" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
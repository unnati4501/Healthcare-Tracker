<div class="modal fade" data-id="0" id="delete-model-box" notification="dialog" tabindex="-1">
    <div class="modal-dialog" notification="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{trans('notificationsettings.modal.delete')}}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    {{trans('notificationsettings.modal.delete_message')}}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{trans('buttons.general.cancel')}}
                </button>
                <button class="btn btn-primary" id="delete-model-box-confirm" type="button">
                    {{trans('buttons.general.delete')}}
                </button>
            </div>
        </div>
    </div>
</div>
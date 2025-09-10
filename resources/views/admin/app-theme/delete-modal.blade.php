<div class="modal fade" id="delete-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('appthemes.modal.delete') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    {{ trans('appthemes.modal.delete_message') }}
                </p>
            </div>
            <div class="modal-footer">
                {{ Form::button(trans('buttons.general.cancel'), ['type' => 'button', 'class' => 'btn btn-outline-primary', 'data-dismiss' => 'modal']) }}
                {{ Form::button(trans('buttons.general.delete'), ['type' => 'button', 'id' => 'delete-model-box-confirm', 'class' => 'btn btn-primary']) }}
            </div>
        </div>
    </div>
</div>
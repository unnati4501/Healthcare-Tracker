<div class="modal fade" data-id="0" data-backdrop="static" data-keyboard="false" id="cancel-event-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Cancel Event?') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('marketplace.booking_details.modal.cancel.title') }}
                </p>
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::textarea('cancel_reason', null, ['id' => 'cancel_reason', 'class' => 'form-control mt-2', 'placeholder' => trans('marketplace.booking_details.placeholder.reason'), 'rows' => 3]) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="event-cancel-model-box-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>
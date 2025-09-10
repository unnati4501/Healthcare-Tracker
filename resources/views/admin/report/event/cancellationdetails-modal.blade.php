<div class="modal fade" data-backdrop="static" data-bid="0" data-keyboard="false" id="cancel-event-details-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('bookingreport.modal.cancellation_details') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::label('', trans('bookingreport.modal.cancelled_by')) }}:
                        {{ Form::label('', trans('bookingreport.modal.event_name'), ['class' => 'f-400', 'id' => 'cancelled_by']) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('', trans('bookingreport.modal.date_time')) }}:
                        {{ Form::label('', trans('bookingreport.modal.event_name'), ['class' => 'f-400', 'id' => 'cancelled_at']) }}
                    </div>
                    <div class="form-group col-12">
                        {{ Form::label('', trans('bookingreport.modal.reason')) }}:
                        {{ Form::label('', trans('bookingreport.modal.event_name'), ['class' => 'f-400', 'id' => 'cancelation_reason']) }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
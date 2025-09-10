<div class="modal fade" data-backdrop="static" data-bid="0" data-keyboard="false" id="cancel-session-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('Cancel Session?') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ __('Are you sure, you want to cancel the session?') }}
                </p>
                {{ Form::open(['action' => null, 'class' => 'form-horizontal', 'method' => 'post', 'role' => 'form', 'id' => 'cancelSessionForm']) }}
                <div class="row">
                    <div class="form-group col-12">
                        {{ Form::textarea('cancelled_reason', null, ['id' => 'cancelled_reason', 'class' => 'form-control mt-2', 'placeholder' => __('Enter reason for cancel the session'), 'rows' => 3]) }}
                    </div>
                </div>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="session-cancel-model-box-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>
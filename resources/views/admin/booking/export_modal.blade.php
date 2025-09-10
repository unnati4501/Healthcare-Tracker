<div class="modal fade" data-id="0" id="export-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'admin.bookings.exportBookings', 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'exportBookings', 'files' => false]) }}
            <div class="modal-header">
                <h5 class="modal-title" id="model-title">
                    {{ trans('marketplace.modal.export.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body" id="exportNps">
                <input type="hidden" name="event" id="event_pop">
                <input type="hidden" name="event_category" id="event_category_pop">
                <input type="hidden" name="event_company" id="event_company_pop">
                <input type="hidden" name="event_status" id="event_status_pop">
                <div class="form-group col-lg-12">
                    {{ Form::label('email', trans('challenges.modal.export.form.labels.email')) }}
                    {{ Form::text('email', $loginemail, ['class' => 'form-control', 'placeholder' => trans('challenges.modal.export.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'off']) }}
                    <span id="emailError" class="text-danger" style="display:none"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="export-model-box-confirm" type="submit">
                    {{ trans('buttons.general.export') }}
                </button>
            </div>
            <div class="modal-body" id="exportBookingMsg" style="display: none">
                {{ trans('marketplace.modal.export.message') }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
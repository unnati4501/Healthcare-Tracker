<div class="modal fade" data-id="0" id="export-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'admin.reports.exportDigitalTherapyReport', 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'exportDigitalTherapyReport', 'files' => false]) }}
            <div class="modal-header">
                <h5 class="modal-title" id="model-title">
                    {{ trans('digitaltheraphyreport.title.modal-title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body" id="exportNps">
                <input type="hidden" name="company" id="company_popup">
                <input type="hidden" name="dtFromdate" id="dt_fromdate_popup">
                <input type="hidden" name="dtTodate" id="dt_todate_popup">
                <input type="hidden" name="dtStatus" id="dt_status_popup">
                <input type="hidden" name="dtService" id="dt_service_popup">
                <input type="hidden" name="tab" id="dt_tab_popup">
                <input type="hidden" name="created_by" id="dt_created_by">
                <input type="hidden" name="wellbeingSpecialist" id="wellbeing_specialist_popup">
                <input type="hidden" name="user" id="user_popup">
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
            <div class="modal-body" id="exportDigitalTherapyMsg" style="display: none">
                {{ trans('challenges.modal.export.message') }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
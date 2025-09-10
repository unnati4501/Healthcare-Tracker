<div class="modal fade" data-id="0" id="export-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'admin.reports.export-occupational-health-report', 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'occupationalHealthReportForm', 'files' => false]) }}
            <div class="modal-header">
                <h5 class="modal-title" id="model-title">
                    {{ trans('occupationalHealthReport.title.modal-title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body" id="exportOccupationalHealth">
                <input type="hidden" name="company" id="company_popup">
                <input type="hidden" name="fromDate" id="fromdate_popup">
                <input type="hidden" name="toDate" id="todate_popup">
                <input type="hidden" name="wellbeingSpecialist" id="wellbeing_specialist_popup">
                <input type="hidden" name="userName" id="username_popup">
                <div class="form-group col-lg-12">
                    {{ Form::label('email', trans('occupationalHealthReport.modal.export.form.labels.email')) }}
                    {{ Form::text('email', $loginemail, ['class' => 'form-control', 'placeholder' => trans('occupationalHealthReport.modal.export.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'off']) }}
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
            <div class="modal-body" id="exportOccupationalHealthReportMsg" style="display: none">
                {{ trans('occupationalHealthReport.modal.export.message') }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
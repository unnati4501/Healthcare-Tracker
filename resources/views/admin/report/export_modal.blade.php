<div class="modal fade" data-id="0" id="export-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'admin.reports.exportNpsReport', 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'exportNpsReport', 'files' => false]) }}
            <div class="modal-header">
                <h5 class="modal-title" id="model-title">
                    {{ trans('customersatisfaction.modal.export.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body" id="exportNps">
                {{ Form::hidden('is_portal', null, ['id' => 'isPortal']) }}
                {{ Form::hidden('isFiltered', 0, ['id' => 'isFiltered']) }}
                {{ Form::hidden('queryString', null, ['id' => 'queryString']) }}
                {{ Form::hidden('tab', null, ['id' => 'tab']) }}
                <div class="form-group col-lg-12">
                    {{ Form::label('email', trans('challenges.modal.export.form.labels.email')) }}
                    {{ Form::text('email', $loginemail, ['class' => 'form-control', 'placeholder' => trans('challenges.modal.export.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'off']) }}
                    <span id="emailError" class="text-danger" style="display:none"></span>
                </div>
                <div class="input-daterange daterangesFromExportModel">
                    <div class="form-group col-lg-12">
                        {{ Form::label('start_date', trans('challenges.modal.export.form.labels.from_date'), ['id' => 'start_date_label']) }}
                        {{ Form::text('start_date', null, ['id' => 'start_date_app', 'class' => 'form-control datepicker', 'placeholder' => trans('userregistration.filter.from_date'), 'readonly' => true, 'style'=>'text-align:left']) }}
                    </div>
                    <div class="form-group col-lg-12">
                        {{ Form::label('end_date', trans('challenges.modal.export.form.labels.to_date'),['id' => 'to_date_label']) }}
                        {{ Form::text('end_date', null, ['id' => 'start_date_app', 'class' => 'form-control datepicker', 'placeholder' => trans('userregistration.filter.to_date'), 'readonly' => true, 'style'=>'text-align:left']) }}
                    </div>
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
            <div class="modal-body" id="exportNpsReportMsg" style="display: none">
                {{ trans('challenges.modal.export.message') }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
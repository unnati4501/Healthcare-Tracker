<div class="modal fade" data-id="0" id="export-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{trans('contentreport.modal.export_content_report')}}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    {{ Form::label('email', trans('contentreport.modal.email_address')) }}
                    {{ Form::text('email', $loginemail, ['class' => 'form-control', 'placeholder' => trans('contentreport.modal.enter_email_address'), 'id' => 'email', 'autocomplete' => 'off']) }}
                    <span id="emailError" class="text-danger" style="display:none"></span>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                        {{trans('buttons.general.cancel')}}
                    </button>
                    <button class="btn btn-primary" id="export-model-box-confirm" type="button">
                        {{trans('buttons.general.export')}}
                    </button>
                </div>
            </div>
            <div class="modal-body" id="exportContentMsg" style="display: none">
                {{trans('contentreport.modal.report_running_background')}}
            </div>
        </div>
    </div>
</div>
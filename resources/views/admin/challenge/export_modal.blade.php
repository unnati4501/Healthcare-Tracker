<div class="modal fade" data-id="0" id="export-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'admin.interCompanyChallenges.exportReport', 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'exportIntercompanychallenge', 'files' => false]) }}
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('challenges.modal.export.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body" id="exportChallenge">
                <input id="challengeId" name="challengeId" type="hidden" value=""/>
                <input id="exportFrom" name="exportFrom" type="hidden" value=""/>
                <input id="totalmembers" name="totalmembers" type="hidden" value=""/>
                <input id="totalteams" name="totalteams" type="hidden" value=""/>
                <input id="totalcompanies" name="totalcompanies" type="hidden" value=""/>
                <input id="exportRoute" name="exportRoute" type="hidden" value=""/>
                <div class="form-group col-lg-12">
                    {{ Form::label('email', trans('challenges.modal.export.form.labels.email')) }}
                    {{ Form::text('email', $loginemail, ['class' => 'form-control', 'placeholder' => trans('challenges.modal.export.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'off']) }}
                </div>
                <div class="form-group col-lg-12 start-date">
                    {{ Form::label('start_date', trans('challenges.modal.export.form.labels.from_date')) }}
                    {{ Form::text('start_date', null, ['class' => 'form-control datepicker', 'placeholder' => trans('challenges.modal.export.form.placeholders.from_date'), 'id' => 'start_date', 'readonly' => true]) }}
                </div>
                <div class="form-group col-lg-12 end-date">
                    {{ Form::label('end_date', trans('challenges.modal.export.form.labels.to_date')) }}
                    {{ Form::text('end_date', null, ['class' => 'form-control datepicker', 'placeholder' => trans('challenges.modal.export.form.placeholders.to_date'), 'id' => 'end_date', 'readonly' => true]) }}
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
            <div class="modal-body" id="exportChallengeMsg" style="display: none">
                {{ trans('challenges.modal.export.message') }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<div class="modal fade" data-id="0" id="export-clientlist-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'admin.cronofy.clientlist.export-client', 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'exportClientReport', 'files' => false]) }}
            <div class="modal-header">
                <h5 class="modal-title" id="model-title"></h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div id="exportClient">
                <div class="modal-body">
                    {{ Form::hidden('type', null, ['id' => 'type']) }}
                    <div class="container">
                        <div class="form-group">
                            {{ Form::label('email', trans('Cronofy.client_list.details.modal.export.email_address')) }}
                            {{ Form::text('email', $loginemail, ['class' => 'form-control', 'placeholder' => trans('Cronofy.client_list.details.modal.export.enter_email_address'), 'id' => 'email', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>
                {{ Form::hidden('name', request()->get('name'), ['class' => 'form-control', 'placeholder' => trans('Cronofy.client_list.filters.name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                {{ Form::hidden('searchemail', request()->get('email'), ['class' => 'form-control', 'placeholder' => trans('Cronofy.client_list.filters.email'), 'id' => 'searchemail', 'autocomplete' => 'off']) }}
                {{ Form::hidden('ws', request()->get('ws'), ['class' => 'form-control', 'id' => 'ws', 'placeholder' => "", 'data-placeholder' => "Select wellbeing specialist", 'data-allow-clear' => 'true']) }}
                {{ Form::hidden('company', request()->get('company'), ['class' => 'form-control', 'id' => 'company', 'placeholder' => trans('Cronofy.client_list.filters.company'), 'data-placeholder' => trans('Cronofy.client_list.filters.company'), 'data-allow-clear' => 'true']) }}
                {{ Form::hidden('location', request()->get('location'), ['class' => 'form-control', 'id' => 'location', 'disabled' => (Request()->get('company') != null ? false : true ), 'placeholder' => trans('Cronofy.client_list.filters.location'), 'data-placeholder' => trans('Cronofy.client_list.filters.location'), 'data-allow-clear' => 'true']) }}
                <div class="modal-footer">
                    <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                        {{trans('buttons.general.cancel')}}
                    </button>
                    <button class="btn btn-primary" id="export-model-box-confirm" type="submit">
                        {{trans('buttons.general.export')}}
                    </button>
                </div>
            </div>
            <div class="modal-body" id="exportMsg" style="display: none">
                {{trans('Cronofy.client_list.details.modal.export.report_running_background')}}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
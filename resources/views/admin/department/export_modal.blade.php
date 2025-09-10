<div class="modal fade" data-id="0" id="export-model-box" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'admin.departments.exportDepartments', 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'exportReport', 'files' => false]) }}
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
                {{ Form::hidden('queryString', null, ['id' => 'queryString']) }}
                <div class="form-group col-lg-12">
                    {{ Form::label('email', trans('challenges.modal.export.form.labels.email')) }}
                    {{ Form::text('email', $loginemail, ['class' => 'form-control', 'placeholder' => trans('challenges.modal.export.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'off']) }}
                  
                </div>
                @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->is_reseller == true))
                <div class="form-group col-lg-12">
                    @if(!empty(request()->get('company')) && request()->get('company') != '')
                    {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'placeholder' => trans('department.filter.select_company'), 'data-placeholder' => trans('department.filter.select_company'), 'id' => 'company', 'data-allow-clear' => 'true', 'disabled'=>true]) }}
                    @else
                    {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'placeholder' => trans('department.filter.select_company'), 'data-placeholder' => trans('department.filter.select_company'), 'id' => 'company', 'data-allow-clear' => 'true']) }}
                    @endif
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="export-model-box-confirm" type="submit">
                    {{ trans('buttons.general.export') }}
                </button>
            </div>
            <div class="modal-body" id="exportReportMsg" style="display: none">
                {{ trans('challenges.modal.export.message') }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
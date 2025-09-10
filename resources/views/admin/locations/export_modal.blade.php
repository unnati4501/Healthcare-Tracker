<div class="modal fade" data-id="0" id="export-model-box" role="dialog" tabindex="-1" >
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(['route' => 'admin.departments.exportDepartments', 'class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'exportLocationReport', 'files' => false]) }}
            <div class="modal-header">
                <h5 class="modal-title" id="model-title">
                    {{ trans('customersatisfaction.modal.export.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body" id="exportLocations">
                {{ Form::hidden('queryString', null, ['id' => 'queryString']) }}
                <div class="form-group col-lg-12">
                    {{ Form::label('email', trans('challenges.modal.export.form.labels.email')) }}
                    {{ Form::text('email', $loginemail, ['class' => 'form-control', 'placeholder' => trans('challenges.modal.export.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'off']) }}
                  
                </div>
                <div class="form-group col-lg-12">
                    @if(!empty(request()->get('country')) && request()->get('country') != '')
                    {{ Form::select('country', $countries, request()->get('country'), ['class' => 'form-control select2','id'=>'countrySearch', 'placeholder' => trans('location.filter.search_country'), 'data-placeholder' => trans('location.filter.search_country'), 'target-data' => 'timezone', 'autocomplete' => 'off',  'disabled'=>true] ) }}
                    @else
                    {{ Form::select('country', $countries, request()->get('country'), ['class' => 'form-control select2','id'=>'countrySearch', 'placeholder' => trans('location.filter.search_country'), 'data-placeholder' => trans('location.filter.search_country'), 'target-data' => 'timezone', 'autocomplete' => 'off'] ) }}
                    @endif
                </div>
                <div class="form-group col-lg-12">
                    @if(!empty(request()->get('timezone')) && request()->get('timezone') != '')
                    {{ Form::select('timezone', ($timezone ?? []), request()->get('timezone'), ['class' => 'form-control select2','id'=>'timezoneSearch',"style"=>"width: 100%;", 'placeholder' => trans('location.filter.search_timezone'), 'data-placeholder'=>trans('location.filter.search_timezone'), 'autocomplete' => 'off', 'disabled' => true] ) }}
                    @else
                    {{ Form::select('timezone', ($timezone ?? []), request()->get('timezone'), ['class' => 'form-control select2','id'=>'timezoneSearch',"style"=>"width: 100%;", 'placeholder' => trans('location.filter.search_timezone'), 'data-placeholder'=>trans('location.filter.search_timezone'), 'autocomplete' => 'off'] ) }}
                    @endif
                </div>
                <div class="form-group col-lg-12">
                    @if(!empty(request()->get('country')) && request()->get('country') != '')
                    {{ Form::select('county', ($states ?? []), request()->get('county'), ['class' => 'form-control select2', 'id'=>'stateSearch', "style"=>"width: 100%;", 'placeholder' => trans('location.filter.search_county'), 'data-placeholder' => trans('location.filter.search_county'), 'autocomplete' => 'off', 'disabled' => true] ) }}
                    @else
                    {{ Form::select('county', ($states ?? []), request()->get('county'), ['class' => 'form-control select2','id'=>'stateSearch',"style"=>"width: 100%;", 'placeholder' => trans('location.filter.search_county'), 'data-placeholder'=>trans('location.filter.search_county'), 'autocomplete' => 'off'] ) }}
                    @endif
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
            <div class="modal-body" id="exportReportMsg" style="display: none">
                {{ trans('challenges.modal.export.message') }}
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
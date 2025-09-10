<div category="dialog" class="modal fade" data-id="0" id="addUser-model-box" tabindex="-1">
    <div category="document" class="modal-dialog">
        <div class="modal-content">
            {{ Form::open(['class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'adminAlertUserForm']) }}
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">
                    {{ trans('adminalert.model_popup.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                {{ Form::hidden("edit", 0, ['class' => 'form-control', 'id' => 'editflag']) }}
                <div class="form-group col-xl-12">
                    {{ Form::label('user_name', trans('adminalert.form.labels.user_name')) }}
                    {{ Form::text('user_name', old('user_name') ?? null, ['class' => 'form-control', 'placeholder' => trans('adminalert.form.placeholder.user_name'), 'id' => 'user_name', 'autocomplete' => 'off', 'maxlength' => 50]) }}
                    <div id="user_name-error-cstm" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                        </div>
                </div>
                <div class="form-group col-xl-12">
                    {{ Form::label('user_email', trans('adminalert.form.labels.user_email')) }}
                    {{ Form::text('user_email', old('user_email') ?? null, ['class' => 'form-control', 'placeholder' => trans('adminalert.form.placeholder.user_email'), 'id' => 'user_email', 'autocomplete' => 'off', 'maxlength' => 255]) }}
                    <div id="user_email-error-cstm" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                        </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary subcategory-save" id="userSave" type="button">
                    {{ trans('buttons.general.save') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
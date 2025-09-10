<div class="card-inner">
	<div class="row">
    <input type="hidden" id="last_user_id" value="{{$getLastInsertedUser->id ?? 0}}">
		<div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('title', trans('adminalert.form.labels.alert_name')) }}
                {{ Form::text('title', old('title', ($record->title ?? $title)), ['class' => 'form-control', 'placeholder' => trans('Cronofy.consent_form.form.placeholder.title'), 'id' => 'title', 'autocomplete' => 'off', 'readonly' => true]) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-xl-12">
            <div class="form-group">
            	{{ Form::label('Description', trans('adminalert.form.labels.description')) }}
            	{{ Form::textarea('description', old('description', ($record->description ?? $description)), ['class' => 'form-control article-ckeditor', 'id' => 'description', 'data-errplaceholder' => '#description-error-cstm', 'data-formid' => "#description", 'data-upload-path' => route('admin.ckeditor-upload.consentform-description', ['_token' => csrf_token() ])]) }}
                    <div>
                        <small>
                            {{ trans('Cronofy.consent_form.message.fullscreen_mode_for_description') }}

                            <i class="fas fa-arrows-alt" style="transform: rotate(45deg);">
                            </i>
                            {{ trans('Cronofy.consent_form.message.from_toolbar') }}
                        </small>
                    </div>
                    <div id="description-error-cstm" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                    </div>
            </div>
        </div>
	</div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('adminalert.form.labels.users') }}
    </h3> 
    <div class="text-end">
        <a href="javascript:void(0)" class="btn btn-primary" id="addUser">
            <i class="far fa-plus me-3 align-middle">
            </i>
            {{ trans('adminalert.buttons.add_user') }}
        </a>
    </div>
    <div class="row">
        <div class="card-body">
            <div class="card-table-outer" id="userManagment-wrap">
                <div class="table-responsive">
                    <table class="table custom-table" id="usersTbl">
                        <thead>
                            <tr>
                                <th class="no-sort">
                                    {{ trans('adminalert.table.user_name') }}
                                </th>
                                <th class="no-sort">
                                    {{ trans('adminalert.table.user_email') }}
                                </th>
                                <th class="text-center th-btn-3 no-sort">
                                    {{ trans('adminalert.table.action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adminAlertUsers as $key => $value)
                                @include('admin.admin-alerts.users', [
                                    'key' => $key+1,
                                    'user_name' => $value['user_name'],
                                    'user_email' => $value['user_email'],
                                    'id' => $value['id']
                                ])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
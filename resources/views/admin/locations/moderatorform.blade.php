<div class="form-group col-xl-10 offset-xl-1">
    <div class="row">
        <div class="form-group col-md-4 col-sm-6">
            {{ Form::label('first_name', trans('labels.company.first_name')) }}
            {{ Form::text('first_name', old('first_name'), ['class' => 'form-control', 'placeholder' => 'Enter First Name', 'id' => 'first_name', 'autocomplete' => 'off']) }}
        </div>
        <div class="form-group col-md-4 col-sm-6">
            {{ Form::label('last_name', trans('labels.company.last_name')) }}
            {{ Form::text('last_name', old('last_name'), ['class' => 'form-control', 'placeholder' => 'Enter Last Name', 'id' => 'last_name', 'autocomplete' => 'off']) }}
        </div>
        <div class="form-group col-md-4 col-sm-6">
            {{ Form::label('email', trans('labels.company.email')) }}
            {{ Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => 'Enter Email', 'id' => 'email', 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>
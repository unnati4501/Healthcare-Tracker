@extends('layouts.auth')

@section('content')
{{ Form::open(['route' => 'setnewpassword', 'method'=>'POST','role' => 'form', 'id'=>'setPasswordForm']) }}
{{ Form::hidden('token', $token) }}
{{ Form::hidden('email', $email) }}
<div class="form-group form-group-bottom">
    {{ Form::email('email', $email, ['class' => 'form-control ps-0', 'placeholder' => trans('non-auth.set-password.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'on', 'disabled'=>'disabled']) }}
</div>
<div class="form-group form-group-bottom">
    {{ Form::password('password', ["placeholder"=> trans('non-auth.set-password.form.placeholders.password'), "class"=>"form-control ps-0", "id"=>"password"]) }}
</div>
<div class="form-group form-group-bottom">
    {{ Form::password('password_confirmation', ["placeholder"=> trans('non-auth.set-password.form.placeholders.confirm_password'), "class"=>"form-control ps-0", "id"=>"password-confirm"]) }}
</div>
<div class="form-group d-grid">
    {{ Form::submit(trans('non-auth.set-password.buttons.set'), array('class' => 'btn btn-primary')) }}
</div>
{{ Form::close() }}
@endsection

@section('after-scripts')
    {!! JsValidator::formRequest('App\Http\Requests\Auth\ResetPasswordRequest','#setPasswordForm') !!}
@endsection

@extends('layouts.auth')

@section('content')
{{ Form::open(['route' => 'resetPassword', 'method'=>'POST','role' => 'form', 'id'=>'resetPasswordForm']) }}
{{ Form::hidden('token', $token) }}
{{ Form::hidden('appUser', $appUser) }}
{{ Form::hidden('email', $email) }}
<div class="form-group form-group-bottom">
    {{ Form::email('email', $email, ['class' => 'form-control ps-0', 'placeholder' => trans('non-auth.reset-password.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'on', 'disabled'=>'disabled']) }}
</div>
<div class="form-group form-group-bottom">
    {{ Form::password('password', ["placeholder"=> trans('non-auth.reset-password.form.placeholders.password'), "class"=>"form-control ps-0", "id"=>"password"]) }}
</div>
<div class="form-group form-group-bottom">
    {{ Form::password('password_confirmation', ["placeholder"=> trans('non-auth.reset-password.form.placeholders.confirm_password'), "class"=>"form-control ps-0", "id"=>"password-confirm"]) }}
</div>
<div class="form-group d-grid">
    {{ Form::submit(trans('non-auth.reset-password.buttons.reset'), array('class' => 'btn btn-primary')) }}
</div>
{{ Form::close() }}
@endsection

@section('after-scripts')
    {!! JsValidator::formRequest('App\Http\Requests\Auth\ResetPasswordRequest','#resetPasswordForm') !!}
@endsection

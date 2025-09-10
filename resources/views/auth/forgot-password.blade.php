@extends('layouts.auth')

@section('content')
<div class="ps-3 pe-3">
    {{ Form::open(['route' => 'password.email', 'method'=>'POST','role' => 'form', 'id'=>'forgotPasswordForm']) }}
    <div class="form-group form-group-bottom">
        {{ Form::email('email', old('email'), ['class' => 'form-control ps-0', 'placeholder' => trans('non-auth.forgot-password.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'on']) }}
    </div>
    <div class="form-group mt-4 d-grid">
        {{ Form::submit(trans('non-auth.forgot-password.buttons.reset'), array('class' => 'btn btn-primary')) }}
    </div>
    {{ Form::close() }}
</div>
<div class="text-center m-t-vh-5 m-b-vh-5">
    <div>
        {{ trans('non-auth.forgot-password.texts.remember') }}
    </div>
    <a href="{{ route('login') }}">
        {{ trans('non-auth.forgot-password.links.login') }}
    </a>
</div>
@endsection

@section('after-scripts')
    {!! $validator = JsValidator::formRequest('App\Http\Requests\Auth\ForgotPasswordRequest','#forgotPasswordForm') !!}
@endsection

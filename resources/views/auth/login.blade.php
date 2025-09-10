@extends('layouts.auth')

@section('content')

{{ Form::open(['route' => 'login', 'method'=>'POST','role' => 'form', 'id'=>'loginForm']) }}
<input type="hidden" id="timezone" name="timezone" value="Europe/Dublin">
<div class="form-group form-group-bottom">
    {{ Form::email('email', old('email'), ['class' => 'form-control ps-0', 'placeholder' => trans('non-auth.login.form.placeholders.email'), 'id' => 'email', 'autocomplete' => 'on']) }}
</div>
<div class="form-group form-group-bottom password-field">
    {{ Form::password('password', ["placeholder"=> trans('non-auth.login.form.placeholders.password'), "class"=>"form-control ps-0", "id"=>"password", 'autocomplete' => 'off']) }}
</div>
{{ Form::hidden('type', 'password', ["class"=>"form-control ps-0", "id"=>"type"]) }}
<div class="form-group row remember-field">
    <div class="col">
        <label class="custom-checkbox custom-checkbox-r-border remember-field" for="remember">
            {{ trans('non-auth.login.form.placeholders.remember') }}
            <input id="remember" name="remember" type="checkbox"/>
            <span class="checkmark mt-0">
            </span>
            <span class="checkbox-line">
            </span>
        </label>
    </div>
    <div class="col-auto">
        @if (Route::has('password.request'))
        <a class="font-12" href="{{ route('password.request') }}">
            {{ trans('non-auth.login.links.forgot') }}
        </a>
        @endif
    </div>
</div>
<div class="form-group">
    <div>
        <div class="d-grid">{{ Form::submit(trans('non-auth.login.buttons.login'), array('class' => 'btn btn-primary login-btn')) }}</div>
       {{--  <a class="btn btn-block btn-primary login-btn-popup d-none" id="2fa" href="javascript:;">
            {{ trans('non-auth.login.buttons.login') }}
        </a> --}}
        <div class="text-center pb-2 pt-2 partition-line"><span> {{ trans('non-auth.login.texts.or') }}</span></div>
        <div class="login-btns-wrap d-flex">
            @if($disableSSO)
            <a class="btn btn-outline-primary microsoft-btn" href="{{ url($lang.'/login/azure') }}">
                <img class="btn-microsoft-img" src="{{asset('assets/dist/img/Microsoft-logo.svg')}}"/>
                {{ trans('non-auth.login.buttons.microsoft') }}
            </a>
            @endif
            <a class="btn btn-outline-primary 2fa-button" id="2fa" href="javascript:;">
                {{ trans('non-auth.login.buttons.login_with_2fa') }}
            </a>
        </div>
    </div>
</div>
{{ Form::close() }}
<!-- Delete Model Popup -->
@include('auth.otp-model')
@endsection

@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Auth\LoginRequest','#loginForm') !!}
<script type="text/javascript">
    zE("webWidget", "hide")
    zE('webWidget', 'setLocale', 'en');
    window.zESettings = {
        webWidget: {
            color: { theme: '#50c9b5' },
        }
    };
    window.zE('webWidget:on', 'close', function () {
        window.zE('webWidget', 'hide');
        $('.openwidget').show();
    });
    window.zE('webWidget:on', 'open', function () {
        window.zE('webWidget', 'show');
        setTimeout(function() {
            $('.openwidget').hide();
        }, 500);
    });
    var url = {
        send_email: `{{ route('send-otp') }}`,
        verify_otp: `{{ route('verify-otp') }}`,
        dashboad: `{{ route('dashboard') }}`,
    };
</script>
<script src="{{ mix('js/auth/login.js') }}" type="text/javascript">
</script>
@endsection

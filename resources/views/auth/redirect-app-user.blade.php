@extends('layouts.auth')

@section('content')
<div class="text-center m-b-vh-5">
    <img class="password-reset-img" src="{!! asset('assets/dist/img/checked.svg') !!}"/>
    <h3 class="mb-0">
        {{ trans('non-auth.redirect-app-user.texts.your') }} {{ config('app.name') }}
    </h3>
    <h3 class="mb-3">
        {{ trans('non-auth.redirect-app-user.texts.password_reset') }}
    </h3>
    <div class="text-center">
        <a class="btn btn-primary" href="{{ url('/login') }}">
            {{ trans('non-auth.redirect-app-user.links.login') }}
        </a>
    </div>
    <div class="or-devider">
        <hr/>
        <div class="text-center">
            {{ trans('non-auth.redirect-app-user.texts.or') }}
        </div>
    </div>
    <p>
        {{ trans('non-auth.redirect-app-user.texts.login_mobile') }}
    </p>
    <p class="gray-500">
        {{ trans('non-auth.redirect-app-user.texts.can_download') }}
        {{ config('app.name') }} 
        {{ trans('non-auth.redirect-app-user.texts.app_from') }}
    </p>
    <div class="row">
        <div class="col-6 mb-3">
            <div class="app-store-box-link">
                <a class="app-store-link-icon" href="{{ config('zevolifesettings.app_store_link.android') }}">
                    <i class="fab fa-android">
                    </i>
                </a>
                <a href="{{ config('zevolifesettings.app_store_link.android') }}" title="{{ config('app.name') }} for Android">
                    {{ trans('non-auth.redirect-app-user.links.android') }}
                </a>
            </div>
        </div>
        <div class="col-6 mb-3">
            <div class="app-store-box-link">
                <a class="app-store-link-icon" href="{{ config('zevolifesettings.app_store_link.ios') }}">
                    <i class="fab fa-apple">
                    </i>
                </a>
                <a href="{{ config('zevolifesettings.app_store_link.ios') }}" title="{{ config('app.name') }} for Apple">
                    {{ trans('non-auth.redirect-app-user.links.apple') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
@endsection

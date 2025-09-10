@extends('errors.layouts.error-layout')

@section('code', '404 ERROR')
@section('title', __('Page Not Found'))

@section('image')
    <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/404.svg')}}" alt="">
@endsection

@section('message', __('Sorry, the page you are looking for could not be found.'))
{{--@section('message', __($exception->getMessage() ?: 'SORRY, THE PAGE NOT FOUND'))--}}


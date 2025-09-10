@extends('errors.layouts.error-layout')

@section('code', '503')
@section('title', __('Service under maintenance'))

@section('image')
    <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/404.svg')}}" alt="">
@endsection

@section('message', __('Sorry, we are doing some maintenance. Please check back soon.'))
{{--@section('message', __($exception->getMessage() ?: 'Sorry, we are doing some maintenance. Please check back soon.'))--}}

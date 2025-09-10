@extends('errors.layouts.error-layout')

@section('code', '419')
@section('title', __('Page Expired'))

@section('image')
    <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/404.svg')}}" alt="">
@endsection

@section('message', __('Sorry, your session has expired. Please refresh and try again or Check if your third party cookies are enabled from your browser.'))

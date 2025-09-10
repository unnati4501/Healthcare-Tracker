@extends('errors.layouts.error-layout')

@section('code', '429')
@section('title', __('Too Many Requests'))

@section('image')
    <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/404.svg')}}" alt="">
@endsection

@section('message', __('Sorry, you are making too many requests to our servers.'))

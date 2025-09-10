@extends('errors.layouts.error-layout')

@section('title', __('Bad request'))
@section('code', '')

@section('image')
<img alt="400 Bad Request" class="error-msg-img mb-4" src="{{ asset('assets/dist/img/404.svg')}}"/>
@endsection

@section('message', __('It seems request is malformed or bad.'))

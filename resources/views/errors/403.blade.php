@extends('errors.layouts.error-layout')
@section('code', '403 ERROR')
@section('title', __('Forbidden'))

@section('image')
    <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/404.svg')}}" alt="">
@endsection

{{-- @section('message', __('This action is unauthorized.')) --}}
@section('message', __($exception->getMessage() ?: 'This action is unauthorized.'))

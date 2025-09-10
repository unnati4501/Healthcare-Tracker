@extends('errors.layouts.error-layout')

@section('code', '500')
@section('title', __('Error'))

@section('image')
    <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/404.svg')}}" alt="">
@endsection

@section('message', __($exception->getMessage()) ?: 'SORRY, INTERNAL SERVER ERROR')

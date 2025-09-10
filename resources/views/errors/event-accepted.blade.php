@extends('errors.layouts.error-layout')

@section('title', __('Error'))
@section('code', '')

@section('image')
    <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/404.svg')}}" alt="">
@endsection

@section('message', __('It seems your event is accepted'))

@if(!empty($sub_message))
@section('sub_message')
<p>
    {{ $sub_message }}
</p>
@endsection
@endif

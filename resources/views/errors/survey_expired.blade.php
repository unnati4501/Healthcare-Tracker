@extends('errors.layouts.error-layout')

@section('title', __('Survey expired!'))
@section('code', '')

@section('image')
<img alt="Survey expired" class="error-msg-img mb-4" src="{{ asset('assets/dist/img/icons/expired.svg') }}" title="Survey expired"/>
@endsection

@if(!empty($message))
	@section('message', __($message))
@else
	@section('message', __('This survey has been expired!'))
@endif

@if(!empty($sub_message))
@section('sub_message')
<p>
    {{ $sub_message }}
</p>
@endsection
@endif

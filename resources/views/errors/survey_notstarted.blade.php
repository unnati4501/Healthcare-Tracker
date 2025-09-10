@extends('errors.layouts.error-layout')

@section('title', __('Survey not started!'))
@section('code', '')

@section('image')
<img alt="Survey not started" class="error-msg-img mb-4" src="{{ asset('assets/dist/img/feedback/not-started.svg') }}" title="Survey not started"/>
@endsection

@if(!empty($message))
	@section('message', __($message))
@else
	@section('message', __('This survey not started yet!'))
@endif

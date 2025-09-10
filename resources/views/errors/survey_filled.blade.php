@extends('errors.layouts.error-layout')

@section('title', __('Survey already submitted!'))
@section('code', '')

@section('image')
<img alt="Survey already submitted" class="error-msg-img mb-4" src="{{ asset('assets/dist/img/icons/filled.svg') }}" title="Survey already submitted"/>
@endsection

@if(!empty($message))
	@section('message', __($message))
@else
	@section('message', __('You have already submitted the survey!'))
@endif

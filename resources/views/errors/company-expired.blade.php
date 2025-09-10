@extends('errors.layouts.error-layout')

@section('title', __('Company subscription isn\'t active!'))
@section('code', '')

@section('image')
<img alt="Company subscription isn't active" class="error-msg-img mb-4" src="{{ asset('assets/dist/img/feedback/status-expired.svg') }}" title="Company subscription isn't active"/>
@endsection

@section('message', __('It seems your company\'s subscription isn\'t active.'))

@if(!empty($sub_message))
@section('sub_message')
<p>
    {{ $sub_message }}
</p>
@endsection
@endif

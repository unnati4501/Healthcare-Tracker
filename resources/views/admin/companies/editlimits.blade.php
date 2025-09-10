@extends('layouts.app')
@section('after-styles')
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ trans('company.limit.title.edit') }}
                </h1>
                {{ Breadcrumbs::render('companies.limits.edit', $companyType, $company->id) }}
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.companies.updateLimits', [$companyType, $company->id]], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'editLimits']) }}
            <!-- .challenge -->
            @if($type == "challenge")
            <div class="card-body">
                <div class="card-inner">
                    <h3 class="card-inner-title">
                        {!! trans('company.limit.tabs.challenge_activity', ['split' => '']) !!}
                    </h3>
                    <div class="row">
                        @foreach($challenge_targets as $key => $value)
                        @if($key != 'exercises')
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label($key, $value) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title='{{ config("zevolifesettings.default_limits_message.$key", $key) }}'>
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="input-group mb-3 symbol-end">
                                <span class="input-group-text">
                                            {{ ((array_key_exists($key,$uom)) ? $uom[$key] : "") }}
                                            {{ form::hidden("uom[$key]", $uom[$key]) }}
                                        </span>    
                                {{ Form::text($key, old($key,(!empty($limitsData[$key]))? $limitsData[$key] : @$default_limits[$key] ), ['class' => 'form-control', 'placeholder' => trans('company.limit.form.placeholder.enter', ['value' => $value]), 'id' => $key, 'autocomplete' => 'off']) }}
                                       
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label($key."_distance", $value) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title='{{ config("zevolifesettings.default_limits_message.exercises_distance", $key."_distance") }}'>
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="input-group mb-3 symbol-end">
                                <span class="input-group-text">
                                        {{ ((array_key_exists($key,$uom)) ? $uom[$key][0] : "") }}
                                        {{ form::hidden("uom[{$key}_distance]", $uom[$key][0]) }}
                                    </span>    
                                {{ Form::text($key."_distance", old($key."_distance",(!empty($limitsData[$key."_distance"]))? $limitsData[$key."_distance"] : @$default_limits[$key."_distance"] ), ['class' => 'form-control', 'placeholder' => trans('company.limit.form.placeholder.enter', ['value' => $value]), 'id' => $key."_distance", 'autocomplete' => 'off']) }}
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label($key."_duration", $value) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title='{{ config("zevolifesettings.default_limits_message.exercises_duration", $key."_duration") }}'>
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="input-group mb-3 symbol-end">
                                <span class="input-group-text">
                                        {{ ((array_key_exists($key,$uom)) ? $uom[$key][1] : "") }}
                                        {{ form::hidden("uom[{$key}_duration]", $uom[$key][1]) }}
                                    </span>    
                                {{ Form::text($key."_duration", old($key."_duration",(!empty($limitsData[$key."_duration"]))? $limitsData[$key."_duration"] : @$default_limits[$key."_duration"] ), ['class' => 'form-control', 'placeholder' => trans('company.limit.form.placeholder.enter', ['value' => $value]), 'id' => $key."_duration", 'autocomplete' => 'off']) }}
                                    
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                        {{-- Daily meditation limit - total number of meditations that users earn points for in a day --}}
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label("daily_meditation_limit", "Daily meditation limit") }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title='{{ config("zevolifesettings.default_limits_message.daily_meditation_limit", $key) }}'>
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="input-group mb-3 symbol-end">
                                <span class="input-group-text">
                                        {{ trans('company.limit.form.labels.count') }}
                                        {{ form::hidden("uom[daily_meditation_limit]", "Count") }}
                                    </span>   
                                {{ Form::text("daily_meditation_limit", old("daily_meditation_limit", ((!empty($limitsData["daily_meditation_limit"])) ? $limitsData["daily_meditation_limit"] : @$default_limits["daily_meditation_limit"])), ['class' => 'form-control', 'placeholder' => trans('company.limit.form.placeholder.daily-meditation-limit'), 'id' => "daily_meditation_limit", 'autocomplete' => 'off']) }}
                                   
                                </div>
                            </div>
                        </div>
                        {{-- Daily track limit - total number of points for a single track in a day --}}
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label("daily_track_limit", "Daily track limit") }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title='{{ config("zevolifesettings.default_limits_message.daily_track_limit", $key) }}'>
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="input-group mb-3 symbol-end">
                                <span class="input-group-text">
                                        {{ trans('company.limit.form.labels.count') }}
                                        {{ form::hidden("uom[daily_track_limit]", "Count") }}
                                    </span>    
                                {{ Form::text("daily_track_limit", old("daily_track_limit", ((!empty($limitsData["daily_track_limit"])) ? $limitsData["daily_track_limit"] : @$default_limits["daily_track_limit"])), ['class' => 'form-control', 'placeholder' => trans('company.limit.form.placeholder.daily-meditation-limit'), 'id' => "daily_track_limit", 'autocomplete' => 'off']) }}
                                  
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label("daily_podcast_limit", "Daily podcast limit") }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title='{{ config("zevolifesettings.default_limits_message.daily_podcast_limit", $key) }}'>
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="input-group mb-3 symbol-end">
                                <span class="input-group-text">
                                        {{ trans('company.limit.form.labels.count') }}
                                        {{ form::hidden("uom[daily_podcast_limit]", "Count") }}
                                    </span>
                                    {{ Form::text("daily_podcast_limit", old("daily_podcast_limit", ((!empty($limitsData["daily_podcast_limit"])) ? $limitsData["daily_podcast_limit"] : @$default_limits["daily_podcast_limit"])), ['class' => 'form-control', 'placeholder' => trans('company.limit.form.placeholder.daily-podcast-limit'), 'id' => "daily_podcast_limit", 'autocomplete' => 'off']) }}
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <!-- /.challenge -->
            <!-- .reward -->
            @if($type == "reward")
            <div class="card-body">
                <div class="card-inner">
                    <h3 class="card-inner-title">
                        {!! trans('company.limit.tabs.reward_activity', ['split' => '']) !!}
                    </h3>
                    <div class="row">
                        @foreach($portal_limits as $limitKey => $limit)
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label($limitKey, $limit) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ $default_portal_limits_message[$limitKey] }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="input-group mb-3 symbol-end">
                                <span class="input-group-text">
                                        {{ trans('company.limit.form.labels.points') }}
                                    </span>
                                    {{ Form::text($limitKey, old($limitKey, ($company_portal_limits[$limitKey] ?? $default_portal_limits[$limitKey])), ['class' => 'form-control', 'placeholder' => trans('company.limit.form.placeholder.enter-limits', ['limit' => $limit]), 'id' => $limitKey]) }}
                                    
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            <!-- /.challenge -->
            <!-- .reward-daily-limit -->
            @if($type == "reward-daily-limit")
            <div class="card-body">
                <div class="card-inner">
                    <h3 class="card-inner-title">
                        {!! trans('company.limit.tabs.reward_point_limit', ['split' => '']) !!}
                    </h3>
                    <div class="row">
                        @foreach($reward_point_labels as $limitKey => $limit)
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label("dailylimit_" . $limitKey, $limit) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ $reward_point_daily_limit_message[$limitKey] }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="input-group mb-3 symbol-end">
                                <span class="input-group-text">
                                        {{ trans('company.limit.form.labels.per-day') }}
                                    </span>
                                    {{ Form::text("dailylimit_" . $limitKey, old($limitKey, ($company_reward_point_limits[$limitKey] ?? $reward_point_daily_limit[$limitKey])), ['class' => 'form-control', 'placeholder' => trans('company.limit.form.placeholder.enter-limits-pd', ['limit' => $limit]), 'id' => $limitKey]) }}
                                  
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            <!-- ./reward-daily-limit -->
            <div class="card-footer">
                {{ Form::hidden('type', $type) }}
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ $cancel_url }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditLimitRequest', '#editLimits') !!}
<script type="text/javascript">
    $(document).ready(function() {
        $("input[type=text]").focusout(function () {
            $(this).val($.trim($(this).val()).replace(/^0+/, ''));
        });
    });
</script>
@endsection

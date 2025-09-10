@extends('layouts.app')

@section('content')
@include('admin.team.breadcrumb', [
    'appPageTitle' => trans('labels.team.set_limit'),
    'breadcrumb' => 'team.setlimit',
    'create' => false,
    'setLimit' => false,
])
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.teams.updateTeamLimit', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'frmSetTeamLimit']) }}
            <div class="card-body">
                <div class="row justify-content-center justify-content-md-start">
                    <div class="col-lg-6 col-xl-6">
                        <div class="form-group">
                            <div>
                                <label class="custom-checkbox no-label" for="auto_team_creation">
                                    {{ trans('labels.team.auto_team_creation') }}
                                    {{ Form::checkbox('auto_team_creation', 'on', old('auto_team_creation', $autoTeamCreationValue), ['id' => 'auto_team_creation']) }}
                                    <span class="checkmark">
                                    </span>
                                    <span class="box-line">
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-6 {{ $visibilityClass }}" id="team_limit_block">
                        <div class="form-group">
                            {{ Form::label('team_limit', trans('team.form.labels.team_limit')) }}
                            {{ Form::text('team_limit', old('team_limit', $teamLimit), ['class' => 'form-control', 'placeholder' => trans('team.form.placeholder.auto_team_creation'), 'data-placeholder' => trans('team.form.placeholder.auto_team_creation'), 'id' => 'team_limit', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                <a class="btn btn-outline-primary" href="{{ route('admin.teams.index') }}">
                    {{trans('labels.buttons.cancel')}}
                </a>
                <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                    {{trans('labels.buttons.save')}}
                </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\SetTeamLimitRequest', '#frmSetTeamLimit') !!}
<script src="{{ asset('js/team/set-limit.js') }}" type="text/javascript">
</script>
@endsection

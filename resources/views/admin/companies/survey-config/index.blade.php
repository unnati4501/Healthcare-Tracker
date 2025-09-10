@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
<style type="text/css">
    .tree-error { width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ trans('company.survey_configuration.title.index', [
                        'company' => $company->name
                    ]) }}
                </h1>
                {{ Breadcrumbs::render('companies.survey-configuration',$companyType) }}
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.companies.set-survey-configuration', $company->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'surveyConfigForm']) }}
        {{Form::hidden('companyType', $companyType,['id' => 'companyType'])}}
        <div class="card form-card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-xl-4">
                        <div class="form-group">
                            {{ Form::label('survey_for_all', trans('company.survey_configuration.form.allow_survey_for_all')) }}
                            <div>
                                <label class="custom-radio" for="survey_for_all_yes">
                                    {{ trans('buttons.general.yes') }}
                                    {{ Form::radio('survey_for_all', 'on', old('survey_for_all', $survey_to_all), ['id' => 'survey_for_all_yes', 'class' => 'form-control']) }}
                                    <span class="checkmark">
                                    </span>
                                    <span class="box-line">
                                    </span>
                                </label>
                                <label class="custom-radio" for="survey_for_all_no">
                                    {{ trans('buttons.general.no') }}
                                    {{ Form::radio('survey_for_all', 'off', old('survey_for_all', !$survey_to_all), ['id' => 'survey_for_all_no', 'class' => 'form-control']) }}
                                    <span class="checkmark">
                                    </span>
                                    <span class="box-line">
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-inner {{ $surveyUsersVisibility }} d-none" id="surveyUsersWrapper">
                    <h3 class="card-inner-title">
                        {{ trans('company.survey_configuration.form.select_users') }}
                    </h3>
                    <div class="tree-multiselect-box" id="selectetSurveyUsers">
                        {{ Form::label('survey-users', trans('labels.company.select_users')) }}
                        <select class="form-control" id="survey-users" multiple="multiple" name="survey-users">
                            {!! $users !!}
                        </select>
                    </div>
                    <span class="tree-error" id="survey-users-error" style="display: none;">
                        {{ trans('company.survey_configuration.messages.select_users') }}
                    </span>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.companies.index', $companyType) }}">
                        {{ trans('labels.buttons.cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('labels.buttons.save') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateSurveyUsersRequest', '#surveyConfigForm') !!}
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    $(document).ready(function() {
       var companyType = $("#companyType").val(); 
    });
    var redirectRoute = `{{ route('admin.companies.index', ':companyType') }}`;
    var 
    url = {
        success: redirectRoute.replace(':companyType', $("#companyType").val()),
    },
    messages = {
        error: `{{ trans('labels.common_title.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ asset('js/company/survey-config.js') }}" type="text/javascript">
</script>
@endsection

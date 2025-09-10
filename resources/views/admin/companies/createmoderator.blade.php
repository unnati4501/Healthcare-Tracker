@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ trans('labels.company.create_moderator') }}
                </h1>
                {{ Breadcrumbs::render('companies.moderator.create', $companyType, $company->id) }}
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.companies.storeModerator', $companyType, $company->id, 'referrer' => $referrer], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'storeModerator']) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-lg-6 col-xl-4">
                            <div class="form-group">
                                {{ Form::label('first_name', trans('labels.company.first_name')) }}
                                {{ Form::text('first_name', old('first_name'), ['class' => 'form-control', 'placeholder' => 'Enter First Name', 'id' => 'first_name', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-4">
                            <div class="form-group">
                                {{ Form::label('last_name', trans('labels.company.last_name')) }}
                                {{ Form::text('last_name', old('last_name'), ['class' => 'form-control', 'placeholder' => 'Enter Last Name', 'id' => 'last_name', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-4">
                            <div class="form-group">
                                {{ Form::label('email', trans('labels.company.email')) }}
                                {{ Form::text('email', old('email'), ['class' => 'form-control', 'placeholder' => 'Enter Email', 'id' => 'email', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ $cancel_url }}">
                        {{ trans('labels.buttons.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateCompanyModeratorRequest', '#storeModerator') !!}
@endsection

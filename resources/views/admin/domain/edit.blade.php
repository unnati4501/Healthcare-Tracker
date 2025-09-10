@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.domain.breadcrumb', [
    'appPageTitle' => trans('domain.title.edit_form_title'),
    'breadcrumb' => 'domains.edit',
    'create' => false
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.domains.update',$domainData->id], 'class' => 'form-horizontal', 'method'=>'PATCH','role' => 'form', 'id'=>'domainEdit']) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="row justify-content-center justify-content-md-start">
                    @include('admin.domain.form')
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.domains.index') !!}">{{trans('buttons.general.cancel')}}</a>
                    <button type="submit" class="btn btn-primary">{{trans('buttons.general.update')}}</button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditDomainRequest','#domainEdit') !!}
@endsection
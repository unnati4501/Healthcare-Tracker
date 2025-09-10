@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.domain.breadcrumb', [
    'appPageTitle' => trans('domain.title.add_form_title'),
    'breadcrumb' => 'domains.create',
    'create' => false
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.domains.store', 'class' => 'form-horizontal', 'method'=>'post','role' => 'form', 'id'=>'domainAdd']) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="row justify-content-center justify-content-md-start">
                    @include('admin.domain.form')
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.domains.index') !!}" >{{trans('buttons.general.cancel')}}</a>
                    <button type="submit" class="btn btn-primary">
                        {{trans('buttons.general.save')}}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateDomainRequest','#domainAdd') !!}
@endsection
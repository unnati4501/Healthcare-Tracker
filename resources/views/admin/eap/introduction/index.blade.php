@extends('layouts.app')
<style type="text/css">
.introducetextarea{
    height: 200px !important;
}
</style>
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.eap.introduction.breadcrumb',[
    'mainTitle' => trans('eap.title.eap_introduction'),
    'breadcrumb' => 'eap.introduction',
    'back' => true
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.support.introduction', 'class' => 'form-horizontal', 'method'=>'post','role' => 'form', 'id'=>'eapIntroductionStore']) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="form-group">
                    <label for=" ">
                        {{ trans('eap.form.labels.introduction') }}
                    </label>
                    {{ Form::textarea('introduction', old('introduction', ($introduction)), ['class' => 'form-control introducetextarea', 'placeholder' => trans('eap.form.placeholder.introduction_placeholder'), 'id' => 'introduction', 'rows' => 60]) }}
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.support.list') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{trans('buttons.general.update')}}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}

    </div>
</section>
@endsection
@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\EditEAPIntroductionRequest','#eapIntroductionStore') !!}
@endsection

@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.slides.breadcrumb', [
    'appPageTitle' => trans('appslides.title.edit_form_title'),
    'breadcrumb' => 'appslides.edit'
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.appslides.update',$id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'slideEdit','files' => true]) }}
            <div class="card-body">
                @include('admin.slides.form')
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.appslides.index', array('#'.$appSlideData->type)) !!}">{{trans('buttons.general.cancel')}}</a>
                    <button type="submit" class="btn btn-primary" id="zevo_submit_btn">{{trans('buttons.general.update')}}</button>
                </div>
            </div>
          {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditSlideRequest','#slideEdit') !!}
<script type="text/javascript">
var message = {
    image_valid_error: `{{trans('appsettings.message.image_valid_error')}}`,
    image_size_2M_error: `{{trans('appsettings.message.image_size_2M_error')}}`,
    upload_image_dimension: `{{trans('appsettings.message.upload_image_dimension')}}`,
};
</script>
<script src="{{ asset('js/appslides/create-edit.js') }}" type="text/javascript">
</script>
@endsection
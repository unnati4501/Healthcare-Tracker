@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.slides.breadcrumb', [
    'appPageTitle' => trans('appslides.title.add_form_title'),
    'breadcrumb' => 'appslides.add'
])
<!-- /.content-header -->
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card form-card">
                <!-- /.card-header -->
                {{ Form::open(['route' => 'admin.appslides.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'slideAdd','files' => true]) }}
                <div class="card-body">
                    @include('admin.slides.form')
                </div>
                 <!-- /.card-body -->
                <div class="card-footer">
                    <div class="save-cancel-wrap">
                        <a class="btn btn-outline-primary" href="{!! route('admin.appslides.index', array('#'.$type)) !!}" >{{trans('buttons.general.cancel')}}</a>
                        <button type="submit" class="btn btn-primary" id="zevo_submit_btn">{{trans('buttons.general.save')}}</button>
                    </div>
                </div>
                {{ Form::close() }}
                <!-- /.card-footer-->
            </div>
        </div>
    </section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateSlideRequest','#slideAdd') !!}
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
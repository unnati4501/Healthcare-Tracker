@extends('layouts.app')
@section('after-styles')
<link href="{{asset('assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.notifications.breadcrumb', [
    'mainTitle'  => trans('notificationsettings.title.add_form_title'),
    'breadcrumb' => 'notification.add',
    'back'       => false,
    'create'     => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.notifications.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'notificationAdd', 'files' => true]) }}
            <div class="card-body">
                @include('admin.notifications.form')
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.notifications.index') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" type="submit" onclick="formSubmit();" id="zevo_submit_btn">
                        {{trans('buttons.general.save')}}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreatenotificationRequest','#notificationAdd') !!}
<script src="{{asset('assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}"></script>
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}">
</script>
<style type="text/css">
.datetimepicker{
    padding: 4px !important;
}
.datetimepicker .table-condensed th,.datetimepicker .table-condensed td {
    padding: 4px 5px;
}
</style>
<script type="text/javascript">
var message = {
    image_valid_error: `{{trans('notificationsettings.message.image_valid_error')}}`,
    image_size_2M_error: `{{trans('notificationsettings.message.image_size_2M_error')}}`,
    upload_image_dimension: `{{trans('notificationsettings.message.upload_image_dimension')}}`
};
</script>
<script src="{{ mix('js/notifications/create.js') }}">
</script>
@endsection

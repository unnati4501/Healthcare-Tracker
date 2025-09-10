@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.webinar.breadcrumb', [
    'mainTitle'  => trans('webinar.title.edit_form_title'),
    'breadcrumb' => 'webinar.edit',
    'create'     => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.webinar.update', $data->id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH', 'role' => 'form', 'id'=>'webinaredit', 'files' => true]) }}
            <div class="card-body">
                @include('admin.webinar.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.webinar.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="webinarSubmit" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection


@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditWebinarRequest','#webinaredit') !!}
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand())}}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        webinarIndex: `{{ route('admin.webinar.index') }}`,
    },
    placeholder = {
        select_goal_tags: `{{ trans('webinar.form.placeholder.select_goal_tags') }}`,
    },
    message = {
        image_valid_error: `{{trans('webinar.message.image_valid_error')}}`,
        image_size_2M_error: `{{trans('webinar.message.image_size_2M_error')}}`,
        video_valid_error: `{{trans('webinar.message.video_valid_error')}}`,
        video_size_250M_error: `{{trans('webinar.message.video_size_250M_error')}}`,
        something_wrong_try_again: `{{ trans('webinar.message.something_wrong_try_again') }}`,
        uploading_media: `{{ trans('webinar.message.uploading_media') }}`,
        processing_media: `{{ trans('webinar.message.processing_media') }}`,
        video_1minute_log: `{{ trans('webinar.validation.video_1minute_log') }}`,
        upload_image_dimension: `{{ trans('webinar.message.upload_image_dimension') }}`,
    };
</script>
<script src="{{ asset('js/webinar/edit.js') }}" type="text/javascript">
</script>
@endsection

@extends('layouts.app')

@section('after-styles')
@if($isSA)
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endif
<link href="{{ asset('assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    .datetimepicker{ padding: 4px !important; }
    .datetimepicker .table-condensed th,.datetimepicker .table-condensed td { padding: 4px 5px; }
    .is-valid-cstm { border-color: #28a745 !important; }
    .is-invalid-cstm { border-color: #dc3545 !important; }
</style>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.feed.breadcrumb',[
    'appPageTitle' => trans('feed.title.add_form_title'),
    'breadcrumb' => 'feed.create',
    'create'     => false,
    'back'       => false,
    'edit'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.feeds.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post', 'role' => 'form', 'id'=>'feedAdd', 'files' => true]) }}
            <div class="card-body">
                @include('admin.feed.form', [ 'edit' => false, 'isSA' => $isSA, 'clone' => false ])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.feeds.index') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" id="feedSubmit" type="submit">
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
<script src="{{ asset('assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
@if($isSA)
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
@endif
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateFeedRequest','#feedAdd') !!}
<script type="text/javascript">
    var url = {
        feed_index: `{{ route('admin.feeds.index') }}`,
    },
    condition = {
        isSA: '{{ $isSA }}',
    },
    assets = {
        boxed_bg: `{{ asset('assets/dist/img/boxed-bg.png') }}`,
    },
    message = {
        image_valid_error: `{{trans('feed.message.image_valid_error')}}`,
        image_size_2M_error: `{{trans('feed.message.image_size_2M_error')}}`,
        video_valid_error: `{{trans('feed.message.video_valid_error')}}`,
        audio_valid_error: `{{trans('feed.message.audio_valid_error')}}`,
        video_size_100M_error: `{{trans('feed.message.video_size_100M_error')}}`,
        audio_size_100M_error: `{{trans('feed.message.audio_size_100M_error')}}`,
        something_wrong_try_again: `{{ trans('feed.message.something_wrong_try_again') }}`,
        select_goal_tags: `{{ trans('feed.form.placeholder.select_goal_tags') }}`,
        start_end_interval_message: '{{ trans('feed.message.start_end_interval_message') }}',
        choose_file: `{{ trans('feed.form.placeholder.choose_file') }}`,
        uploading_media: '{{ trans('feed.message.uploading_media') }}',
        processing_media: '{{ trans('feed.message.processing_media') }}',
        upload_image_dimension: '{{ trans('feed.message.upload_image_dimension') }}',
    };
</script>
<script src="{{ asset('js/feed/create.js') }}" type="text/javascript">
</script>
@endsection

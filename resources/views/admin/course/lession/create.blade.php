@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.course.lession.breadcrumb', [
    'mainTitle' => trans('masterclass.lesson.title.add'),
    'breadcrumb' => Breadcrumbs::render('course.lesson.create', $course->id),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.masterclass.storeLession', $course->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'courseLessionAdd', 'files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.course.lession.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.masterclass.manageLessions', $course->id) }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn"  type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateCourseLessionRequest','#courseLessionAdd') !!}
<script type="text/javascript">
    var messages = {!! json_encode(trans('masterclass.lesson.messages')) !!},
        bg_background = `{{ asset('assets/dist/img/boxed-bg.png') }}`,
        url = {
            success: `{{ route('admin.masterclass.manageLessions', $course->id) }}`,
        };
    messages.choosefile = `{{ trans('masterclass.lesson.form.placeholder.choose_file') }}`,
    messages.upload_image_dimension = '{{ trans('masterclass.lesson.messages.upload_image_dimension') }}';
</script>
<script src="{{ mix('js/masterclass/lesson-create.js') }}" type="text/javascript">
</script>
@endsection

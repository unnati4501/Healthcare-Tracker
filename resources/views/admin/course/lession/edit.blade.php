@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.course.lession.breadcrumb', [
    'mainTitle' => trans('masterclass.lesson.title.edit'),
    'breadcrumb' => Breadcrumbs::render('course.lesson.edit', $record->course_id),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.masterclass.updateLession', $record->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'POST', 'role' => 'form', 'id' => 'courseLessionEdit', 'files' => true]) }}
            <div class="card-body">
                @include('admin.course.lession.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    @if(!empty($request['referrer']) && $request['referrer'] == 'preview')
                    <input name="referrer" type="hidden" value="1"/>
                    <a class="btn btn-outline-primary" href="{{ route('admin.masterclass.view', [$record->course_id, '#' . $record->getKey()]) }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                    @else
                    <a class="btn btn-outline-primary" href="{{ route('admin.masterclass.manageLessions', $record->course_id) }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                    @endif
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditCourseLessionRequest','#courseLessionEdit') !!}
<script type="text/javascript">
    var messages = {!! json_encode(trans('masterclass.lesson.messages')) !!},
        _lesson_type = {{ ($record->type ?? 0) }};
    messages.choosefile = `{{ trans('masterclass.lesson.form.placeholder.choose_file') }}`,
    messages.upload_image_dimension = '{{ trans('masterclass.lesson.messages.upload_image_dimension') }}';
</script>
<script src="{{ mix('js/masterclass/lesson-edit.js') }}" type="text/javascript">
</script>
@endsection

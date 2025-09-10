@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.exercise.breadcrumb',[
    'appPageTitle' => trans('exercise.title.add_form_title'),
    'breadcrumb' => 'exercise.create',
    'create'     => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
<div class="container-fluid">
    <div class="card form-card">
        {{ Form::open(['route' => 'admin.exercises.store', 'class' => 'form-horizontal  zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'exerciseAdd','files' => true]) }}
        <div class="card-body">
            @include('admin.exercise.form', ['edit' => false])
        </div>
        <div class="card-footer">
            <div class="save-cancel-wrap">
                <a class="btn btn-outline-primary" href="{!! route('admin.exercises.index') !!}" >{{trans('buttons.general.cancel')}}</a>
                <button id="zevo_submit_btn" type="submit" class="btn btn-primary">{{trans('buttons.general.save')}}</button>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>
</section>
@endsection
@section('after-scripts')
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateExerciseRequest','#exerciseAdd') !!}
<script type="text/javascript">
    var message = {
        image_valid_error: `{{trans('labels.common_title.image_valid_error')}}`,
        image_size_2M_error: `{{trans('labels.common_title.image_size_2M_error')}}`,
        upload_image_dimension: '{{ trans('exercise.message.upload_image_dimension') }}',
    };
</script>
<script src="{{ asset('js/exercise/create.js') }}" type="text/javascript">
</script>
@endsection
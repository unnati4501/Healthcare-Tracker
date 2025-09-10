@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.exercise.breadcrumb',[
    'appPageTitle' => trans('exercise.title.edit_form_title'),
    'breadcrumb' => 'exercise.edit',
    'create'     => false,
])
<!-- /.content-header -->
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card form-card">
              <!-- /.card-header -->
                {{ Form::open(['route' => ['admin.exercises.update',$id], 'class' => 'form-horizontal  zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'exerciseEdit','files' => true]) }}
                <div class="card-body">
                    @include('admin.exercise.form', ['edit' => true])
                </div>
              <!-- /.card-body -->
                <div class="card-footer">
                    <div class="save-cancel-wrap">
                        <a class="btn btn-outline-primary" href="{!! route('admin.exercises.index') !!}">{{trans('buttons.general.cancel')}}</a>
                        <button id="zevo_submit_btn" type="submit" class="btn btn-primary">{{trans('buttons.general.update')}}</button>
                    </div>
                </div>
              {{ Form::close() }}
            </div>
        </div>
    </section>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditExerciseRequest','#exerciseEdit') !!}
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}"></script>
<script type="text/javascript">
    var message = {
        image_valid_error: `{{trans('labels.common_title.image_valid_error')}}`,
        image_size_10M_error: `{{trans('labels.common_title.image_size_10M_error')}}`,
        upload_image_dimension: '{{ trans('exercise.message.upload_image_dimension') }}',
    };
</script>
<script src="{{ asset('js/exercise/edit.js') }}" type="text/javascript">
</script>
@endsection
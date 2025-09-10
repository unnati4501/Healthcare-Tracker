@extends('layouts.app')

@section('after-styles')
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.goals.breadcrumb',[
    'appPageTitle' => trans('goals.title.edit_form_title'),
    'breadcrumb' => 'goals.edit',
    'create'     => false,
    'back' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.goals.update',$record->id], 'class' => 'form-horizontal', 'method'=>'PATCH','role' => 'form', 'id'=>'goalEdit','files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.goals.form', ['edit'=>false])
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.goals.index') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{trans('buttons.general.update')}}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
        <!-- /.card-footer-->
    </div>
</section>
<!-- /.container-fluid -->
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditGoalRequest','#goalEdit') !!}
<script type="text/javascript">
    var message = {
        upload_valid_image:  `{{trans('goals.message.upload_valid_image')}}`,
        maximum_allowed_2mb: `{{trans('goals.message.maximum_allowed_2mb')}}`,
        upload_image_dimension: `{{trans('goals.message.upload_image_dimension')}}`,
    };
</script>
<script src="{{ asset('js/goals/create-edit.js') }}" type="text/javascript">
</script>
@endsection

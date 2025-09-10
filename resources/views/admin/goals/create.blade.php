@extends('layouts.app')

@section('after-styles')
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.goals.breadcrumb',[
    'appPageTitle' => trans('goals.title.add_form_title'),
    'breadcrumb' => 'goals.create',
    'create'     => false,
    'back' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.goals.store', 'class' => 'form-horizontal', 'method'=>'post','role' => 'form', 'id'=>'goalsAdd','files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.goals.form', ['edit'=>false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.goals.index') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{trans('buttons.general.save')}}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
<!-- /.container-fluid -->
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateGoalRequest','#goalsAdd') !!}
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

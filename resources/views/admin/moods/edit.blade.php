@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.moods.breadcrumb', [
  'mainTitle' => trans('moods.title.edit'),
  'breadcrumb' => 'moods.edit'
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.moods.update',$record->id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'moodEdit','files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    @include('admin.moods.form')
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.moods.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditMoodRequest','#moodEdit') !!}
<script type="text/javascript">
    var image_valid_error = '{{ trans('moods.messages.image_valid_error') }}';
    var image_size_2M_error = '{{ trans('moods.messages.image_size_2M_error') }}';
    var upload_image_dimension = `{{trans('moods.messages.upload_image_dimension')}}`;
</script>
<script src="{{ mix('js/moods/createEdit.js') }}">
</script>
@endsection

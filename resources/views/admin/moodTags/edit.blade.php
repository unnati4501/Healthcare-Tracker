@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.moodTags.breadcrumb', [
  'mainTitle' => trans('moods.tags.title.edit'),
  'breadcrumb' => 'moodTags.edit'
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.moodTags.update',$record->id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'moodTagEdit']) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    @include('admin.moodTags.form')
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.moodTags.index') !!}">
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
<!-- /.container-fluid -->
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditMoodTagRequest','#moodTagEdit') !!}
@endsection

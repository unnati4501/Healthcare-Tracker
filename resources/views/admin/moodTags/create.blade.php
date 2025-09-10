@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.moodTags.breadcrumb', [
  'mainTitle' => trans('moods.tags.title.add'),
  'breadcrumb' => 'moodTags.create'
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.moodTags.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'moodTagsAdd']) }}
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
                        {{ trans('buttons.general.save') }}
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateMoodTagRequest','#moodTagsAdd') !!}
@endsection

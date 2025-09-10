@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.categories.tags.breadcrumb', [
    'mainTitle' => trans('categories.tags.title.create'),
    'breadcrumb' => Breadcrumbs::render('categoryTags.create'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.categoryTags.store', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'POST', 'role' => 'form', 'id' => 'categoryTagsAdd']) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        @include('admin.categories.tags.form', ['edit' => false])
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.categoryTags.tag-index') }}">
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
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateCategoryTagsRequest', '#categoryTagsAdd') !!}
@endsection

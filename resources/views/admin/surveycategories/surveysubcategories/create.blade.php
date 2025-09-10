@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.surveycategories.surveysubcategories.breadcrumb', [
    'mainTitle' => trans('surveysubcategories.title.add', [
        'category' => request()->surveycategory->display_name,
    ]),
    'breadcrumb' => Breadcrumbs::render('surveysubcategories.create', request()->surveycategory->id),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.surveysubcategories.store', request()->surveycategory->id], 'class' => 'form-horizontal', 'method' => 'post', 'role' => 'form', 'id' => 'subCategoryAdd', 'files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.surveycategories.surveysubcategories.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.surveysubcategories.index', request()->surveycategory->id) }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateSurveySubCategoryRequest','#subCategoryAdd') !!}
<script type="text/javascript">
    var messages = {!! json_encode(trans('surveysubcategories.messages')) !!};
    messages.choose_file = `{{ trans('surveysubcategories.form.placeholder.choose_file') }}`;
    messages.upload_image_dimension = `{{ trans('surveysubcategories.messages.upload_image_dimension') }}`;
</script>
<script src="{{ mix('js/surveysubcategories/create.js') }}" type="text/javascript">
</script>
@endsection

@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.categories.breadcrumb', [
  'mainTitle' => trans('categories.subcategories.title.edit'),
  'breadcrumb' => Breadcrumbs::render('subcategories.edit', $categoryId),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.subcategories.update', $id], 'method' => 'PATCH', 'role' => 'form', 'id' => 'subCategoryEdit','files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        @include('admin.categories.subcategories.form')
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.subcategories.index', $categoryId) }}">
                        {{ trans('labels.buttons.cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('labels.buttons.update') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
    <!-- /.container-fluid -->
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditSubCategoryRequest', '#subCategoryEdit') !!}
<script src="{{mix('js/categories/subcategories/edit.js')}}">
</script>
<script type="text/javascript">
    var messages = {!! json_encode(trans('categories.subcategories.messages')) !!};
    var upload_image_dimension = '{{ trans('categories.subcategories.messages.upload_image_dimension') }}';
</script>
@endsection

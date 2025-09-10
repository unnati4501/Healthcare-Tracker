@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.categories.breadcrumb', [
  'mainTitle' => trans('categories.subcategories.title.add'),
  'breadcrumb' => Breadcrumbs::render('subcategories.create'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.subcategories.store', 'method' => 'post', 'role' => 'form', 'id' => 'subCategoryAdd', 'files' => true]) }}
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
                    <a class="btn btn-outline-primary" href="{{ route('admin.categories.index') }}">
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
    <!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateSubCategoryRequest', '#subCategoryAdd') !!}
<script src="{{mix('js/categories/subcategories/create.js')}}">
</script>
<script type="text/javascript">
    var messages = {!! json_encode(trans('categories.subcategories.messages')) !!};
    var upload_image_dimension = '{{ trans('categories.subcategories.messages.upload_image_dimension') }}';
</script>
@endsection

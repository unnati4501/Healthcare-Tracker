@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.services.breadcrumb', [
  'mainTitle'  => trans('services.title.edit'),
  'breadcrumb' => Breadcrumbs::render('services.edit'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.services.update', $id], 'method' => 'PATCH', 'role' => 'form', 'id' => 'subCategoryEdit','files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        @include('admin.services.form')
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.services.index', $id) }}">
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
@include('admin.services.addsubcategory-modal')
@include('admin.services.delete-modal')
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditServiceRequest', '#subCategoryEdit') !!}
<script src="{{mix('js/services/edit.js')}}">
</script>
<script type="text/javascript">
    var messages = {!! json_encode(trans('services.messages')) !!};
    var upload_image_dimension = '{{ trans('services.messages.upload_image_dimension') }}';
    var labels = {!! json_encode(trans('services.modal')) !!}
</script>
@endsection

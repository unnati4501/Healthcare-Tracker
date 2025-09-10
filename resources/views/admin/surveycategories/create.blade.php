@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.surveycategories.breadcrumb', [
    'mainTitle' => trans('surveycategories.title.add'),
    'breadcrumb' => Breadcrumbs::render('surveycategories.create'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.surveycategories.store', 'class' => 'form-horizontal', 'method' => 'post','role' => 'form', 'id' => 'surveycategoryAdd', 'files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.surveycategories.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.surveycategories.index') }}">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateSurveyCategoryRequest', '#surveycategoryAdd') !!}
<script type="text/javascript">
    var messages = {!! json_encode(trans('surveycategories.messages')) !!};
    messages.choose_file = `{{ trans('surveycategories.form.placeholder.choose_file') }}`;
    messages.upload_image_dimension = `{{ trans('surveycategories.messages.upload_image_dimension') }}`;
</script>
<script src="{{ mix('js/surveycategories/create.js') }}" type="text/javascript">
</script>
@endsection

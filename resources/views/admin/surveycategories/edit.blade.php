@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.surveycategories.breadcrumb', [
    'mainTitle' => trans('surveycategories.title.edit'),
    'breadcrumb' => Breadcrumbs::render('surveycategories.edit'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.surveycategories.update', $id], 'class' => 'form-horizontal', 'method' => 'PATCH', 'role' => 'form', 'id' => 'surveycategoryEdit', 'files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.surveycategories.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.surveycategories.index') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
<div class="modal fade" data-id="0" id="remove-media-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title remove-media-title">
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="remove-media-message">
                </p>
            </div>
            <div class="modal-footer">
                {{ Form::hidden('remove_media_type', null, ['id' => 'remove_media_type']) }}
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="remove-media-confirm" type="button">
                    {{ trans('surveycategories.buttons.remove') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditSurveyCategoryRequest', '#surveycategoryEdit') !!}
<script type="text/javascript">
    var messages = {!! json_encode(trans('surveycategories.messages')) !!},
        remove = {!! json_encode(trans('surveycategories.modal.remove')) !!};
    messages.choose_file = `{{ trans('surveycategories.form.placeholder.choose_file') }}`;
    messages.upload_image_dimension = `{{ trans('surveycategories.messages.upload_image_dimension') }}`;
</script>
<script src="{{ mix('js/surveycategories/edit.js') }}" type="text/javascript">
</script>
@endsection

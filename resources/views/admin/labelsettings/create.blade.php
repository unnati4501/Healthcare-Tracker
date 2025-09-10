@extends('layouts.app')

@section('after-styles')
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.labelsettings.breadcrumb', [
    'appPageTitle'  => trans('labelsettings.title.add_form_title'),
    'breadcrumb'    => 'labelsettings.changelabel',
    'changelabel'   => false,
    'defautlBtn'    => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.labelsettings.update', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'changelabelsettings', 'files' => true]) }}
            @include('admin.labelsettings.form')
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-effect btn-outline-secondary me-2 mm-w-100" href="{!! route('admin.labelsettings.index') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary btn-effect mm-w-100" id="zevo_submit_btn" type="submit">
                        {{trans('buttons.general.save')}}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
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
                {{ Form::hidden('remove_media_type', '', ['id' => 'remove_media_type']) }}
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="remove-media-confirm" type="button">
                    {{ trans('buttons.general.remove') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditLabelStringRequest','#changelabelsettings') !!}
<script type="text/javascript">
    var messages = {!! json_encode(trans('labelsettings.message')) !!},
        defaultImages = {
            location_logo: `{{ config('zevolifesettings.fallback_image_url.company.location_logo') }}`,
            department_logo: `{{ config('zevolifesettings.fallback_image_url.company.department_logo') }}`,
        };
    messages.choose_file = `{{ trans('labelsettings.form.placeholder.choose_file') }}`;
    messages.upload_image_dimension = `{{trans('labelsettings.message.upload_image_dimension')}}`;
</script>
<script src="{{ asset('js/labelsettings/create.js') }}" type="text/javascript">
</script>
@endsection

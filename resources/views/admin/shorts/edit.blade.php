@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.shorts.breadcrumb', [
    'mainTitle' => trans('shorts.title.edit'),
    'breadcrumb' => Breadcrumbs::render('shorts.edit'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.shorts.update', $data->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'shortsEdit', 'files' => true]) }}
            <div class="card-body">
                @include('admin.shorts.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.shorts.index') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" onclick="formSubmit()" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditShortsRequest','#shortsEdit') !!}
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        shortsIndex: `{{ route('admin.shorts.index') }}`,
    },
    placeholder = {
        select_goal_tags: `{{ trans('shorts.form.placeholder.select_goal_tags') }}`,
    },
    message = {
        image_valid_error: `{{trans('shorts.message.image_valid_error')}}`,
        image_size_2M_error: `{{trans('shorts.message.image_size_2M_error')}}`,
        video_valid_error: `{{trans('shorts.message.video_valid_error')}}`,
        video_size_250M_error: `{{trans('shorts.message.video_size_250M_error')}}`,
        something_wrong_try_again: `{{ trans('shorts.message.something_wrong_try_again') }}`,
        uploading_media: `{{ trans('shorts.message.uploading_media') }}`,
        processing_media: `{{ trans('shorts.message.processing_media') }}`,
        video_1minute_log: `{{ trans('shorts.validation.video_1minute_log') }}`,
        upload_image_dimension: `{{ trans('shorts.message.upload_image_dimension') }}`,
    };

    function formSubmit() {
        $('#shortsEdit').valid();
        var domEditableElement = document.querySelector( '.ck-editor__editable' );
                editorInstance = domEditableElement.ckeditorInstance;
                description = editorInstance.getData();
                content = $(description).text().replace(/[\r\n]+/g, "").trim(),
                contentLength = content.length;
        var masterclassCompany = $('#shorts_company').val().length;

        if (contentLength == 0) {
            event.preventDefault();
            $('#shortsEdit').valid();
            $('#description-max-error').hide();
            $('#description-error').show();
            $('#description').next().addClass('is-invalid');
        } else if(contentLength > 500) {
            event.preventDefault();
            $('#shortsEdit').valid();
            $('#description-error').hide();
            $('#description-max-error').show();
            $('#description').next().addClass('is-invalid');
        } else {
            // editor.setContent(content);
            $('#description-error').hide();
            $('#description-max-error').hide();
            $('#description').next().removeClass('is-invalid').css('border-color', '');
        }

        if (masterclassCompany <= 0) {
            event.preventDefault();
            $('#shortsEdit').valid();
            $('#shorts_company').addClass('is-invalid');
            $('#shorts_company-error').show();
            $('.tree-multiselect');
        } else {
            $('#shorts_company').removeClass('is-invalid');
            $('#shorts_company-error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
    }
</script>
<script src="{{ mix('js/shorts/edit.js') }}" type="text/javascript">
</script>
@endsection

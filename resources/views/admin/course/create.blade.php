@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.course.breadcrumb', [
    'mainTitle' => trans('masterclass.title.add'),
    'breadcrumb' => Breadcrumbs::render('course.create'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.masterclass.store', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'name' => 'courseAdd', 'id' => 'courseAdd', 'files' => true]) }}
            <div class="card-body">
                @include('admin.course.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.masterclass.index') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" onclick="formSubmit()" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}" type="text/javascript">
</script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateCourseRequest','#courseAdd') !!}
<script type="text/javascript">
    var messages = {!! json_encode(trans('masterclass.messages')) !!},
        url = {
            success: `{{ route('admin.masterclass.index') }}`,
        },
        defaultCourseImg = `{{ asset('assets/dist/img/placeholder-img.png') }}`;
    messages.choosefile = `{{ trans('masterclass.form.placeholder.choose-file') }}`;
    messages.upload_image_dimension = '{{ trans('masterclass.messages.upload_image_dimension') }}';
    function formSubmit() {
        $('#courseAdd').valid();
        var domEditableElement = document.querySelector( '.ck-editor__editable' );
            editorInstance = domEditableElement.ckeditorInstance;
            description = editorInstance.getData();
            content = $(description).text().trim();
            contentLength = content.length;

        var selectedMembers = $('#masterclass_company').val().length;

        if (contentLength == 0) {
            event.preventDefault();
            $('#courseAdd').valid();
            $('#description-max-error').hide();
            $('#description-error').show();
            $('#description').next().addClass('is-invalid');
        } else if(contentLength > 500) {
            event.preventDefault();
            $('#courseAdd').valid();
            $('#description-error').hide();
            $('#description-max-error').show();
            $('#description').next().addClass('is-invalid');
        } else {
            // editor.setContent(content);
            $('#description-error').hide();
            $('#description-max-error').hide();
            $('#description').next().removeClass('is-invalid').css('border-color', '');
        }

        if (selectedMembers <= 0) {
            event.preventDefault();
            $('#courseAdd').valid();
            $('#masterclass_company').addClass('is-invalid');
            $('#masterclass_company-error').show();
            $('.tree-multiselect');
        } else {
            $('#masterclass_company').removeClass('is-invalid');
            $('#masterclass_company-error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
    }
</script>
<script src="{{ mix('js/masterclass/create.js') }}" type="text/javascript">
</script>
@endsection

@extends('layouts.app')

@section('after-styles')
@if($isSA)
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
@endif
<style type="text/css">
    .tooltip-inner { max-width: 100% !important; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.recipe.breadcrumb', [
  'mainTitle' => trans('recipe.title.add'),
  'breadcrumb' => Breadcrumbs::render('recipe.create'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.recipe.store', 'class' => 'form-horizontal', 'method' => 'post', 'role' => 'form', 'id' => 'recipeAdd', 'files' => true]) }}
            <div class="card-body">
                @include('admin.recipe.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.recipe.index') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn_cstm" onclick="formSubmit()" type="submit">
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
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\CreateRecipeRequest','#recipeAdd') !!}
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}">
</script>
@if($isSA)
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
@endif
<script type="text/javascript">
    var upload_image_dimension = `{{trans('recipe.messages.upload_image_dimension')}}`;
    var ingdCount = 1;
    var messages = {!! json_encode(trans('recipe.messages')) !!};
    
    function formSubmit() {
        var filesLenght = $('#image').prop('files').length;
        if(filesLenght == 0) {
            event.preventDefault();
            $('#recipeAdd').valid();
            $('.image-upload-box').addClass('danger is-invalid');
            $('#recipeImage-error').html('The image field is required.');
            $('#recipeImage-error').show();
        } else {
            $('#recipeImage-error').html('');
            $('.image-upload-box').removeClass('danger is-invalid');
            $('#recipeImage-error').hide();
        }

        var editor = tinymce.get('description'),
            content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim(),
            contentLength = content.length;
        if (contentLength == 0) {
            event.preventDefault();
            $('#recipeAdd').valid();
            $('#description-max-error').hide();
            $('#description-error').show();
            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
        } else if(contentLength > 5000) {
            event.preventDefault();
            $('#recipeAdd').valid();
            $('#description-error').hide();
            $('#description-max-error').show();
            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
        } else {
            // editor.setContent(content);
            $('#description-error').hide();
            $('#description-max-error').hide();
            $('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
        }

        @if($isSA)
        var recipeCompany = $('#recipe_company').val().length;
        if (recipeCompany == 0) {
            event.preventDefault();
            $('#recipeAdd').valid();
            $('#recipe_company').addClass('is-invalid');
            $('#recipe_company-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else {
            $('#recipe_company').removeClass('is-invalid');
            $('#recipe_company-error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
        @endif

        if ($('#recipeAdd').valid() && $(".is-invalid").length == 0) {
            $("#zevo_submit_btn_cstm").attr("disabled", true);
            $('#recipeAdd').submit();
        }

        if (!$('#recipeAdd').valid() || ($(".is-invalid").length > 0)) {
            $('html, body').animate({
                scrollTop: $(".is-invalid").first().offset().top - 100
            }, 500);
            return false;
        }
    }

    function readURL(input, previewElement) {
        if (input && input.files.length > 0) {
            var reader = new FileReader();
            reader.onload = function(e) {
                // Validation for image max height / width and Aspected Ratio
                var image = new Image();
                image.src = e.target.result;
                image.onload = function () {
                    var imageWidth = $(input).data('width');
                    var imageHeight = $(input).data('height');
                    var ratio = $(input).data('ratio');
                    var aspectedRatio = ratio;
                    var ratioSplit = ratio.split(':');
                    var newWidth = ratioSplit[0];
                    var newHeight = ratioSplit[1];
                    var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                    if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
                        $(input).empty().val('');
                        $(input).parent('div').find('.custom-file-label').html('Choose File');
                        $(previewElement).removeAttr('src');
                        toastr.error(messages.upload_image_dimension);
                        readURL(null, previewElement);
                    }
                }
                $(previewElement).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            if(previewElement == '#previewImg') {
                $(previewElement).attr('src', defaultCourseImg);
            } else {
                $(previewElement).removeAttr('src');
            }
        }
    }

    $(document).ready(function() {
        $('.select2').select2({
            allowClear: true,
            width: '100%'
        });

        $('#goal_tag').select2({
            multiple: true,
            closeOnSelect: false,
        });

        // Custom Scrolling
        if ($("#setPermissionList").length > 0) {
            $.mCustomScrollbar.defaults.scrollButtons.enable = true;
            $.mCustomScrollbar.defaults.axis = "yx";
            $("#setPermissionList").mCustomScrollbar({
                axis: "y",
                theme: "inset-dark"
            });
        }

        $('[data-bs-toggle="tooltip"]').tooltip();
        $("#image").change(function () {
            $('#imagesPreview').empty();
            var maxImageCount = {{ config('zevolifesettings.max_limit_counts.recipe.logo', 3) }},
                files = $(this).prop('files'),
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'],
                canGeneratePreview = false;

            if(files.length > 0)  {
                $('#recipeImage-error').html('');
                $('.image-upload-box').removeClass('danger is-invalid');
                $('#recipeImage-error').hide();

                if(files.length <= maxImageCount)  {
                    $.each(files, function(key, file) {
                        if (!allowedMimeTypes.includes(file.type)) {
                            $('.image-upload-box').addClass('danger is-invalid');
                            $('#recipeImage-error').html("{{trans('labels.common_title.image_valid_error')}}");
                            $('#recipeImage-error').show();
                            $("#image").val('');
                            $('#imagesPreview').empty();
                            canGeneratePreview = false;
                            return false;
                        } else if(file.size > 5242880) {
                            $('.image-upload-box').addClass('danger is-invalid');
                            $('#recipeImage-error').html("{{trans('labels.common_title.image_size_5M_error')}}");
                            $('#recipeImage-error').show();
                            $("#image").val('');
                            $('#imagesPreview').empty();
                            canGeneratePreview = false;
                            return false;
                        } else {
                            canGeneratePreview = true;
                        }
                    });

                    if(canGeneratePreview == true) {
                        var errorResult = true;
                        $.each(files, function(key, file) {
                            var reader = new FileReader(),
                                template = $('#recipeImagePreview').text().trim();
                            reader.onload = function (e) {
                                // Validation for image max height / width and Aspected Ratio
                                var image = new Image();
                                image.src = e.target.result;
                                image.onload = function () {
                                    var imageWidth = $('#image').data('width');
                                    var imageHeight = $('#image').data('height');
                                    var ratio = $('#image').data('ratio');
                                    var aspectedRatio = ratio;
                                    var ratioSplit = ratio.split(':');
                                    var newWidth = ratioSplit[0];
                                    var newHeight = ratioSplit[1];
                                    var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                                    if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
                                        $('#image').empty().val('');
                                        $('#image').parent('div').find('.custom-file-label').html('Choose File');
                                        $('#image').parent('div').find('.invalid-feedback').remove();
                                        $(file).removeAttr('src');
                                        if(errorResult == true){
                                            toastr.error(upload_image_dimension);
                                            errorResult = false;
                                        }
                                    } else {
                                        template = template.replace("##src##", e.target.result);
                                        $('#imagesPreview').append(template);
                                    }
                                }
                            }
                            reader.readAsDataURL(file);
                        });

                    } else {
                        $("#image").val('');
                        $('#imagesPreview').empty();
                    }
                } else {
                    $('.image-upload-box').addClass('danger is-invalid');
                    $('#recipeImage-error').html("The image field will not allow more than 3 images.");
                    $('#recipeImage-error').show();
                    $("#image").val('');
                    $('#imagesPreview').empty();
                    return;
                }
            }
        });

        $(document).on('change', '#header_image', function(e) {
            var previewElement = $(this).data('previewelement');
            if (e.target.files.length > 0) {
                var fileName = e.target.files[0].name,
                    allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
                if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                    toastr.error(messages.image_valid_error);
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html(messages.choosefile);
                    readURL(null, previewElement);
                } else if (e.target.files[0].size > 2097152) {
                    toastr.error(messages.image_size_2M_error);
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html(messages.choosefile);
                    readURL(null, previewElement);
                } else {
                    readURL(e.target, previewElement);
                    $(this).parent('div').find('.custom-file-label').html(fileName);
                }
            } else {
                $(this).parent('div').find('.custom-file-label').html(messages.choosefile);
                readURL(null, previewElement);
            }
        });

        var descriptionEditor = tinymce.init({
            selector: "#description",
            branding: false,
            menubar:false,
            statusbar: false,
            plugins: "code,link,lists,advlist",
            toolbar: 'formatselect | bold italic forecolor backcolor permanentpen formatpainter alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | code | link',
            forced_root_block : true,
            paste_as_text : true,
            setup: function (editor) {
                editor.on('change redo undo', function () {
                    tinymce.triggerSave();
                    var editor = tinymce.get('description');
                    var contentLength = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim().length;
                    $('#description-error').hide();
                    $('#description-max-error').hide();
                    $('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
                    if (contentLength == 0) {
                        event.preventDefault();
                        $('#description-error').show();
                        $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                    } else if(contentLength > 5000) {
                        event.preventDefault();
                        $('#description-max-error').show();
                        $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                    }
                });
            }
        });

        $('.numeric').numeric({ decimal: false, negative: false });
        $('.numeric-decimal').numeric({ decimalPlaces: 10, negative: false });
        $.validator.addMethod("ingredients_required", $.validator.methods.required, 'The ingredients field is required.');
        $.validator.addClassRules("ingredients_required", { ingredients_required: true, minlength: 2, maxlength: 100 });

        $(document).on('click', '#ingriadiantAdd', function () {
            var template = $('#ingredientsTemplate').text().trim();
            template = template.replace(':ingdCount', ingdCount);
            ingdCount++;
            $("#ingriadiantTbl tbody").append(template);
        });

        $(document).on('keyup', '#ingriadiantTbl tbody tr:last input', function(e) {
            if($('#ingriadiantTbl tbody tr').length > 1) {
                $(this).parent().parent().next().toggleClass("show_del", $(this).val().length == 0);
            }
        });

        $(document).on('click', ".ingriadiant-remove", function (e) {
            e.preventDefault();
            $($(this).closest("tr")).remove();
            if($('#ingriadiantTbl tbody tr').length == 1) {
                $('#ingriadiantTbl tbody tr:last td:last').removeClass('show_del');
            }
        });

        @if($isSA)
        $("#recipe_company").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                var recipeCompany = $('#recipe_company').val().length;
                if (recipeCompany == 0) {
                    $('#recipeAdd').valid();
                    $('#recipe_company').addClass('is-invalid');
                    $('#recipe_company-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    $('#recipe_company').removeClass('is-invalid');
                    $('#recipe_company-error').hide();
                    $('.tree-multiselect').css('border-color', '#D8D8D8');
                }
            }
        });
        @endif
    });
</script>
@endsection

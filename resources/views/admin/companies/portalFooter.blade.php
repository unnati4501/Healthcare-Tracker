@extends('layouts.app')
<style>
    .footer-column-group{
        background: #f7f7f7;
        padding: 8px 8px 0;
        margin-bottom: 8px;
    }
    .footer-column-group:nth-child(2n){
        background: #ffffff;
        padding: 8px 8px 0;
        margin-bottom: 8px;
    }
    .footer-column-group .form-group {
        margin-bottom: 8px;
    }
</style>
@section('content-header')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ trans('labels.company.portal_footer') }}
                </h1>
                {{ Breadcrumbs::render('companies.portalFooter', $companyType, $company->id) }}
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.companies.storePortalFooterDetails', $companyType, $company->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'storePortalFooterDetails', 'files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label('footer_text', 'Footer text') }}
                                    {{ Form::text('footer_text', $portal_footer_text, ['class' => 'form-control', 'placeholder' => 'Enter Footer text', 'id' => 'footer_text', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label('portal_footer_logo', 'Footer logo') }}
                                <span class="font-16 qus-sign-tooltip" data-placement="auto" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company.portal_footer_logo') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="custom-file custom-file-preview">
                                    {{ Form::file('portal_footer_logo', ['class' => 'custom-file-input form-control', 'id' => 'portal_footer_logo', 'data-width' => config('zevolifesettings.imageConversions.company.portal_footer_logo.width'), 'data-height' => config('zevolifesettings.imageConversions.company.portal_footer_logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company.portal_footer_logo'), 'data-previewelement' => '#portal_footer_logo_preview']) }}
                                    <label class="file-preview-img" for="portal_footer_logo">
                                        <img height="200" id="portal_footer_logo_preview" src="{{ (!empty($portal_footer_logo) ? $portal_footer_logo : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                                    </label>                                    
                                    {{ Form::label('portal_footer_logo', (!empty($portal_footer_logo) ? $portal_footer_logo_name : "Choose File"), ['class' => 'custom-file-label']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row pb-5">
                        <div class="col-lg-12">
                        <div class="form-group">
                            {{ Form::label('portal_footer_header_text', 'Header Text') }}
                            {{ Form::textarea('portal_footer_header_text', old('portal_footer_header_text', (isset($portal_footer_header_text) ? htmlspecialchars_decode($portal_footer_header_text) : null)), ['class' => 'form-control basic-format-ckeditor', 'id' => 'portal_footer_header_text', 'data-errplaceholder' => '#portal_footer_header_text-error-cstm']) }}
                            <span id="portal-footer-header-text-required-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                                {{ trans('company.validation.header_required') }}
                            </span>
                            <span id="portal-footer-header-text-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                                {{ trans('company.validation.valid_header_required') }}
                            </span>
                            <span id="portal-footer-header-text-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                                {{ trans('company.validation.header_max_limit') }}
                            </span>
                        </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <h4>
                                Column 1
                            </h4>
                            <div class="form-group">
                                {{ Form::label('header1', 'Header') }}
                                        {{ Form::text('header1', $header1, ['class' => 'form-control', 'placeholder' => 'Enter Header', 'id' => 'header1', 'autocomplete' => 'off']) }}
                            </div>
                            @for($i=0; $i<5; $i++)
                            <div class="footer-column-group">
                                <div class="row">
                                    <div class="col-xl-6 form-group">
                                        {{ Form::text('col1key[]', (isset($col1key[$i]) ? $col1key[$i] : ''), ['class' => 'form-control', 'placeholder' => 'Enter Key', 'autocomplete' => 'off']) }}
                                    </div>
                                    <div class="col-xl-6 form-group">
                                        {{ Form::text('col1value[]', (isset($col1value[$i]) ? $col1value[$i] : ''), ['class' => 'form-control', 'placeholder' => 'Enter Value', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                        <div class="col-lg-4">
                            <h4>
                                Column 2
                            </h4>
                            <div class="form-group">
                                {{ Form::label('header2', 'Header') }}
                                    {{ Form::text('header2', $header2, ['class' => 'form-control', 'placeholder' => 'Enter Header', 'id' => 'header2', 'autocomplete' => 'off']) }}
                            </div>
                            @for($i=0; $i<5; $i++)
                            <div class="footer-column-group">
                                <div class="row">
                                    <div class="col-xl-6 form-group">
                                        {{ Form::text('col2key[]', (isset($col2key[$i]) ? $col2key[$i] : ''), ['class' => 'form-control', 'placeholder' => 'Enter Key', 'autocomplete' => 'off']) }}
                                    </div>
                                    <div class="col-xl-6 form-group">
                                        {{ Form::text('col2value[]', (isset($col2value[$i]) ? $col2value[$i] : ''), ['class' => 'form-control', 'placeholder' => 'Enter Value', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                        <div class="col-lg-4">
                            <h4>
                                Column 3
                            </h4>
                            <div class="form-group">
                                {{ Form::label('header3', 'Header') }}
                                    {{ Form::text('header3', $header3, ['class' => 'form-control', 'placeholder' => 'Enter Header', 'id' => 'header3', 'autocomplete' => 'off']) }}
                            </div>
                            @for($i=0; $i<5; $i++)
                            <div class="footer-column-group">
                                <div class="row">
                                    <div class="col-xl-6 form-group">
                                        {{ Form::text('col3key[]', (isset($col3key[$i]) ? $col3key[$i] : ''), ['class' => 'form-control', 'placeholder' => 'Enter Key', 'autocomplete' => 'off']) }}
                                    </div>
                                    <div class="col-xl-6 form-group">
                                        {{ Form::text('col3value[]', (isset($col3value[$i]) ? $col3value[$i] : ''), ['class' => 'form-control', 'placeholder' => 'Enter Value', 'autocomplete' => 'off']) }}
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.companies.index', $companyType) }}">
                        {{ trans('labels.buttons.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('labels.buttons.save') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\StoreFooterDetailsRequest', '#storePortalFooterDetails') !!}
<script>
    function readURL(input, previewElement) {
        if (input != null && input.files.length > 0) {
            var reader = new FileReader();
            reader.onload = function (e) {
                // Validation for image max height / width and Aspected Ratio
                var image = new Image();
                image.src = e.target.result;
                image.onload = function () {
                    var imageWidth = $(input).data('width');
                    var imageHeight = $(input).data('height');
                    var ratio = $(input).data('ratio');
                    var round = $(input).data('round');
                    if(round == undefined){
                        round = 'no';
                    }
                    var aspectedRatio = ratio;
                    var ratioSplit = ratio.split(':');
                    var newWidth = ratioSplit[0];
                    var newHeight = ratioSplit[1];
                    if(round == 'yes'){
                        var ratioGcd = gcdRound(this.width, this.height, newHeight, newWidth);
                    } else {
                        var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                    }
                    if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
                        $(input).empty().val('');
                        $(input).parent('div').find('.custom-file-label').html('Choose File');
                        $(input).parent('div').find('.invalid-feedback').remove();
                        $(previewElement).removeAttr('src');
                        toastr.error("The uploaded image does not match the given dimension and ratio.");
                        readURL(null, previewElement);
                    }
                }
                $(previewElement).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(input).parent('div').find('.custom-file-label').html('Choose File');
            $(previewElement).removeAttr('src');
        }
    }

    $(document).on('change', '#portal_footer_logo', function (e) {
        var previewElement = $(this).data('previewelement');
        if(e.target.files.length > 0) {
            var id = e.target.id,
                fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];

            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error("{{trans('labels.common_title.image_valid_error')}}");
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html('Choose File');
                readURL(null, previewElement);
            } else if (e.target.files[0].size > 2097152) {
                toastr.error("{{trans('labels.common_title.image_size_2M_error')}}");
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html('Choose File');
                readURL(null, previewElement);
            } else {
                console.log(e.target);
                debugger;
                readURL(e.target, previewElement);
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            $(this).parent('div').find('.custom-file-label').html('Choose File');
            readURL(null, previewElement);
        }
    });
    
    var editor = tinymce.init({
        selector: "#portal_footer_header_text",
        branding: false,
        menubar: false,
        statusbar: false,
        plugins: "code,link,lists,advlist",
        toolbar: 'formatselect | bold italic forecolor backcolor permanentpen formatpainter alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | code | link',
        forced_root_block: true,
        paste_as_text: true,
        setup: function(editor) {
            editor.on('change redo undo', function(event) {
                tinymce.triggerSave();
                var editor = tinymce.get('portal_footer_header_text');
                var content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim();
                var contentLength = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim().length;
                var regEx = /(^([^\'\=<>^#@"]*))+$/;
                if (contentLength == 0) {
                    event.preventDefault();
                    $('#portal-footer-header-text-required-error').show();
                    $('#portal-footer-header-text-error').hide();
                    $('#portal-footer-header-text-max-error').hide();
                    $('#portal_footer_header_text').next('.tox-tinymce').addClass('is-invalid');
                } else if (contentLength > 200) {
                    event.preventDefault();
                    $('#portal-footer-header-text-required-error').hide();
                    $('#portal-footer-header-text-error').hide();
                    $('#portal-footer-header-text-max-error').show();
                    $('#portal_footer_header_text').next('.tox-tinymce').addClass('is-invalid');
                } else if(!regEx.test(content) && contentLength > 0) {
                    event.preventDefault();
                    $('#portal-footer-header-text-required-error').hide();
                    $('#portal-footer-header-text-error').show();
                    $('#portal-footer-header-text-max-error').hide();
                    $('#portal_footer_header_text').next('.tox-tinymce').addClass('is-invalid');
                } else {
                    $('#portal-footer-header-text-required-error').hide();
                    $('#portal-footer-header-text-error').hide();
                    $('#portal-footer-header-text-max-error').hide();
                    $('#portal_footer_header_text').next('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
                }
            });
        }
    });

    $(document).on('click','#zevo_submit_btn',function(){
        formSubmit();
    });

    function formSubmit() {
        var editor = tinymce.get('portal_footer_header_text'),
        content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim(),
        contentLength = content.length;
        var regEx = /^(^([^\'\=<>^#@"]*))+$/;
        if (contentLength == 0) {
            event.preventDefault();
            $('.zevo_form_submit').valid();
            $('#portal-footer-header-text-required-error').show();
            $('#portal-footer-header-text-error').hide();
            $('#portal-footer-header-text-max-error').hide();
            $('.tox-tinymce').css('border-color', '#f44436');
        } else if(contentLength > 200) {
            event.preventDefault();
            $('.zevo_form_submit').valid();
            $('#portal-footer-header-text-required-error').hide();
            $('#portal-footer-header-text-error').hide();
            $('#portal-footer-header-text-max-error').show();
            $('.tox-tinymce').css('border-color', '#f44436');
        } else if(!regEx.test(content) && contentLength > 0) {
            event.preventDefault();
            $('.zevo_form_submit').valid();
            $('#portal-footer-header-text-required-error').hide();
            $('#portal-footer-header-text-error').show();
            $('#portal-footer-header-text-max-error').hide();
            $('.tox-tinymce').css('border-color', '#f44436');
        } else {
            $('#portal-footer-header-text-required-error').hide();
            $('#portal-footer-header-text-error').hide();
            $('#portal-footer-header-text-max-error').hide();
            $('.tox-tinymce').css('border-color', '');
        }
    }
</script>
@endsection

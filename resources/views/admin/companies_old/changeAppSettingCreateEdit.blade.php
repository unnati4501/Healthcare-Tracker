@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    Change App Settings
                </h1>
                {{ Breadcrumbs::render('companiesold.app-settings.update', $companyType ,request()->company) }}
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.companiesold.changeAppSettingStoreUpdate', 'class' => 'form-horizontal', 'method' => 'post', 'role' => 'form', 'id' => 'changeAppSettings', 'files' => true]) }}
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label('logo_image_url', 'Logo image') }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company_wise_app_settings.logo_image_url') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="custom-file custom-file-preview">
                                    {{ Form::file('logo_image_url', ['class' => 'custom-file-input', 'id' => 'logo_image_url', 'data-width' => config('zevolifesettings.imageConversions.company_wise_app_settings.logo_image_url.width'), 'data-height' => config('zevolifesettings.imageConversions.company_wise_app_settings.logo_image_url.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company_wise_app_settings.logo_image_url'), 'data-previewelement' => '#logo_image_preview']) }}
                                    <label class="file-preview-img" for="logo_image_url">
                                        <img height="200" id="logo_image_preview" src="{{ (!empty($logo_image_url['image_url']) ? $logo_image_url['image_url'] : $logo_image_url['placeholder']) }}" width="200"/>
                                    </label>
                                    {{ Form::label('logo_image_url', (!empty($logo_image_url['value']) ? $logo_image_url['value'] : 'Choose File'), ['class' => 'custom-file-label']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label('splash_image_url', 'Splash image') }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('company_wise_app_settings.splash_image_url') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="custom-file custom-file-preview">
                                    {{ Form::file('splash_image_url', ['class' => 'custom-file-input', 'id' => 'splash_image_url', 'data-width' => config('zevolifesettings.imageConversions.company_wise_app_settings.splash_image_url.width'), 'data-height' => config('zevolifesettings.imageConversions.company_wise_app_settings.splash_image_url.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.company_wise_app_settings.splash_image_url'), 'data-previewelement' => '#splash_image_preview']) }}
                                    <label class="file-preview-img" for="splash_image_url">
                                        <img height="200" id="splash_image_preview" src="{{ (!empty($splash_image_url['image_url']) ? $splash_image_url['image_url'] : $splash_image_url['placeholder']) }}" width="200"/>
                                    </label>
                                    {{ Form::label('splash_image_url', (!empty($splash_image_url['value']) ? $splash_image_url['value'] : 'Choose File'), ['class' => 'custom-file-label']) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label('splash_message', 'Splash message') }}
                            {{ Form::text('splash_message', old('splash_message', (isset($splash_message['value']) ? $splash_message['value'] : null)), ['class' => 'form-control', 'placeholder' => $splash_message['placeholder'], 'id' => 'splash_message', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label('app_theme', 'App Theme') }}
                            {{ Form::select('app_theme', $app_theme_default, old('app_theme', (isset($app_theme['value']) ? $app_theme['value'] : null)), ['class' => 'form-control select2', 'id' => 'app_theme', 'data-allow-clear' => 'false']) }}
                            </div>
                        </div>
                        {{Form::hidden('company_id', request()->company)}}
                        {{Form::hidden('companyType', $companyType)}}
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.companiesold.changeAppSettingIndex' ,[$companyType, request()->company]) }}">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\CompanyWiseAppSettingsRequest','#changeAppSettings') !!}
<script type="text/javascript">
var message = {
    upload_image_dimension: `{{trans('company.messages.upload_image_dimension')}}`,
};
    function readURL(input, previewElement) {
        if (input && input.files.length > 0) {
            var reader = new FileReader();
            reader.onload = function (e) {
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
                        $(input).parent('div').find('.invalid-feedback').remove();
                        $(previewElement).removeAttr('src');
                        toastr.error(message.upload_image_dimension);
                        readURL(null, previewElement);
                    }
                }
                $(previewElement).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(previewElement).removeAttr('src');
        }
    }

    $(document).ready(function() {
        $(document).on('change', '#logo_image_url, #splash_image_url', function (e) {
            var previewElement = $(this).data('previewelement');
            if(e.target.files.length > 0) {
                var fileName = e.target.files[0].name,
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
                    readURL(e.target, previewElement);
                    $(this).parent('div').find('.custom-file-label').html(fileName);
                }
            } else {
                $(this).parent('div').find('.custom-file-label').html('Choose File');
                readURL(null, previewElement);
            }
        });

        $('form').submit(function(){
            $(this).find('input:text').each(function(){
                $(this).val($.trim($(this).val()));
            });
        });

        $("input[type=text]").focusout(function () {
            $(this).val($.trim($(this).val()));
        });
    });
</script>
@endsection

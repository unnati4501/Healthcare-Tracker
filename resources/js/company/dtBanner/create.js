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
        $(previewElement).removeAttr('src');
    }
}

$(document).ready(function() {
    var editor = tinymce.init({
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
                        var content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim();
                        var contentLength = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim().length;
                        var patt = /^(^([^!#@^]*))+$/;
                        $('#description-error, #description-format-error, #description-max-error').hide();
                        $('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
                        if (contentLength ==  0) {
                            $('#description-error').show();
                            $('#description-format-error, #description-max-error').hide();
                            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                            $('#description-error').addClass('invalid-feedback');
                            $('#description-format-error, #description-max-error').removeClass('invalid-feedback');
                        } else if (contentLength >  500) {
                            $('#description-max-error').show();
                            $('#description-error, #description-format-error').hide();
                            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                            $('#description-max-error').addClass('invalid-feedback');
                            $('#description-format-error, #description-error').removeClass('invalid-feedback');
                        } else if (!patt.test(content)) {
                            $('#description-format-error').show();
                            $('#description-error, #description-max-error').hide();
                            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                            $('#description-format-error').addClass('invalid-feedback');
                            $('#description-error, #description-max-error').removeClass('invalid-feedback');
                        } else {
                            $('#description-error, #description-format-error, #description-max-error').hide();
                            $('.tox-tinymce').addClass('is-invalid').css('border-color', '');
                            $('#description-error, #description-format-error, #description-max-error').removeClass('invalid-feedback');
                        }
                    });
                }
            });

    $(document).on('change', '#banner_image', function(e) {
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

    $(document).on('click','#zevo_submit_btn',function(){
        formSubmit();
    });
});

function formSubmit() {
    var editor = tinymce.get('description'),
        content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim(),
        contentLength = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim().length,
        patt = /^(^([^!#@^]*))+$/;
        $('#description-error, #description-format-error, #description-max-error').hide();
        $('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
        if (contentLength ==  0) {
            event.preventDefault();
            $("#bannerAdd").valid();
            $('#description-error').show();
            $('#description-format-error, #description-max-error').hide();
            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
            $('#description-error').addClass('invalid-feedback');
            $('#description-format-error, #description-max-error').removeClass('invalid-feedback');
        } else if (contentLength >  500) {
            event.preventDefault();
            $("#bannerAdd").valid();
            $('#description-max-error').show();
            $('#description-error, #description-format-error').hide();
            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
            $('#description-max-error').addClass('invalid-feedback');
            $('#description-format-error, #description-error').removeClass('invalid-feedback');
        } else if (!patt.test(content)) {
            event.preventDefault();
            $("#bannerAdd").valid();
            $('#description-format-error').show();
            $('#description-error, #description-max-error').hide();
            $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
            $('#description-format-error').addClass('invalid-feedback');
            $('#description-error, #description-max-error').removeClass('invalid-feedback');
        } else {
            $('#description-error, #description-format-error, #description-max-error').hide();
            $('.tox-tinymce').addClass('is-invalid').css('border-color', '');
            $('#description-error, #description-format-error, #description-max-error').removeClass('invalid-feedback');
        }
}
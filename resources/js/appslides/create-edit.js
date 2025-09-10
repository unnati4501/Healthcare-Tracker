$(document).ready(function() {
    var editor = tinymce.init({
        selector: "#content",
        branding: false,
        menubar:false,
        statusbar: false,
        plugins: "code",
        toolbar: 'formatselect | bold italic forecolor backcolor permanentpen formatpainter alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | code',
        forced_root_block : true,
        paste_as_text : true,
        setup: function (editor) {
            editor.on('change redo undo', function () {
                tinymce.triggerSave();
                var editor = tinymce.get('content');
                var contentLength = $(editor.getContent()).text().trim().length;
                if (contentLength == 0) {
                    event.preventDefault();
                    $('#content-max-error').hide();
                    $('#content-error').show();
                    $('.mobile-content .tox-tinymce').css('border-color', '#f44436');
                } else if(contentLength > 501) {
                    event.preventDefault();
                    $('#content-error').hide();
                    $('#content-max-error').show();
                    $('.mobile-content .tox-tinymce').css('border-color', '#f44436');
                } else {
                    $('#content-error').hide();
                    $('#content-max-error').hide();
                    $('.mobile-content .tox-tinymce').css('border-color', '');
                }
            });
        }
    });

    var editor = tinymce.init({
        selector: "#portal_content",
        branding: false,
        menubar:false,
        statusbar: false,
        plugins: "code",
        toolbar: 'formatselect | bold italic forecolor backcolor permanentpen formatpainter alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | code',
        forced_root_block : true,
        paste_as_text : true,
        setup: function (editor) {
            editor.on('change redo undo', function () {
                tinymce.triggerSave();
                var editor = tinymce.get('portal_content');
                var contentLength = $(editor.getContent()).text().trim().length;
                if (contentLength == 0) {
                    event.preventDefault();
                    $('#portal-content-max-error').hide();
                    $('#portal-content-error').show();
                    $('.portal-content .tox-tinymce').css('border-color', '#f44436');
                } else if(contentLength > 501) {
                    event.preventDefault();
                    $('#portal-content-error').hide();
                    $('#portal-content-max-error').show();
                    $('.portal-content .tox-tinymce').css('border-color', '#f44436');
                } else {
                    $('#portal-content-error').hide();
                    $('#portal-content-max-error').hide();
                    $('.portal-content .tox-tinymce').css('border-color', '');
                }
            });
        }
    });

    $(document).on('click','#zevo_submit_btn',function(){
        formSubmit();
    });
});

function formSubmit() {
    var contentType = $("#type").val();
    var editor = tinymce.get('content'),
        content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim(),
        contentLength = content.length;
    if (contentLength == 0) {
        event.preventDefault();
        $('#slideAdd').valid();
        $('#content-max-error').hide();
        $('#content-error').show();
        $('.mobile-content .tox-tinymce').css('border-color', '#f44436');
    } else if(contentLength > 501) {
        event.preventDefault();
        $('#slideAdd').valid();
        $('#content-error').hide();
        $('#content-max-error').show();
        $('.mobile-content .tox-tinymce').css('border-color', '#f44436');
    } else {
        // editor.setContent(content);
        $('#content-error').hide();
        $('#content-max-error').hide();
        $('.mobile-content .tox-tinymce').css('border-color', '');
    }

    if(contentType == 'eap'){
        var editor = tinymce.get('portal_content'),
        portal_content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim(),
        portalContentLength = portal_content.length;
        if (portalContentLength == 0) {
            event.preventDefault();
            $('#slideAdd').valid();
            $('#portal-content-max-error').hide();
            $('#portal-content-error').show();
            $('.portal-content .tox-tinymce').css('border-color', '#f44436');
        } else if(portalContentLength > 501) {
            event.preventDefault();
            $('#slideAdd').valid();
            $('#portal-content-error').hide();
            $('#portal-content-max-error').show();
            $('.portal-content .tox-tinymce').css('border-color', '#f44436');
        } else {
            // editor.setContent(content);
            $('#portal-content-error').hide();
            $('#portal-content-max-error').hide();
            $('.portal-content .tox-tinymce').css('border-color', '');
        }
    }
}

$('input[type="file"]').change(function (e) {
    var fileName = e.target.files[0].name;
    if (fileName.length > 40) {
        fileName = fileName.substr(0, 40);
    }
    var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    if (!allowedMimeTypes.includes(e.target.files[0].type)) {
        toastr.error(message.image_valid_error);
        $(e.currentTarget).empty().val('');
        $(this).parent('div').find('.custom-file-label').val('');
    } else if (e.target.files[0].size > 2097152) {
        toastr.error(message.image_size_2M_error);
        $(e.currentTarget).empty().val('');
        $(this).parent('div').find('.custom-file-label').val('');
    } else {
        $(this).parent('div').find('.custom-file-label').html(fileName);
    }
});

//--------- preview image
function readURL(input, previewElement) {
    if (input && input.files && input.files[0]) {
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
                    $(previewElement).removeAttr('src');
                    toastr.error(message.upload_image_dimension);
                    readURL(null, previewElement);
                }
            }
            $(previewElement).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
};
$("#slideImage").change(function () {
    var id = '#previewImg';
    readURL(this, id);
});

$("#portalSlideImage").change(function () {
    var id = '#portalPreviewImg';
    readURL(this, id);
});
function readURL(input, selector) {
    if (input != null && input.files.length > 0) {
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
                    $(input).parent('div').find('.invalid-feedback').hide();
                    $(input).parent('div').find('.custom-file-label').html('Choose File');
                    $(selector).removeAttr('src');
                    toastr.error(messages.upload_image_dimension);
                    readURL(null, selector);
                }
            }
            $(selector).attr('src', e.target.result);
            $('.remove-logo-media,input[name=remove_logo]').remove();
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        $(selector).removeAttr('src');
    }
}
$(document).ready(function() {
    $(document).on('change', '#logo', function(e) {
        var previewElement = $(this).data('previewelement');
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.image_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
                readURL(null, previewElement);
            } else if (e.target.files[0].size > 2097152) {
                toastr.error(messages.image_size_2M_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
                readURL(null, previewElement);
            } else {
                readURL(e.target, previewElement);
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
            readURL(null, previewElement);
        }
    });
    $(document).on('click', '.remove-logo-media', function(e) {
        var _action = $(this).data('action');
        if (_action) {
            $('#remove-media-model-box .remove-media-title').html(remove.title.replace(':action', _action));
            $('#remove-media-model-box .remove-media-message').html(remove.message.replace(':action', _action));
            $('#remove-media-model-box #remove_media_type').val(_action);
            $('#remove-media-model-box').modal('show');
        }
    });
    $(document).on('click', '#remove-media-confirm', function(e) {
        var _action = $('#remove-media-model-box #remove_media_type').val();
        if (_action != "") {
            var _selector = `input[type="hidden"][name="remove_logo_${_action}"]`,
                _length = $('#subCategoryEdit').find(_selector).length,
                _element = "logo",
                _previewElement = $(`#${_element}`).data('previewelement');
            if (_length > 0) {
                $(_selector).remove();
            }
            $(`#${_element}`).empty().val('');
            $(`#${_element}`).parent('div').find('.custom-file-label').html(messages.choose_file);
            readURL(null, _previewElement);
            $('#subCategoryEdit').prepend(`<input name="remove_${_action}" type="hidden" value="1" />`);
            $(`.remove-logo-media[data-action="${_action}"]`).remove();
        }
        $('#remove-media-model-box').modal('hide');
    });
});
''
function readURL(input, selector, fallback) {
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
                    $(input).parent('div').find('.custom-file-label').html('Choose File');
                    $(input).parent('div').find('.invalid-feedback').hide();
                    toastr.error(messages.upload_image_dimension);
                    readURL(null, selector);
                }
            }
            $(selector).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        if (fallback) {
            $(selector).attr('src', fallback);
        } else {
            $(selector).removeAttr('src');
        }
    }
}
$(document).ready(function() {
    $(document).on('change', '#location_logo, #department_logo', function(e) {
        var previewElement = $(this).data('previewelement');
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png'];
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
    $(document).on('click', '.remove-media', function(e) {
        var _action = $(this).data('action'),
            label = ((_action == 'location_logo') ? "Location Logo" : "Department Logo")
        if (_action) {
            $('#remove-media-model-box .remove-media-title').html(`Remove ${label}`);
            $('#remove-media-model-box .remove-media-message').html(`Are you sure you want to remove ${label}?`);
            $('#remove-media-model-box #remove_media_type').val(_action);
            $('#remove-media-model-box').modal('show');
        }
    });
    $(document).on('click', '#remove-media-confirm', function(e) {
        debugger;
        var _action = $('#remove-media-model-box #remove_media_type').val();
        if (_action != "") {
            var _selector = `input[type="hidden"][name="remove_${_action}"]`,
                _length = $('#companyEdit').find(_selector).length,
                _previewElement = $(`#${_action}`).data('previewelement');
            if (_length > 0) {
                $(_selector).remove();
            }
            $(`#${_action}`).empty().val('');
            $(`#${_action}`).parent('div').find('.custom-file-label').html(messages.choose_file);
            readURL(null, _previewElement, defaultImages[_action]);
            $('#changelabelsettings').prepend(`<input name="remove_${_action}" type="hidden" value="1" />`);
            $(`.remove-media[data-action="${_action}"]`).remove();
        }
        $('#remove-media-model-box').modal('hide');
    });
    $(document).on('hidden.bs.modal', '#remove-media-model-box', function(e) {
        $('#remove-media-model-box .remove-media-title, #remove-media-model-box .remove-media-message').html('');
        $('#remove-media-model-box #remove_media_type').val('');
    });
});
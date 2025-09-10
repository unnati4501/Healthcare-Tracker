$(document).ready(function() {
    $('#background_wrapper, #logo_wrapper').hide();

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
                        $(input).parent('div').find('.custom-file-label').html('Choose File');
                        $(selector).removeAttr('src');
                        toastr.error(upload_image_dimension);
                        readURL(null, selector);
                    }
                }
                $(selector).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(selector).removeAttr('src');
        }
    }

    $(document).on('change', '#logo, #background', function(e) {
        var previewElement = $(this).data('previewelement');
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
            allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'];
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

    $(document).on('change', '#category', function(e) {
        var _value = $(this).val();
        $('#name').val($('#name').val() + ' ').trigger('keyup');
        $('#name').val($.trim($('#name').val())).trigger('keyup');
        $('#name-error').remove();
        if (_value == '1' || _value == '2' || _value == '7' || _value == '4' || _value == '9' || _value == '10') { // MasterClass
            $('#background_wrapper, #logo_wrapper').show();
        } else {
            $('#background_wrapper, #logo_wrapper').hide();
        }
    });
});
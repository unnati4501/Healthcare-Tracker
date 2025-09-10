$('input[type="file"]').change(function (e) {
    var fileName = e.target.files[0].name;
    if (fileName.length > 40) {
        fileName = fileName.substr(0, 40);
    }
    var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    if (!allowedMimeTypes.includes(e.target.files[0].type)) {
        toastr.error(message.imageValidError);
        $(e.currentTarget).empty().val('');
        $(this).parent('div').find('.custom-file-label').val('');
    } else if (e.target.files[0].size > 2097152) {
        toastr.error(message.imageSizeError);
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
                var aspectedRatio = ratio;
                var ratioSplit = ratio.split(':');
                var newWidth = ratioSplit[0];
                var newHeight = ratioSplit[1];
                var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
                    $(input).empty().val('');
                    $(input).parent('div').find('.invalid-feedback').hide();
                    $(previewElement).removeAttr('src');
                    toastr.error(message.upload_image_dimension);
                    readURL(null, previewElement);
                }
            }
            $('#previewImg').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        $(previewElement).attr('src', defaultCourseImg);
    }
};
$("#slideImage").change(function () {
    readURL(this);
});

$("#profileImage").change(function () {
    var id = '#previewImg';
    readURL(this, id);
  });

  $("#inputImage").change(function () {
    var id = '#input_previewImg';
    readURL(this, id);
  });

  $('#inputImage').change(function (e) {
    var fileName = e.target.files[0].name;
    $('.custom-file-label').html(fileName);
  });
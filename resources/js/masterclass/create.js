var bar = $('#mainProgrssbar'),
    percent = $('#mainProgrssbar .progpercent');

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

function clearTrailerData(type) {
    if (type == 'video' || type == 'all') {
        $('#trailer_video').val('').removeClass('is-invalid');
        $('#trailer_video').parent('div').find('.custom-file-label').html(messages.choosefile);
    }
    if (type == 'audio' || type == 'all') {
        $('#trailer_audio').val('').removeClass('is-invalid');
        $('#trailer_audio').parent('div').find('.custom-file-label').html(messages.choosefile);
        $('#trailer_audio_background').val('').removeClass('is-invalid');
        $('#trailer_audio_background').parent('div').find('.custom-file-label').html(messages.choosefile);
        readURL(null, '#trailer_audio_background_preview');
    }
    if (type == 'youtube' || type == 'all') {
        $('#trailer_youtube').val('').removeClass('is-invalid');
        $('#trailer_youtube').parent('div').find('.custom-file-label').html(messages.choosefile);
        $('#trailer_youtube_background').val('').removeClass('is-invalid');
        $('#trailer_youtube_background').parent('div').find('.custom-file-label').html(messages.choosefile);
        readURL(null, '#trailer_youtube_background_preview');
    }
    if (type == 'vimeo' || type == 'all') {
        $('#trailer_vimeo').val('').removeClass('is-invalid');
        $('#trailer_vimeo').parent('div').find('.custom-file-label').html(messages.choosefile);
        $('#trailer_vimeo_background').val('').removeClass('is-invalid');
        $('#trailer_vimeo_background').parent('div').find('.custom-file-label').html(messages.choosefile);
        readURL(null, '#trailer_vimeo_background_preview');
    }
}
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
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
    $('.select2').select2({
        allowClear: true,
        width: '100%'
    });
    $('#goal_tag').select2({
        multiple: true,
        closeOnSelect: false,
    });
    $(document).on('change', '#logo, #trailer_audio_background, #trailer_youtube_background, #trailer_audio_background_portal, #track_vimeo, #header_image', function(e) {
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
    $('#trailer_audio, #trailer_video').change(function(e) {
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedImageMimeTypes = ['audio/mp3', 'audio/mpeg', 'audio/m4a', 'audio/x-m4a'],
                allowedVideoMimeTypes = ['video/mp4'],
                trackType = $('#trailer_type').val();
            if (fileName.length > 40) {
                fileName = fileName.substr(0, 40);
            }
            if (trackType == 2 && !allowedVideoMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.video_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choosefile);
            } else if (trackType == 1 && !allowedImageMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.meditation_track_audio_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choosefile);
            } else if (e.target.files[0].size > 104857600) {
                if (trackType == 2) {
                    toastr.error(messages.video_size_100M_error);
                } else {
                    toastr.error(messages.audio_size_100M_error);
                }
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choosefile);
            } else {
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').html(messages.choosefile);
        }
    });
    $(document).on('change', '#trailer_type', function(e) {
        var _value = $(this).val();
        if (_value != '') {
            $('#trailer_audio_wrapper,#trailer_video_wrapper,#trailer_youtube_wrapper,#trailer_vimeo_wrapper').hide();
            if (_value == 1) {
                $('#trailer_audio_wrapper').show();
            } else if (_value == 2) {
                $('#trailer_video_wrapper').show();
            } else if (_value == 3) {
                $('#trailer_youtube_wrapper').show();
            } else if (_value == 4) {
                $('#trailer_vimeo_wrapper').show();
            }
        } else {
            $('#trailer_video_wrapper').hide();
            $('#trailer_audio_wrapper').hide();
        }
        if(_value == 3) {
            $('#setPermissionList #check_2, #check_1').parent().parent().remove();
        } else {
            // $('#setPermissionList #check_2, #check_1').parent().parent().find('input').prop({disabled:false});
            $('.tree-multiselect').remove();
            $("#masterclass_company").treeMultiselect({
                enableSelectAll: true,
                searchable: true,
                startCollapsed: true
            });
        }
        clearTrailerData('all');
    });
    $('#courseAdd').ajaxForm({
        type: 'post',
        dataType: 'json',
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html(messages.uploading_media);
            $('.progress-loader-wrapper').show();
            $('#courseAdd .card-footer button, #courseAdd .card-footer a').attr('disabled', 'disabled');
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        uploadProgress: function(event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal)
            percent.html(percentVal);
            if (percentComplete == 100) {
                $('.progress-loader-wrapper .status-text').html(messages.processing_media);
            }
        },
        success: function(data) {
            var percentVal = '100%';
            bar.width(percentVal)
            percent.html(percentVal);
            if (data.status && data.status == 1) {
                window.location.replace(url.success);
            } else {
                if (data.message && data.message != '') {
                    toastr.error(data.message);
                }
            }
        },
        error: function(data) {
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(messages.something_wrong_try_again);
            }
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        complete: function(xhr) {
            $('.progress-loader-wrapper').hide();
            $('#courseAdd .card-footer button, #courseAdd .card-footer a').removeAttr('disabled');
        }
    });
    $("#masterclass_company").treeMultiselect({
        enableSelectAll: true,
        searchable: true,
        startCollapsed: true,
        onChange: function(allSelectedItems, addedItems, removedItems) {
            var selectedMembers = $('#masterclass_company').val().length;
            if (selectedMembers == 0) {
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
    });
});
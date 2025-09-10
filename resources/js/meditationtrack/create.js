var objectUrl,
    bar = $('#mainProgrssbar'),
    percent = $('#mainProgrssbar .progpercent');

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
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    $('.numeric').numeric({
        decimal: false,
        negative: false
    });
    $('.select2').select2({
        allowClear: true,
        width: '100%'
    });
    $('#goal_tag').select2({
        placeholder: "Select Goal Tags",
        multiple: true,
        closeOnSelect: false,
    });
    $("#track_company").treeMultiselect({
        enableSelectAll: true,
        searchable: true,
        startCollapsed: true,
        onChange: function(allSelectedItems, addedItems, removedItems) {
            var trackCompany = $('#track_company').val().length;
            if (trackCompany == 0) {
                $('#trackAdd').valid();
                $('#track_company').addClass('is-invalid');
                $('#track_company-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#track_company').removeClass('is-invalid');
                $('#track_company-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
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
    $(document).on('focusout', '#duration', function() {
        $(this).val($.trim($(this).val()).replace(/^0+/, ''));
    });
    $(document).on('change', '#track_type', function(e) {
        var _value = $(this).val(),
            duration_readonly = true;
        $('.type_wrappers').hide();
        $('#audio_type_wrapper').hide();
        $('#duration').val('');
        $('.type_wrappers').find('input').not('input[type="radio"]').val('');
        $('.type_wrappers').find('.custom-file-label').html(messages.choose_file);
        $('.type_wrappers').find('img').prop('src', bg_background);
        $('.type_wrappers').find('input').removeClass('is-invalid is-valid');
        if (_value != "") {
            if (_value == '1') {
                $('#music').prop('checked', true);
                $('#audio_type_wrapper, #audio_wrapper, #audio_background_wrapper, #audio_background_wrapper_portal').show();
            } else if (_value == '2') {
                $('#video_wrapper').show();
            } else if (_value == '3') {
                $('#youtube_wrapper').show();
                duration_readonly = false;
            } else if (_value == '4') {
                $('#vimeo_wrapper').show();
                duration_readonly = false;
            }
        }
        $('#duration').prop('readonly', duration_readonly);
    });
    $(document).on('change', '#track_cover, #track_background, #track_background_portal, #header_image', function(e) {
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
    $(document).on('change', '#track_audio, #track_video', function(e) {
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedImageMimeTypes = ['audio/mp3', 'audio/mpeg', 'audio/m4a', 'audio/x-m4a'],
                allowedVideoMimeTypes = ['video/mp4'],
                lessonType = $('#track_type').val();
            $('#duration').val('');
            if (lessonType == 2 && !allowedVideoMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.video_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
            } else if (lessonType == 1 && !allowedImageMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.meditation_track_audio_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
            } else if (e.target.files[0].size > 104857600) {
                if (lessonType == 2) {
                    toastr.error(messages.video_size_100M_error);
                } else {
                    toastr.error(messages.audio_size_100M_error);
                }
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
            } else {
                $(this).parent('div').find('.custom-file-label').html(fileName);
                var objectUrl = URL.createObjectURL(e.target.files[0]);
                if (lessonType == 1) {
                    $("#audio_duration").prop("src", objectUrl);
                } else if (lessonType == 2) {
                    $("#video_duration").prop({
                        preload: 'metadata',
                        src: objectUrl,
                    });
                }
            }
        } else {
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').html(messages.choose_file);
            $('#duration').val('');
        }
    });
    $('#video_duration').on('loadedmetadata', function(e) {
        var seconds = e.currentTarget.duration,
            duration = moment.duration(seconds, "seconds"),
            time = ((duration.hours() * 3600) + (duration.minutes() * 60) + duration.seconds());
        $("#duration").val(time);
        $("#trackAdd").validate().element("#duration");
        /*if(time >= 1 && time <= 3600) {
            $("#duration").val(time);
            $("#trackAdd").validate().element("#duration");
        } else {
            toastr.error("Track file duration must be between 1 second to 1 hour.");
            $('#track_video').val('');
            $('#track_video').parent('div').find('.custom-file-label').html(messages.choose_file);
        }*/
        URL.revokeObjectURL(objectUrl);
    });
    $("#audio_duration").on("canplaythrough", function(e) {
        var seconds = e.currentTarget.duration,
            duration = moment.duration(seconds, "seconds"),
            time = ((duration.hours() * 3600) + (duration.minutes() * 60) + duration.seconds());
        $("#duration").val(time);
        $("#trackAdd").validate().element("#duration");
        /*if(time >= 1 && time <= 3600) {
            $("#duration").val(time);
            $("#trackAdd").validate().element("#duration");
        } else {
            toastr.error("Track file duration must be between 1 second to 1 hour.");
            $('#track_audio').val('');
            $('#track_audio').parent('div').find('.custom-file-label').html(messages.choose_file);
        }*/
        URL.revokeObjectURL(objectUrl);
    });
    $(document).on('change','#track_type', function(){
        var _value = $(this).val();
        if(_value == 3) {
            $('#setPermissionList #check_2, #check_1').parent().parent().remove();
        } else {
            // $('#setPermissionList #check_2, #check_1').parent().parent().find('input').prop({disabled:false});
            $('.tree-multiselect').remove();
            $("#track_company").treeMultiselect({
                enableSelectAll: true,
                searchable: true,
                startCollapsed: true
            });
        }
    });
    $('#trackAdd').ajaxForm({
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html(messages.uploading_media);
            $('.progress-loader-wrapper').show();
            $('#trackAdd .card-footer button, #trackAdd .card-footer a').attr('disabled', 'disabled');
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
            $('.progress-loader-wrapper').hide();
            $('#trackAdd .card-footer button, #trackAdd .card-footer a').removeAttr('disabled');
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
            $('.progress-loader-wrapper').hide();
            $('#trackAdd .card-footer button, #trackAdd .card-footer a').removeAttr('disabled');
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
            $('#trackAdd .card-footer button, #trackAdd .card-footer a').removeAttr('disabled');
        }
    });
});
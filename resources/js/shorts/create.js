$(document).ready(function() {
    var objectUrl,
        bar = $('#mainProgrssbar'),
        percent = $('#mainProgrssbar .progpercent');

    $('.select2').select2({
        allowClear: true,
        width: '100%'
    });

    $('#goal_tag').select2({
        placeholder: placeholder.select_goal_tags,
        multiple: true,
        closeOnSelect: false,
    });



    $(document).on('change', '#header_image', function (e) {
        var previewElement = $(this).data('previewelement');
        if(e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];

            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(message.image_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html('Choose File');
                readURL(null, previewElement);
            } else if (e.target.files[0].size > 2097152) {
                toastr.error(message.image_size_2M_error);
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

    $(document).on('change', '#video', function (e) {
        if(e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedImageMimeTypes = ['audio/mp3', 'audio/mpeg','audio/m4a','audio/x-m4a'],
                allowedVideoMimeTypes = ['video/mp4'],
                lessonType = $('#shorts_type').val();
            $('#duration').val('');


            if(lessonType == 1 && !allowedVideoMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(message.video_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html('Choose File');
            } else if (e.target.files[0].size > 262144000) {
                if(lessonType == 1) {
                    toastr.error(message.video_size_250M_error);
                } else {
                    toastr.error(message.video_size_250M_error);
                }
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html('Choose File');
            } else {
                $(this).parent('div').find('.custom-file-label').html(fileName);
                var objectUrl = URL.createObjectURL(e.target.files[0]);
                $("#video_duration").prop({
                    preload: 'metadata',
                    src: objectUrl,
                });
            }
        } else {
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').html('Choose File');
            $('#duration').val('');
        }
    });

    $('#video_duration').on('loadedmetadata', function(e) {
        var seconds = e.currentTarget.duration,
            duration = moment.duration(seconds, "seconds"),
            time = ((duration.hours() * 3600) + (duration.minutes() * 60) + duration.seconds());
        $("#duration").val(time);
        $("#shortsAdd").validate().element("#duration");
        URL.revokeObjectURL(objectUrl);
    });

    $('#shortsAdd').ajaxForm({
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html(message.uploading_media);
            $('.progress-loader-wrapper').show();
            $('#shortsAdd .card-footer button, #shortsAdd .card-footer a').attr('disabled', 'disabled');
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        uploadProgress: function(event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal)
            percent.html(percentVal);
            if(percentComplete == 100) {
                $('.progress-loader-wrapper .status-text').html(message.processing_media);
            }
        },
        success: function(data) {
            $('.progress-loader-wrapper').hide();
            $('#shortsAdd .card-footer button, #shortsAdd .card-footer a').removeAttr('disabled');
            var percentVal = '100%';
            bar.width(percentVal)
            percent.html(percentVal);
            if(data.status && data.status == 1) {
                window.location.replace(url.shortsIndex);
            } else {
                if(data.message && data.message != '') { toastr.error(data.message); }
            }
        },
        error: function(data) {
            $('.progress-loader-wrapper').hide();
            $('#shortsAdd .card-footer button, #shortsAdd .card-footer a').removeAttr('disabled');

            if(data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.something_wrong_try_again);
            }

            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        complete: function(xhr) {
            $('.progress-loader-wrapper').hide();
            $('#shortsAdd .card-footer button, #shortsAdd .card-footer a').removeAttr('disabled');
        }
    });
    $("select#shorts_company").treeMultiselect({
        enableSelectAll: true,
        searchParams: ['section', 'text'],
        searchable: true,
        startCollapsed: true,
        onChange: function (allSelectedItems, addedItems, removedItems) {
            var shortsCompany = $('#shorts_company').val().length;
            if (shortsCompany == 0) {
                $('#shortsAdd').valid();
                $('#shorts_company').addClass('is-invalid');
                $('#shorts_company-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#shorts_company').removeClass('is-invalid');
                $('#shorts_company-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });

    if ($("#setPermissionList").length > 0) {
        $.mCustomScrollbar.defaults.scrollButtons.enable = true;
        $.mCustomScrollbar.defaults.axis = "yx";
        $("#setPermissionList").mCustomScrollbar({
            axis: "y",
            theme: "inset-dark"
        });
    }

    $(document).on('change', '#shorts_type', function(e) {
        var _value = $(this).val(),
            duration_readonly = true;

        $('#video_wrapper,#youtube_wrapper,#vimeo_wrapper').hide();
        $('#duration').val('');

        if(_value != "") {
            if(_value == '1') {
                $('#video_wrapper').show();
            } else if(_value == '2') {
                $('#youtube_wrapper').show();
                duration_readonly = false;
            } else {
                $('#vimeo_wrapper').show();
                duration_readonly = false;
            }
        }
        if(_value == 2) {
            $('#setPermissionList #check_2, #check_1').parent().parent().remove();
        } else {
            $('.tree-multiselect').remove();
            $("#shorts_company").treeMultiselect({
                enableSelectAll: true,
                searchParams: ['section', 'text'],
                searchable: true,
                startCollapsed: true
            });
        }
        $('#duration').prop('readonly', duration_readonly);
    });
});

function readURL(input, selector) {
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
                var aspectedRatio = ratio;
                var ratioSplit = ratio.split(':');
                var newWidth = ratioSplit[0];
                var newHeight = ratioSplit[1];
                var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
                    $(input).empty().val('');
                    $(input).parent('div').find('.custom-file-label').html('Choose File');
                    $(input).parent('div').find('.invalid-feedback').remove();
                    $(selector).removeAttr('src');
                    toastr.error(message.upload_image_dimension);
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
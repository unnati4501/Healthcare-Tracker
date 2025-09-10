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
    // Custom Scrolling
    if ($("#setPermissionList").length > 0) {
        $.mCustomScrollbar.defaults.scrollButtons.enable = true;
        $.mCustomScrollbar.defaults.axis = "yx";
        $("#setPermissionList").mCustomScrollbar({
            axis: "y",
            theme: "inset-dark"
        });
    }
    $("#podcast_company").treeMultiselect({
        enableSelectAll: true,
        searchable: true,
        startCollapsed: true,
        onChange: function(allSelectedItems, addedItems, removedItems) {
            var trackCompany = $('#podcast_company').val().length;
            if (trackCompany == 0) {
                $('#podcastEdit').valid();
                $('#podcast_company').addClass('is-invalid');
                $('#podcast_company-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#podcast_company').removeClass('is-invalid');
                $('#podcast_company-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });
    $(document).on('focusout', '#duration', function() {
        $(this).val($.trim($(this).val()).replace(/^0+/, ''));
    });
    
    $('#duration').prop('readonly', true);
    $(document).on('change', '#podcast_logo', function(e) {
        var previewElement = $(this).data('previewelement');
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.image_valid_error);
                $(e.currentTarget).empty().val('');
                readURL(null, previewElement);
            } else if (e.target.files[0].size > 2097152) {
                toastr.error(messages.image_size_2M_error);
                $(e.currentTarget).empty().val('');
                readURL(null, previewElement);
            } else {
                readURL(e.target, previewElement);
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            readURL(null, previewElement);
        }
    });
    $(document).on('change', '#track_audio', function(e) {
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedImageMimeTypes = ['audio/mp3', 'audio/mpeg', 'audio/m4a', 'audio/x-m4a'];
                $('#duration').val('');
            if (!allowedImageMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.podcast_audio_valid_error);
                $(e.currentTarget).empty().val('');
            } else if (e.target.files[0].size > 104857600) {
                toastr.error(messages.audio_size_100M_error);
                $(e.currentTarget).empty().val('');
            } else {
                $(this).parent('div').find('.custom-file-label').html(fileName);
                var objectUrl = URL.createObjectURL(e.target.files[0]);
                $("#audio_duration").prop("src", objectUrl);
            }
        } else {
            $(e.currentTarget).empty().val('');
            $('#duration').val('');
        }
    });
    
    $("#audio_duration").on("canplaythrough", function(e) {
        var seconds = e.currentTarget.duration,
            duration = moment.duration(seconds, "seconds"),
            time = ((duration.hours() * 3600) + (duration.minutes() * 60) + duration.seconds());
        $("#duration").val(time);
        $("#podcastEdit").validate().element("#duration");
        /*if(time >= 1 && time <= 3600) {
            $("#duration").val(time);
            $("#podcastEdit").validate().element("#duration");
        } else {
            toastr.error("Track file duration must be between 1 second to 1 hour.");
            $('#track_audio').val('');
        }*/
        URL.revokeObjectURL(objectUrl);
    });
    $('#podcastEdit').ajaxForm({
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html('Uploading media....');
            $('.progress-loader-wrapper').show();
            $('#podcastEdit .card-footer button, #podcastEdit .card-footer a').attr('disabled', 'disabled');
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        uploadProgress: function(event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal)
            percent.html(percentVal);
            if (percentComplete == 100) {
                $('.progress-loader-wrapper .status-text').html('Processing on media...');
            }
        },
        success: function(data) {
            $('.progress-loader-wrapper').hide();
            $('#podcastEdit .card-footer button, #podcastEdit .card-footer a').removeAttr('disabled');
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
            $('#podcastEdit .card-footer button, #podcastEdit .card-footer a').removeAttr('disabled');
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
            $('#podcastEdit .card-footer button, #podcastEdit .card-footer a').removeAttr('disabled');
        }
    });
});
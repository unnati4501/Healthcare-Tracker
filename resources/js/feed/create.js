var bar = $('#mainProgrssbar'),
        percent = $('#mainProgrssbar .progpercent'),
        start = new Date(),
        end;

start.setHours(start.getHours()+1);
end = new Date(new Date().setYear(start.getFullYear() + 100));

function readURL(input, previewElement) {
    if (input && input.files.length > 0) {
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
                    $(previewElement).removeAttr('src');
                    toastr.error(message.upload_image_dimension);
                    readURL(null, previewElement);
                }
            }
            // End validation for image max height / width and Aspected Ratio
            $(previewElement).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        $(previewElement).removeAttr('src');
    }
}

$(document).ready(function() {
    $(document).on('click','#feedSubmit',function(){
        $('#feedAdd').valid();
        if($("#start_date").val() != "" && $("#end_date").val() != "") {
            var start_date = new Date($("#start_date").val());
            var end_date = new Date($("#end_date").val());

            var diffMs = (end_date - start_date);
            var diffDays = Math.floor(diffMs / 86400000); // days
            var diffHrs = Math.floor((diffMs % 86400000) / 3600000); // hours
            var diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000);

            if(diffDays == 0 && diffHrs == 0 && diffMins < 15) {
                event.preventDefault();
                toastr.error(message.start_end_interval_message);
            }
        }

        if(condition.isSA){
            var feedCompany = $('#feed_company').val();
            if (feedCompany == 0) {
                $('#feedAdd').valid();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });

    $('#feedAdd').validate();

    $('.select2').select2({
        allowClear: true,
        width: '100%'
    });

    $('#goal_tag').select2({
        placeholder: message.select_goal_tags,
        multiple: true,
        closeOnSelect: false,
    });

    $("#start_date").datetimepicker({
        format: 'yyyy-mm-dd hh:00',
        startDate: start,
        endDate: end,
        autoclose: true,
        fontAwesome:true,
        minView:'day',
        todayHighlight: true,
        pickerPosition: "top-right"
    }).on('changeDate', function () {
        $('#end_date').datetimepicker('setStartDate', new Date($(this).val()));
        $('#start_date').valid();
    });

    $("#end_date").datetimepicker({
        format: 'yyyy-mm-dd hh:00',
        startDate: start,
        endDate: end,
        autoclose: true,
        fontAwesome:true,
        minView:'day',
        todayHighlight: true,
        pickerPosition: "top-right"
    }).on('changeDate', function () {
        $('#start_date').datetimepicker('setEndDate', new Date($(this).val()));
        $('#end_date').valid();
    });

    $(document).on('change.select2', '#multicompany', function(e) {
        var selected = $('#multicompany option:selected').length,
            total_option = $('#multicompany option').length;
        $('#select_all_feed_company').prop('checked', (selected == total_option));
    });

    $(document).on('change', '#select_all_feed_company', function(e) {
        var isChecked = $(this).is(":checked");
        $('#multicompany option').prop('selected', isChecked).trigger('change');
    });

    $("#start_date,#end_date").keypress(function(event) { event.preventDefault(); });
    $("#start_date").change(function(event) { $("#start_date").trigger("changeDate"); });
    $("#end_date").change(function(event) { $("#end_date").trigger("changeDate"); });

    if(condition.isSA){
        $("#feed_company").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                var feedCompany = $('#feed_company').val().length;
                if (feedCompany == 0) {
                    $('#feedAdd').valid();
                    $('#feed_company-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    $('#feed_company-error').hide();
                    $('.tree-multiselect').css('border-color', '#D8D8D8');
                }
            }
        });
    }
    if ($("#setPermissionList").length > 0) {
        $.mCustomScrollbar.defaults.scrollButtons.enable = true;
        $.mCustomScrollbar.defaults.axis = "yx";
        $("#setPermissionList").mCustomScrollbar({
            axis: "y",
            theme: "inset-dark"
        });
    }

    $(document).on('change', '#feed_type', function(e) {
        var _value = $(this).val();
        $('.type_wrappers').hide();
        $('#main_wrapper').hide();
        $('#main_wrapper').find('input, textarea').val('');
        $('#main_wrapper').find('.custom-file-label').html(message.choose_file);
        $('#main_wrapper').find('img').prop('src', assets.boxed_bg);
        $('#main_wrapper').find('input, textarea').removeClass('is-invalid is-valid');

        // if(CKEDITOR.instances['content']) {
        //     CKEDITOR.instances['content'].updateElement();
        //     CKEDITOR.instances['content'].setData('');
        // }

        if(_value != "") {
            $('#main_wrapper').show();
            if(_value == '1') {
                $('#audio_wrapper').show();
            } else if(_value == '2') {
                $('#video_wrapper').show();
            } else if(_value == '3') {
                $('#youtube_wrapper').show();
            } else if(_value == '4') {
                $('#content_wrapper').show();
            } else if(_value == '5') {
                $('#vimeo_wrapper').show();
            }
        }
        if(_value == 3) {
            $('#setPermissionList #check_2, #check_1').parent().parent().remove();
        } else {
            // $('#setPermissionList #check_2, #check_1').parent().parent().find('input').prop({disabled:false});
            $('.tree-multiselect').remove();
            $("#feed_company").treeMultiselect({
                enableSelectAll: true,
                searchable: true,
                startCollapsed: true
            });
        }
    });

    $(document).on('change', '#featured_image, #audio_background, #audio_background_portal, #header_image', function (e) {
        var previewElement = $(this).data('previewelement');
        if(e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];

            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(message.image_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(message.choose_file);
                readURL(null, previewElement);
            } else if (e.target.files[0].size > 2097152) {
                toastr.error(message.image_size_2M_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(message.choose_file);
                readURL(null, previewElement);
            } else {
                readURL(e.target, previewElement);
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            $(this).parent('div').find('.custom-file-label').html(message.choose_file);
            readURL(null, previewElement);
        }
    });

    $(document).on('change', '#audio, #video', function (e) {
        if(e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedAudioMimeTypes = ['audio/mp3', 'audio/mpeg','audio/m4a','audio/x-m4a'],
                allowedVideoMimeTypes = ['video/mp4'],
                lessonType = $('#feed_type').val();

            if(lessonType == 2 && !allowedVideoMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(message.video_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(message.choose_file);
            } else if(lessonType == 1 && !allowedAudioMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(message.audio_valid_error);
                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(message.choose_file);
            } else if (e.target.files[0].size > 104857600) {
                if(lessonType == 2) {
                    toastr.error(message.video_size_100M_error);
                } else {
                    toastr.error(message.audio_size_100M_error);
                }

                $(e.currentTarget).empty().val('');
                $(this).parent('div').find('.custom-file-label').html(message.choose_file);
            } else {
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').html(message.choose_file);
        }
    });

    $('#feedAdd').ajaxForm({
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html(message.uploading_media);
            $('.progress-loader-wrapper').show();
            $('#feedAdd .card-footer button, #feedAdd .card-footer a').attr('disabled', 'disabled');
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
            $('#feedAdd .card-footer button, #feedAdd .card-footer a').removeAttr('disabled');
            var percentVal = '100%';
            bar.width(percentVal)
            percent.html(percentVal);
            if(data.status && data.status == 1) {
                window.location.replace(url.feed_index);
            } else {
                toastr.error((data.message && data.message != '') ? data.message : message.something_wrong_try_again);
            }
        },
        error: function(data) {
            $('.progress-loader-wrapper').hide();
            $('#feedAdd .card-footer button, #feedAdd .card-footer a').removeAttr('disabled');

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
            $('#feedAdd .card-footer button, #feedAdd .card-footer a').removeAttr('disabled');
        }
    });

    $("#youtube").focusout(function () {
        $(this).val($.trim($(this).val()).replace(/^0+/, ''));
    });


});
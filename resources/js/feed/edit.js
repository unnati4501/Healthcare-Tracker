var bar = $('#mainProgrssbar'),
    percent = $('#mainProgrssbar .progpercent'),
    start = new Date(),
    end,
    creator_id = condition.creator_id;

start.setHours(start.getHours()+1);
end = new Date(new Date().setYear(start.getFullYear() + 100));
var _feed_type = $('#feed_type').val();
var contentCkeditor;
function loadCkeditor() {
    var imageUrl = document.querySelector('.article-ckeditor').getAttribute('data-upload-path');
    CKEDITOR.ClassicEditor.create(document.querySelector( '.article-ckeditor' ), {
        ckfinder: {
            uploadUrl: imageUrl,
        },
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                'bulletedList', 'numberedList', 'todoList', '|',
                'outdent', 'indent', '|',
                'undo', 'redo',
                '-',
                'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                'alignment', '|',
                'link', 'insertImage', 'blockQuote', 'mediaEmbed', '|',
                'specialCharacters', '|',
                'sourceEditing'
            ],
            shouldNotGroupWhenFull: true
        },
        list: {
            properties: {
                styles: true,
                startIndex: true,
                reversed: true
            }
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
            ]
        },
        fontFamily: {
            options: [
                'default',
                'Arial, Helvetica, sans-serif',
                'Courier New, Courier, monospace',
                'Georgia, serif',
                'Lucida Sans Unicode, Lucida Grande, sans-serif',
                'Tahoma, Geneva, sans-serif',
                'Times New Roman, Times, serif',
                'Trebuchet MS, Helvetica, sans-serif',
                'Verdana, Geneva, sans-serif'
            ],
            supportAllValues: true
        },
        fontSize: {
            options: [ 10, 12, 14, 'default', 18, 20, 22 ],
            supportAllValues: true
        },
        htmlSupport: {
            allow: [
                {
                    name: /.*/,
                    attributes: true,
                    classes: true,
                    styles: true
                }
            ]
        },
        link: {
            decorators: {
                addTargetToExternalLinks: true,
                defaultProtocol: 'https://',
                toggleDownloadable: {
                    mode: 'manual',
                    label: 'Downloadable',
                    attributes: {
                        download: 'file'
                    }
                }
            }
        },
        // https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html#configuration
        mention: {
            feeds: [
                {
                    marker: '@',
                    feed: [
                        '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                        '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                        '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                        '@sugar', '@sweet', '@topping', '@wafer'
                    ],
                    minimumCharacters: 1
                }
            ]
        },
        // The "super-build" contains more premium features that require additional configuration, disable them below.
        // Do not turn them on unless you read the documentation and know how to configure them and setup the editor.
        removePlugins: [
            // These two are commercial, but you can try them out without registering to a trial.
            'CKBox',
            //'CKFinder',
           // 'EasyImage',
            // 'Base64UploadAdapter',
            'RealTimeCollaborativeComments',
            'RealTimeCollaborativeTrackChanges',
            'RealTimeCollaborativeRevisionHistory',
            'PresenceList',
            'Comments',
            'TrackChanges',
            'TrackChangesData',
            'RevisionHistory',
            'Pagination',
            'WProofreader',
            'MathType'
        ]
    }).then( editor => {
        window.editor = editor;
        contentCkeditor = editor;
    })
    .catch( err => {
        console.error( err.stack );
    });
}
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

$('document').ready(function() {

    setTimeout(function() {
        $('#feedEdit').validate();
    }, 2000);

    loadCkeditor();

    // Showing message for tree selection remove data
    if(localStorage.getItem("tree-selection-error-message")){
        toastr.error(localStorage.getItem("tree-selection-error-message"));
        localStorage.clear();
    }

    $(document).on('click', '#feedSubmit', function() {
        $('#feedEdit').valid();
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

        if(_feed_type == '4') {
            content = contentCkeditor.getData();
            description = $(content).text().trim();
            contentCkeditor.setData(content);
            contentCkeditor.destroy();
            loadCkeditor();
            if (description == '') {
                event.preventDefault();
                $('#description-error').html("The description field is required").addClass('invalid-feedback').show();
            } else {
                $('#description-error').removeClass('invalid-feedback').hide();
            }
        }

        if(condition.isSA){
            var feedCompany = $('#feed_company').val();
            if (feedCompany == 0) {
                $('#feedEdit').valid();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });

    $('.select2').select2({
        allowClear: true,
        width: '100%'
    });

    $('#goal_tag').select2({
        placeholder: message.select_goal_tags,
        multiple: true,
        closeOnSelect: false,
    });

    var selected = $('#multicompany option:selected').length,
        total_option = $('#multicompany option').length;
    $('#select_all_feed_company').prop('checked', (selected == total_option));

    $(document).on('change', '#select_all_feed_company', function(e) {
        var isChecked = $(this).is(":checked");
        $('#multicompany option').prop('selected', isChecked).trigger('change');
    });

    $('#multicompany').on('change.select2', function(e) {
        var selected = $('#multicompany option:selected').length,
            total_option = $('#multicompany option').length;
        $('#select_all_feed_company').prop('checked', (selected == total_option));
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

    $("#start_date,#end_date").keypress(function(event) {
        event.preventDefault();
    });

    $("#end_date").change(function(event) {
        $("#end_date").trigger("changeDate");
    });

    $("#start_date").change(function(event) {
        $("#start_date").trigger("changeDate");
    });

    $(document).on('change', '#featured_image, #audio_background, #audio_background_portal, #header_image', function (e) {
        var previewElement = $(this).data('previewelement');
        if(e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];

            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(message.image_valid_error);
                $(e.currentTarget).empty().val('');
                readURL(null, previewElement);
            } else if (e.target.files[0].size > 2097152) {
                toastr.error(message.image_size_2M_error);
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

    $(document).on('change', '#audio, #video', function (e) {
        if(e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedAudioMimeTypes = ['audio/mp3', 'audio/mpeg','audio/m4a','audio/x-m4a'],
                allowedVideoMimeTypes = ['video/mp4'],
                lessonType = $('#feed_type').val();

            if(lessonType == 2 && !allowedVideoMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(message.video_valid_error);
                $(e.currentTarget).empty().val('');
            } else if(lessonType == 1 && !allowedAudioMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(message.audio_valid_error);
                $(e.currentTarget).empty().val('');
            } else if (e.target.files[0].size > 104857600) {
                if(lessonType == 2) {
                    toastr.error(message.video_size_100M_error);
                } else {
                    toastr.error(message.audio_size_100M_error);
                }

                $(e.currentTarget).empty().val('');
            } else {
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            $(e.currentTarget).empty().val('');
        }
    });

    $('#feedEdit').ajaxForm({
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html(message.uploading_media);
            $('.progress-loader-wrapper').show();
            $('#feedEdit .card-footer button, #feedEdit .card-footer a').attr('disabled', 'disabled');
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
            $('#feedEdit .card-footer button, #feedEdit .card-footer a').removeAttr('disabled');
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
            $('#feedEdit .card-footer button, #feedEdit .card-footer a').removeAttr('disabled');

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
            $('#feedEdit .card-footer button, #feedEdit .card-footer a').removeAttr('disabled');
        }
    });

    $("#youtube").focusout(function () {
        $(this).val($.trim($(this).val()).replace(/^0+/, ''));
    });

    if(condition.isSA){
        $("#feed_company").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                var feedCompany = $('#feed_company').val().length;
                var _removeItem = removedItems[0].value;
                if(_removeItem == creator_id){
                    localStorage.setItem("tree-selection-error-message",message.feed_cannot_remove);
                    window.location.reload();
                    return false;
                }
                if (feedCompany == 0) {
                    $('#feedEdit').valid();
                    $('#feed_company-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    $('#feed_company-error').hide();
                    $('.tree-multiselect').css('border-color', '#D8D8D8');
                }
            }
        });
        $('#feedCompanyList .remove-selected').click(function(e){
            e.preventDefault();
            var _id = $(this).parent().data('value');
            if(_id == creator_id){
                localStorage.setItem("tree-selection-error-message",message.feed_cannot_remove);
                window.location.reload();
                return false;
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

    
    if(_feed_type != "") {
        $('#main_wrapper').show();
        if (_feed_type == '1') {
            $('#audio_wrapper').show();
        } else if(_feed_type == '2') {
            $('#video_wrapper').show();
        } else if(_feed_type == '3') {
            $('#youtube_wrapper').show();
            $('#setPermissionList #check_2, #check_1').parent().parent().remove();
        } else if(_feed_type == '4') {
            $('#content_wrapper').show();
        } else if(_feed_type == '5') {
            $('#vimeo_wrapper').show();
        }
    }
});
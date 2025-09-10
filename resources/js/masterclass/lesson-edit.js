var objectUrl,
    bar = $('#mainProgrssbar'),
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
        $(previewElement).removeAttr('src');
    }
}

function sectomins(value, ceil) {
    var ceil = (ceil || false),
        sec_num = parseInt(value, 10),
        minutes = Math.floor(sec_num / 60),
        seconds = sec_num - (minutes * 60);
    if (minutes < 10) {
        minutes = "0" + minutes;
    }
    if (seconds < 10) {
        seconds = "0" + seconds;
    }
    return ((ceil === true) ? Math.ceil(`${minutes}.${seconds}`) : `${minutes}.${seconds}`);
}
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
        removePlugins: [
            'CKBox',
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
$(document).ready(function() {
   
    loadCkeditor();
    
    setTimeout(function() {
        $('#courseLessionEdit').validate();
    }, 2000);
    $('.numeric').numeric({
        decimal: false,
        negative: false
    });
    $("#duration").focusout(function() {
        $(this).val($.trim($(this).val()).replace(/^0+/, ''));
    });
    $("#youtube").focusout(function() {
        $(this).val($.trim($(this).val()).replace(/^0+/, ''));
    });
    var duration_readonly = true;
    if (_lesson_type != "") {
        $('#main_wrapper').show();
        if (_lesson_type == '1') {
            $('#audio_wrapper').show();
        } else if (_lesson_type == '2') {
            $('#video_wrapper').show();
        } else if (_lesson_type == '3') {
            $('#youtube_wrapper').show();
            duration_readonly = false;
        } else if (_lesson_type == '4') {
            $('#content_wrapper').show();
            duration_readonly = false;
        } else if (_lesson_type == '5') {
            $('#vimeo_wrapper').show();
            duration_readonly = false;
        }
        $('#duration').prop("readonly", duration_readonly);
    }
    $('#video_duration').on('loadedmetadata', function(e) {
        var seconds = e.currentTarget.duration,
            duration = moment.duration(seconds, "seconds"),
            time = ((duration.hours() * 3600) + (duration.minutes() * 60) + duration.seconds());
        if (time > 59) {
            $("#duration").val(sectomins(time, true));
            $("#courseLessionEdit").validate().element("#duration");
        } else {
            toastr.error(messages.video_length_limit);
            $('#video').val('');
        }
        URL.revokeObjectURL(objectUrl);
    });
    $("#audio_duration").on("canplaythrough", function(e) {
        var seconds = e.currentTarget.duration,
            duration = moment.duration(seconds, "seconds"),
            time = ((duration.hours() * 3600) + (duration.minutes() * 60) + duration.seconds());
        if (time > 59) {
            $("#duration").val(sectomins(time, true));
            $("#courseLessionEdit").validate().element("#duration");
        } else {
            toastr.error(messages.audio_length_limit);
            $('#audio').val('');
        }
        URL.revokeObjectURL(objectUrl);
    });
    
    $(document).on('change', '#audio_background, #audio_background_portal, #logo', function(e) {
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
            $(this).parent('div').find('.custom-file-label').html(messages.choosefile);
            readURL(null, previewElement);
        }
    });
    $('#audio, #video').change(function(e) {
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedImageMimeTypes = ['audio/mp3', 'audio/mpeg', 'audio/m4a', 'audio/x-m4a'],
                allowedVideoMimeTypes = ['video/mp4'],
                lessonType = $('#lesson_type').val();
            if (lessonType == 2 && !allowedVideoMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.video_valid_error);
                $(e.currentTarget).empty().val('');
            } else if (lessonType == 1 && !allowedImageMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.meditation_track_audio_valid_error);
                $(e.currentTarget).empty().val('');
            } else if (e.target.files[0].size > 104857600) {
                if (lessonType == 2) {
                    toastr.error(messages.video_size_100M_error);
                } else {
                    toastr.error(messages.audio_size_100M_error);
                }
                $(e.currentTarget).empty().val('');
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
            $('#duration').val('');
        }
    });

    $('#courseLessionEdit').ajaxForm({
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html(messages.uploading_media);
            $('.progress-loader-wrapper').show();
            $('#courseLessionEdit .card-footer button, #courseLessionEdit .card-footer a').attr('disabled', 'disabled');
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
            $('#courseLessionEdit .card-footer button, #courseLessionEdit .card-footer a').removeAttr('disabled');
            var percentVal = '100%';
            bar.width(percentVal)
            percent.html(percentVal);
            if (data.status && data.status == 1) {
                window.location.replace(data.url);
            } else {
                if (data.message && data.message != '') {
                    toastr.error(data.message);
                }
            }
        },
        error: function(data) {
            $('.progress-loader-wrapper').hide();
            $('#courseLessionEdit .card-footer button, #courseLessionEdit .card-footer a').removeAttr('disabled');
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
            $('#courseLessionEdit .card-footer button, #courseLessionEdit .card-footer a').removeAttr('disabled');
        }
    });
});


$(document).on('click', '#zevo_submit_btn', function() {
    if(_lesson_type == '4') {
        content = contentCkeditor.getData();
        description = $(content).text().trim();
        contentCkeditor.setData(content);
        contentCkeditor.destroy();
        loadCkeditor();
        if (description == '') {
            $('#zevo_submit_btn').removeAttr('disabled');
            event.preventDefault();
            $('#description-error-cstm').html("The description field is required").addClass('invalid-feedback').show();
        } else {
            $('#description-error-cstm').removeClass('invalid-feedback').hide();
        }
        return true;
    }
});
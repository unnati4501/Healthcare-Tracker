$(document).ready(function(){
    $('input[type="file"]').change(function (e) {
        var fileName = e.target.files[0].name;
        if (fileName.length > 40) {
            fileName = fileName.substr(0, 40);
        }
        var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedMimeTypes.includes(e.target.files[0].type)) {
            toastr.error(message.image_valid_error);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else if (e.target.files[0].size > 2097152) {
            toastr.error(message.image_size_2M_error);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else {
            $(this).parent('div').find('.custom-file-label').html(fileName);
        }
    });

    //--------- preview image
    function readURL(input, selector) {
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
                        $(input).parent('div').find('.custom-file-label').html('Choose File');
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
    };

    $("#logo").change(function () {
        var id = '#previewImg';
        readURL(this, id);
    });

    $("#schedule_date_time").keypress(function(event) {
        event.preventDefault();
    });

    $('#schedule_date_time').bind('paste',function(e) {
        e.preventDefault();
    });

    var start = new Date();
    var end = new Date(new Date().setYear(start.getFullYear() + 1));

    var descriptionEditor = tinymce.init({
        selector: "#message",
        branding: false,
        menubar:false,
        statusbar: false,
        plugins: "code,link,lists,advlist",
        toolbar: 'formatselect | bold italic forecolor backcolor permanentpen formatpainter alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | code | link',
        forced_root_block : true,
        paste_as_text : true,
        setup: function (editor) {
            editor.on('change redo undo', function () {
                tinymce.triggerSave();
                var editor = tinymce.get('message');
                var contentLength = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim().length;
                $('#message-error').hide();
                $('#message-max-error').hide();
                $('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
                if (contentLength == 0) {
                    event.preventDefault();
                    $('#message-error').show();
                    $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                } else if(contentLength > 5000) {
                    event.preventDefault();
                    $('#message-max-error').show();
                    $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                }
            });
        }
    });

    $("#schedule_date_time").datetimepicker({
        format: 'yyyy-mm-dd hh:ii:00',
        startDate: start,
        endDate: end,
        autoclose: true,
        fontAwesome:true,
        todayHighlight: true
    });

    $("#members").treeMultiselect({
        enableSelectAll: true,
        searchable: true,
        startCollapsed: true,
        onChange: function (allSelectedItems, addedItems, removedItems) {
            var selectedMembers = $('#members').val().length;
            if (selectedMembers == 0) {
                $('#notificationAdd').valid();
                $('#members-required-error').hide();
                $('#members-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#members-error').hide();
                $('#members-required-error').hide();
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
    $(document).on('click','#zevo_submit_btn',function(){
        formSubmit();
    });
});
function formSubmit() {
    var selectedMembers = $('#members').val().length;
    if (selectedMembers == 0) {
        event.preventDefault();
        $('#notificationAdd').valid();
        $('#members-required-error').hide();
        $('#members-error').show();
        $('.tree-multiselect').css('border-color', '#f44436');
    } else {
        $('#members-error').hide();
        $('#members-required-error').hide();
        $('.tree-multiselect').css('border-color', '#D8D8D8');
    }

    var editor = tinymce.get('message'),
        content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim(),
        contentLength = content.length;
    if (contentLength == 0) {
        event.preventDefault();
        $('#notificationAdd').valid();
        $('#message-max-error').hide();
        $('#message-error').show();
        $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
    } else if(contentLength > 5000) {
        event.preventDefault();
        $('#recipeAdd').valid();
        $('#message-error').hide();
        $('#message-max-error').show();
        $('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
    } else {
        // editor.setContent(content);
        $('#message-error').hide();
        $('#message-max-error').hide();
        $('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
    }
}
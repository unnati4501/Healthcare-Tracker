var ingdCount = 1;

function readURL(input, previewElement) {
    if (input && input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            // Validation for image max height / width and Aspected Ratio
            var image = new Image();
            image.src = e.target.result;
            image.onload = function() {
                var imageWidth = $(input).data('width');
                var imageHeight = $(input).data('height');
                var ratio = $(input).data('ratio');
                var aspectedRatio = ratio;
                var ratioSplit = ratio.split(':');
                var newWidth = ratioSplit[0];
                var newHeight = ratioSplit[1];
                var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                if ((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio) {
                    $(input).empty().val('');
                    $(input).parent('div').find('.custom-file-label').html('Choose File');
                    $(previewElement).removeAttr('src');
                    toastr.error(upload_image_dimension);
                    readURL(null, previewElement);
                }
            }
            $(previewElement).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        $(previewElement).removeAttr('src');
    }
};
$(document).ready(function() {
    $('input[type=radio][name=type]').change(function() {
        if (this.value == 'to-do') {
            $('#showTaskAdd').show();
            $('#hideTaskAdd').hide();
        } else {
            $('#showTaskAdd').hide();
            $('#hideTaskAdd').show();
        }
    });
    $("#target_value").focusout(function() {
        $(this).val($.trim($(this).val()).replace(/^0+/, ''));
    });
    $('input[type="file"]').change(function(e) {
        var fileName = e.target.files[0].name;
        if (fileName.length > 40) {
            fileName = fileName.substr(0, 40);
        }
        var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedMimeTypes.includes(e.target.files[0].type)) {
            toastr.error(image_valid_error);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else if (e.target.files[0].size > 2097152) {
            toastr.error(image_size_2M_error);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else {
            $(this).parent('div').find('.custom-file-label').html(fileName);
        }
    });
    $("#logo").change(function() {
        var id = '#previewImg';
        readURL(this, id);
    });
    $.validator.addMethod("single_task_required", $.validator.methods.required, 'The task field is required.');
    $.validator.addMethod("ingredients_required", $.validator.methods.required, 'The tasks field is required.');
    $.validator.addClassRules("ingredients_required", {
        ingredients_required: true,
        maxlength: 50
    });
    $.validator.addClassRules("single_task_required", {
        single_task_required: true,
        maxlength: 50
    });
    $(document).on('click', '#ingriadiantAdd', function() {
        $(this).hide();
        var template = $('#ingredientsTemplate').text().trim();
        template = template.replace(':ingdCount', ingdCount);
        ingdCount++;
        if ($('#ingriadiantTbl tbody tr').length <= 14) {
            $("#ingriadiantTbl tbody").append(template);
            if ($('#ingriadiantTbl tbody tr').length == 15) {
                $('#ingriadiantTbl tbody tr:last td:last').find('#ingriadiantAdd').hide();
            }
        }
    });
    $(document).on('keyup', '#ingriadiantTbl tbody tr:last input', function(e) {
        if ($('#ingriadiantTbl tbody tr').length > 1) {
            $(this).parent().next().toggleClass("show_del", $(this).val().length == 0);
        }
    });
    $(document).on('click', ".ingriadiant-remove", function(e) {
        e.preventDefault();
        $($(this).closest("tr")).remove();
        if ($('#ingriadiantTbl tbody tr').length == 1) {
            $('#ingriadiantTbl tbody tr:last td:last').removeClass('show_del');
        }
        $('#ingriadiantTbl tbody tr:last td:last').find('#ingriadiantAdd').show();
    });
    $(document).on('click', '.challengetype', function(e) {
        var id = $(this).attr('id');
        $('.challengetypeTabs').hide();
        if (id == 'routine') {
            $('#hideTaskAdd').hide();
            $('#showTaskAdd').show();
            $('#streak').parent().show();
            $('#steps').prop('checked', false);
            $('#todo').click();
        } else if (id == 'challenge') {
            $('#todo').prop('checked', false);
            $('#steps').click();
        } else if (id == 'habit') {
            $('#steps').prop('checked', false);
            $('#todo').click();
        }
        if (id == 'habit') {
            $('#isRecursive').hide();
            $('#showTaskAdd').hide();
            $('#hideTaskAdd').show();
            $('#streak').parent().hide();
            $('.routine').show();
        } else {
            $('#isRecursive').show();
            $('.' + id).show();
        }
    });
    $(document).on('click', '.fitnesstype', function(e) {
        var id = $(this).attr('id');
        $('.target-value-type').hide();
        switch (id) {
            case 'distance':
                $('#meter').show()
                break;
            case 'meditations':
                $('#minutes').show()
                break;
            default:
                $('#counts').show()
        }
    });
});
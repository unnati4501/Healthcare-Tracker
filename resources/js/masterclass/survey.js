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
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    if (queCount > 1) {
        $('#surveyEdit').validate().settings.ignore = ".ignore-validation";
        $('.qus-option-table').each(function(index, el) {
            if ($(el).find('tr').length > 1) {
                $(el).find('tr:last td:last').removeClass('show_del');
            }
        });
    }
    $('.select2').select2({
        width: '100%'
    });
    $(document).on('change', '.question_logo_tag', function(e) {
        var previewElement = $(this).data('previewelement');
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            $('.toast').remove()
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
    $(document).on('click', '.add_question', function(e) {
        var questions_count = $('.questions-block .single-question-block').length;
        if (questions_count < 5) {
            queCount++;
            var template = $('#survey_questions_temp').text().trim();
            template = template.replace(/\:id/g, queCount);
            $(".questions-block").append(template);
            $(".questions-block .single-question-block:last .select2").select2({
                width: '100%'
            });
        } else {
            $('.toast').remove();
            toastr.error(messages.max_options);
        }
    });
    $(document).on('click', '.remove-question', function(e) {
        var questions_count = $('.questions-block .single-question-block').length,
            _id = $(this).data('id');
        if (questions_count > 1) {
            $('#remove-survey-question-model-box').data('id', _id);
            $('#remove-survey-question-model-box').modal('show');
        } else {
            $('.toast').remove()
            toastr.error(messages.min_questions);
        }
    });
    $(document).on('click', '#remove-survey-question-model-box-confirm', function(e) {
        var _id = $('#remove-survey-question-model-box').data('id');
        if ($(`.single-question-block[data-id="${_id}"]`).length > 0) {
            $('#remove-survey-question-model-box').modal('hide');
            if ($(`.single-question-block[data-id="${_id}"]`).hasClass('old-added')) {
                _deleted_questions.push(_id);
                $('input[name="deleted_questions"]').val(_deleted_questions.toString());
            }
            $(`.single-question-block[data-id="${_id}"]`).remove();
        }
    });
    $(document).on('hidden.bs.modal', '#remove-survey-question-model-box', function(e) {
        $('#remove-survey-question-model-box').data('id', 0);
    });
    $(document).on('click', '.add-option', function(e) {
        if ($(this).parents('tbody').find('tr').length < 7) {
            var questionId = $(this).data('qid'),
                template = $('#survey_option_temp').text().trim();
            optionCount++;
            template = template.replace(/\:id/g, questionId);
            template = template.replace(/\:oid/g, optionCount);
            $(this).parents("tbody").append(template);
            $(this).parents("tbody").find('tr:last').find('.select2').select2({
                width: '100%'
            });
        } else {
            $('.toast').remove()
            toastr.error(messages.max_questions);
        }
    });
    $(document).on('click', ".remove-option", function(e) {
        e.preventDefault();
        var _id = $(this).data('qid'),
            _oid = $(this).data('oid');
        if ($(this).hasClass('old-added')) {
            _deleted_options.push(_oid);
            $('input[name="deleted_options"]').val(_deleted_options.toString());
        }
        $($(this).closest("tr")).remove();
        if ($(`.question-option-${_id} tbody tr`).length == 1) {
            $(`.question-option-${_id} tbody tr td:last`).removeClass('show_del');
        }
    });
});
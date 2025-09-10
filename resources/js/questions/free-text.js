// Document ready START;
$(function() {
    //------------------------- Input Preview Image -------------------------//
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
                        $(`[data-id-ref="previewImg[${selector}]"]`).attr('src', defaultQuestionImg);
                        toastr.error(upload_image_dimension);
                        readURL(null, selector);
                    } else {
                        $(`[data-id-ref="previewImg[${selector}]"]`).attr('src', e.target.result);
                        $(`[name="question_image-free-text_src[${selector}]"]`).val(e.target.result);
                    }
                }
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(`[data-id-ref="previewImg[${selector}]"]`).attr('src', defaultQuestionImg);
            $(`[name="question_image-free-text_src[${selector}]"]`).val('');
        }
    }
    $(".image-selector").change(function(e) {
        var id = $(this).data('previewelement');
        if (!e.target.files[0]) {
            return;
        }
        var fileName = e.target.files[0].name;
        if (fileName.length > 40) {
            fileName = fileName.substr(0, 40);
        }
        var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedMimeTypes.includes(e.target.files[0].type)) {
            $(this).parent().addClass('is-invalid');
            toastr.error("Please try again with uploading valid image.");
            $(e.currentTarget).empty().val('');
            $(`[name="question_image-free-text_src[${id}]"]`).val('');
        } else if (e.target.files[0].size > 2097152) {
            $(this).parent().addClass('is-invalid');
            toastr.error("Maximum allowed size for uploading image is 2 mb. Please try again.");
            $(e.currentTarget).empty().val('');
            $(`[name="question_image-free-text_src[${id}]"]`).val('');
        } else {
            $(this).parent().removeClass('is-invalid');
            readURL(this, id);
        }
    });
    //------------------------- ./Input Preview Image -------------------------//
    // Delete single question.
    $('body').on('click', '.question-delete-free-text', function() {
        var questionSelector = $(this).closest('.question-wrap');
        var totalQuestionInSurveyForm = $(".survey-form-free-text").find('.question-wrap');
        if (totalQuestionInSurveyForm.length == 1) {
            // toastr.error("Question has been delete");
        } else {
            questionSelector.remove();
            toastr.error(singleDeleteMessage);
        }
    });
    // Delete all question .
    $('body').on('click', '.question-delete-all-free-text', function() {
        $('.delete-all-question-model-free-text').modal('show');
    });
    // Delete all question confirm.
    $('body').on('click', '.question-delete-all-confirm-free-text', function() {
        $(".survey-form-free-text").find('.question-wrap').each(function(e) {
            if ($(".survey-form-free-text").find('.question-wrap').length == 1) {
                isLast = true;
                $newItem = $(".survey-form-free-text").find('.question-wrap');
                // Question id increment logic
                $newItem.find('.data-id').text('Q1').attr('data-id', 1);
                $newItem.find(':input').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        var name = $(this).attr('name');
                        var id = $(this).attr('id');
                        // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
                        $(this).attr({
                            'name': name,
                            'id': id
                        }).val('').removeAttr('checked');
                    }
                });
                // Reset image src
                $newItem.find('img').each(function() {
                    var id = $(this).attr('id');
                    var src = APPURL + '/assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png';
                    if (id) {
                        $(this).attr({
                            'src': src
                        }).val('');
                    }
                });
                $newItem.find('.image-selection-free-text').each(function() {
                    var name = $(this).attr('data-input-ref');
                    if (name) {
                        var name = $(this).attr('data-input-ref');
                        // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
                        $(this).attr({
                            'data-input-ref': name
                        }).val('').removeAttr('checked');
                    }
                });
                showToastr = false;
                $newItem.find('.imageNotice').html(null);
            } else {
                this.remove();
                showToastr = true;
            }
        });
        toastr.error(multiDeleteMessage);
        $('.delete-all-question-model-free-text').modal('hide');
    });
    // Preview single question
    $('body').on('click', '.preview-button-free-text', function() {
        var questionSelector = $(this).closest('.question-wrap');
        var activeQuestion = questionSelector.attr('data-order');
        $('#userQuestionFreeText').html(null);
        var freeTextData = $('.survey-form-free-text').serializeJSON();
        if (freeTextData) {
            var i = activeQuestion;
            var id = freeTextData.question[activeQuestion];
            var dynamicHtml = '';
            if (freeTextData["question_image-free-text_src"][i] && freeTextData["question_image-free-text_src"][i].includes('73a90acaae2b1ccc0e969709665bc62f')) {
                freeTextData["question_image-free-text_src"][i] = '';
            }
            if (freeTextData["question_image-free-text_src"][i] != '') {
                dynamicHtml = '<div class="col-lg-4 order-lg-last">\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-lg-0 m-b-30" src="' + freeTextData["question_image-free-text_src"][i] + '" alt="">\n' + '</div>\n' + '</div>\n' + '<div class="col-lg-8 order-lg-first p-r-lg-30 align-self-center">\n';
            } else {
                dynamicHtml = '<div class="col-lg-12 align-self-center">';
            }
            var questionText = freeTextData["question"][i];
            if(/<\/?[^>]+(>|$)/g.test(questionText)) {
                questionText = '';
            }
            var htmlElements = '<div class="row align-items-center"><div class="col-lg-12 align-self-center text-center">\n' + '<div class="ans-main-area question-type-one m-0-a">\n' + '<div class="text-center">\n' + '<h2 class="question-text">' + questionText + '</h2></div>\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + freeTextData["question_image-free-text_src"][i] + '" alt="">\n' + '</div>\n' + '<div class="form-group ans-textarea">\n' + '<textarea class="form-control animated flash slow h-auto" id="" rows="5"></textarea>\n' + '</div>\n' + '</div>\n' + '</div>\n' + '</div>';
            $('#userQuestionFreeText').append(htmlElements);
        }
        $('#userQuestionFreeTextModelBox').modal('show');
    });
    // Preview all question .
    $('.preview-all-button-free-text').click(function() {
        $('#userQuestionFreeTextAll').html(null);
        var freeTextData = $('.survey-form-free-text').serializeJSON();
        if (freeTextData) {
            var count = 1;
            var dynamicHtml = '';
            var prefixStart = '<h3><span></span></h3><section class="step-box"><div class="row align-items-center">';
            var prefixEnd = '</div></section>';
            var totalQs = Object.keys(freeTextData.question).length;
            for (var i in freeTextData.question) {
                substring = "73a90acaae2b1ccc0e969709665b62f";
                if (freeTextData["question_image-free-text_src"][i] && freeTextData["question_image-free-text_src"][i].includes(substring)) {
                    freeTextData["question_image-free-text_src"][i] = '';
                }
                if (freeTextData["question_image-free-text_src"][i] != '') {
                    dynamicHtml = prefixStart + '<div class="col-lg-4 order-lg-last">\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-lg-0 m-b-30" src="' + freeTextData["question_image-free-text_src"][i] + '" alt="">\n' + '</div>\n' + '</div>' + '<div class="col-lg-8 order-lg-first p-r-lg-30 align-self-center">\n';
                } else {
                    dynamicHtml = prefixStart + '<div class="col-lg-12 align-self-center">';
                }
                var htmlElements = prefixStart + '<div class="col-lg-12 align-self-center text-center">\n' + '<div class="ans-main-area question-type-one m-0-a">\n' + '<div class="text-center">\n<p class="question-text-title">Question (' + count + ' of ' + totalQs + ')</p>\n' + '<h2 class="question-text">' + freeTextData["question"][i] + '</h2></div>\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + freeTextData["question_image-free-text_src"][i] + '" alt="">\n' + '</div>\n' + '<div class="form-group ans-textarea">\n' + '<textarea class="form-control animated flash slow" id="" rows="5"></textarea>\n' + '</div>\n' + '</div>\n' + '</div>' + prefixEnd;
                $('#userQuestionFreeTextAll').append(htmlElements);
                count++;
            }
        }
        // ------------------------- Steps ------------------------- //
        // http://www.jquery-steps.com/Examples#basic
        // https://github.com/rstaib/jquery-steps/wiki/Settings
        $("#userQuestionFreeTextAll").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "fade",
            // slideLeft
            autoFocus: true,
            onFinished: function(event, currentIndex) {
                $('#userQuestionFreeTextModelBoxdAll').modal('hide');
            },
        });
        $('#userQuestionFreeTextModelBoxdAll').modal('show');
    });
});
// Document ready END;
$('#addAnotherQuestionFreeText').on('click', function() {
    var totalQuestionInSurveyForm = $(".survey-form-free-text").find('.question-wrap');
    if (totalQuestionInSurveyForm.length >= 5) {
        $('.toast').remove();
        toastr.warning('Five questions have been added, not allowed to add more.');
        // Prevent from adding more question.
        return;
    }
    // Get previous form value
    var currentFormId = $('#total-form-free-text').val();
    // Increase form value for next iteration.
    currentFormId++;
    // var previousFormId = currentFormId - 1;
    // Get last question html source
    var $lastItem = $('.survey-form-free-text .question-wrap').last();
    var previousFormId = $lastItem.attr('data-order');
    // Create new clone from lastItem
    var $newItem = $lastItem.clone(true);
    // Insert clone html after last question html
    $newItem.insertAfter($lastItem);
    // Question id increment logic
    // Question id increment logic
    var previousDataId = $lastItem.find('.data-id').attr('data-id');
    var questionSequence = parseInt(previousDataId) + 1;
    $newItem.find('.data-id').text('Q' + questionSequence).attr('data-id', questionSequence);
    // Replace id and name element with currentFormId
    $newItem.find(':input').each(function() {
        var name = $(this).attr('name');
        if (name) {
            var name = $(this).attr('name').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            var id = $(this).attr('id').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
            $(this).attr({
                'name': name,
                'id': id,
                'data-previewelement': currentFormId,
                'aria-describedby': 'question[' + currentFormId + ']-error'
            }).data('previewelement', currentFormId).val('').removeAttr('checked');
        }
    });
    $newItem.find('label').each(function() {
        $(this).attr('for', 'question_image-free-text[' + currentFormId + ']');
    });
    // Reset image src
    $newItem.find('img').each(function() {
        var id = $(this).attr('id');
        var src = APPURL + '/assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png';
        if (id) {
            var id = $(this).attr('id').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            $(this).attr({
                'src': src,
                'id': id,
                'data-id-ref': 'previewImg[' + currentFormId + ']'
            }).data('id-ref', 'previewImg[' + currentFormId + ']').val('');
        }
    });
    $newItem.find('.image-selection-free-text').each(function() {
        var name = $(this).attr('data-input-ref');
        if (name) {
            var name = $(this).attr('data-input-ref').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
            $(this).attr({
                'data-input-ref': name
            }).val('').removeAttr('checked');
        }
    });
    // $newItem.find('.invalid-feedback').each(function() {
    //     var id = $(this).attr('id');
    //     if (id) {
    //         var id = $(this).attr('id').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
    //         $(this).attr({
    //             'id': id,
    //         }).html(null);
    //     }
    // });
    // This is used for identify current raw of question.
    $newItem.closest('.question-wrap').attr('data-order', currentFormId);
    // For image selection sometime
    $newItem.find('.imageNotice').html(null);
    $newItem.find('.invalid-feedback').remove();
    // replace currentFormId to input value
    $('#total-form-free-text').val(currentFormId);
    toastr.success(singleAddMessage);
});
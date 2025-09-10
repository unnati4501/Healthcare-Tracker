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
                        $(`[data-id-ref="previewImg2[${selector}]"]`).attr('src', e.target.result);
                        $(`[name="question_image-choice_src[${selector}]"]`).val(e.target.result);
                    }
                }
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(`[data-id-ref="previewImg2[${selector}]"]`).attr('src', defaultQuestionImg);
            $(`[name="question_image-choice_src[${selector}]"]`).val('');
        }
    }
    $(".image-selector-choice").change(function(e) {
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
            $(`[name="question_image-choice_src[${id}]"]`).val('');
        } else if (e.target.files[0].size > 2097152) {
            $(this).parent().addClass('is-invalid');
            toastr.error("Maximum allowed size for uploading image is 2 mb. Please try again.");
            $(e.currentTarget).empty().val('');
            $(`[name="question_image-choice_src[${id}]"]`).val('');
        } else {
            $(this).parent().removeClass('is-invalid');
            readURL(this, id);
        }
    });

    function readChoiceOptionURL(input, question, choice) {
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
                        $(`[data-id-ref="previewImg3[${question}][${choice}]"]`).attr('src', defaultQuestionImg);
                        // $(`[data-id-ref="previewImg3[${question}][${choice}]"]`).removeAttr('src');
                        toastr.error(upload_image_dimension);
                        readChoiceOptionURL(null, question, choice);
                    } else {
                        $(`[data-id-ref="previewImg3[${question}][${choice}]"]`).attr('src', e.target.result);
                        $(`[name="image[${question}][${choice}][imageSrc]"]`).val(e.target.result);
                    }
                }
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(`[data-id-ref="previewImg3[${question}][${choice}]"]`).attr('src', defaultQuestionImg);
            $(`[name="image[${question}][${choice}][imageSrc]"]`).val('');
        }
    }
    $(".image-selector-choice-option").change(function(e) {
        var currentQuestion = $(this).closest('.question-wrap');
        var question = currentQuestion.attr('data-order');
        var choice = $(this).attr('data-previewoptionelement');
        if (!e.target.files[0]) {
            return;
        }
        var fileName = e.target.files[0].name;
        if (fileName.length > 40) {
            fileName = fileName.substr(0, 40);
        }
        var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedMimeTypes.includes(e.target.files[0].type)) {
            toastr.error("Please try again with uploading valid image.");
            $(e.currentTarget).empty().val('');
            $(`[name="image[${question}][${choice}][imageSrc]"]`).val('');
        } else if (e.target.files[0].size > 2097152) {
            toastr.error("Maximum allowed size for uploading image is 2 mb. Please try again.");
            $(e.currentTarget).empty().val('');
            $(`[name="image[${question}][${choice}][imageSrc]"]`).val('');
        } else {
            readChoiceOptionURL(this, question, choice);
        }
    });
    //------------------------- ./Input Preview Image -------------------------//
    // Delete single question. <- DONE
    $('body').on('click', '.question-delete-choice', function() {
        var questionSelector = $(this).closest('.question-wrap');
        var totalQuestionInSurveyForm = $(".survey-form-choice").find('.question-wrap');
        if (totalQuestionInSurveyForm.length == 1) {
            // toastr.error("Question has been delete");
        } else {
            questionSelector.remove();
            toastr.error(singleDeleteMessage);
        }
    });
    // Delete all question .
    $('body').on('click', '.question-delete-all-choice', function() {
        $('.delete-all-question-model-choice').modal('show');
    });
    // Delete all question confirm.
    $('body').on('click', '.question-delete-all-confirm-choice', function() {
        $(".survey-form-choice").find('.question-wrap').each(function(e) {
            if ($(".survey-form-choice").find('.question-wrap').length == 1) {
                isLast = true;
                $('#total-form-choice').val(1);
                $newItem = $(".survey-form-choice").find('.question-wrap');
                // Question id increment logic
                $newItem.attr('data-id', 1);
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
                        regex = new RegExp(/score\[\d{1,2}\]/);
                        if (regex.test(name)) {
                            // Reset to value = 0
                            $('[id="' + id + '"]').val("");
                        }
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
                $newItem.find('.image-selection-choice').each(function() {
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
        $('.delete-all-question-model-choice').modal('hide');
    });
    // Preview single question
    $('body').on('click', '.preview-button-choice', function() {
        var questionSelector = $(this).closest('.question-wrap');
        var activeQuestion = questionSelector.attr('data-order');
        $('#singleQuestionPreviewModalChoiceHtml').html(null);
        var choiceData = $('.survey-form-choice').serializeJSON();
        if (choiceData) {
            var dynamicHtml = '';
            var dynamicSwithClassData = '';
            var innerCount = 1;
            for (var i in choiceData.image[activeQuestion]) {
                if (choiceData.image[activeQuestion][i]["imageSrc"] && choiceData.image[activeQuestion][i]["imageSrc"].includes('73a90acaae2b1ccc0e969709665bc62f')) {
                    choiceData.image[activeQuestion][i]["imageSrc"] = '';
                }
                if (choiceData.image[activeQuestion][i]["imageSrc"] != '' && choiceData.image[activeQuestion][i]["imageSrc"] != undefined) {
                    var imageLink = choiceData.image[activeQuestion][i]["imageSrc"];
                } else {
                    var imageLink = `/assets/dist/img/choice-${innerCount}.png`;
                }
                innerCount++;
                if (choiceData.choice[activeQuestion][i] != '' && choiceData.choice[activeQuestion][i] != undefined) {
                    var imageTitle = choiceData.choice[activeQuestion][i];
                } else {
                    var imageTitle = '';
                }
                dynamicHtml += '<!-- item-box -->' + '<label class="choices-item-box">\n' + '    <input type="radio" name="choices">\n' + '    <div class="markarea">\n' + '        <span class="checkmark animated tada faste"></span>\n' + '        <div class="choices-item-img">\n' + '            <img class="" src="' + imageLink + '" alt="">\n' + '        </div>\n' + '    </div>\n' + '    <div class="choices-box-title">' + imageTitle + '</div>\n' + '</label>\n' + '<!-- item-box /-->';
            }
            if (choiceData["question_image-choice_src"][activeQuestion] && choiceData["question_image-choice_src"][activeQuestion].includes('73a90acaae2b1ccc0e969709665bc62f')) {
                choiceData["question_image-choice_src"][activeQuestion] = '';
            }
            if (choiceData["question_image-choice_src"][activeQuestion] && choiceData["question_image-choice_src"][activeQuestion][i] != '') {
                dynamicSwithClassData = '<div class="col-lg-4 order-lg-last">\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-lg-0 m-b-30" src="' + choiceData["question_image-choice_src"][activeQuestion] + '" alt="">\n' + '</div>\n' + '</div>\n' + '<div class="col-lg-8 text-center align-self-center">';
            } else {
                dynamicSwithClassData = '<div class="col-lg-12 text-center align-self-center">';
            }
            var singlePreviewHtml = '<div class="row align-items-center"><div class="col-lg-12 align-self-center text-center">' + '    <div class="ans-main-area question-type-one m-0-a">\n' + ' <div class="text-center">\n' + '        <h2 class="question-text">' + choiceData.question[activeQuestion] + '</h2></div>\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + choiceData["question_image-choice_src"][activeQuestion] + '" alt="">\n' + '</div>\n' + '        <div class="animated flash slow choices-main-box">\n' + dynamicHtml + '        </div>\n' + '    </div>\n' + '</div>\n' + '</div>';
            $('#singleQuestionPreviewModalChoiceHtml').append(singlePreviewHtml);
        }
        $('#singleQuestionPreviewModalChoice').modal('show');
    });
    // Preview all question .
    $('.preview-all-button-choice').click(function() {
        $('#allQuestionPreviewModalChoiceHtml').html(null);
        var choiceData = $('.survey-form-choice').serializeJSON();
        if (choiceData) {
            var count = 1;
            var totalQs = Object.keys(choiceData.question).length;
            for (var activeQuestion in choiceData.question) {
                var dynamicHtml = '';
                var dynamicSwithClassData = '';
                var singlePreviewHtml = '';
                var innerCount = 1;
                for (var i in choiceData.image[activeQuestion]) {
                    substring = "73a90acaae2b1ccc0e969709665b62f";
                    if (choiceData.image[activeQuestion][i]["imageSrc"] && choiceData.image[activeQuestion][i]["imageSrc"].includes(substring)) {
                        choiceData.image[activeQuestion][i]["imageSrc"] = '';
                    }
                    if (choiceData.image[activeQuestion][i]["imageSrc"] != '' && choiceData.image[activeQuestion][i]["imageSrc"] != undefined) {
                        var imageLink = choiceData.image[activeQuestion][i]["imageSrc"];
                    } else {
                        var imageLink = `/assets/dist/img/choice-${innerCount}.png`;
                    }
                    innerCount++;
                    if (choiceData.choice[activeQuestion][i] != '' && choiceData.choice[activeQuestion][i] != undefined) {
                        var imageTitle = choiceData.choice[activeQuestion][i];
                    } else {
                        var imageTitle = '';
                    }
                    if (choiceData["question_image-choice_src"][i] != '') {
                        dynamicSwithClassData = '<div class="col-lg-4 order-lg-last">\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-lg-0 m-b-30" src="' + choiceData["question_image-choice_src"][activeQuestion] + '" alt="">\n' + '</div>\n' + '</div>\n' + '<div class="col-lg-8 text-center align-self-center">';
                    } else {
                        dynamicSwithClassData = '<div class="col-lg-12 text-center align-self-center">';
                    }
                    dynamicHtml += '<!-- item-box -->' + '<label class="choices-item-box">\n' + '    <input type="radio" name="choices">\n' + '    <div class="markarea">\n' + '        <span class="checkmark animated tada faste"></span>\n' + '        <div class="choices-item-img">\n' + '            <img class="" src="' + imageLink + '" alt="">\n' + '        </div>\n' + '    </div>\n' + '    <div class="choices-box-title">' + imageTitle + '</div>\n' + '</label>\n' + '<!-- item-box /-->';
                }
                singlePreviewHtml = '<h3><span></span></h3><section class="step-box"><div class="row align-items-center"><div class="col-lg-12 align-self-center text-center">\n' + '    <div class="ans-main-area question-type-one m-0-a">\n' + '  <div class="text-center">\n<p class="question-text-title">Question (' + count + ' of ' + totalQs + ')</p>\n' + '        <h2 class="question-text">' + choiceData.question[activeQuestion] + '</h2></div>\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + choiceData["question_image-choice_src"][activeQuestion] + '" alt="">\n' + '</div>\n' + '        <div class="animated flash slow choices-main-box">\n' + dynamicHtml + '        </div>\n' + '    </div>\n' + '</div>\n' + '</div></section>';
                $('#allQuestionPreviewModalChoiceHtml').append(singlePreviewHtml);
                count++;
            }
        }
        // ------------------------- Steps ------------------------- //
        // http://www.jquery-steps.com/Examples#basic
        // https://github.com/rstaib/jquery-steps/wiki/Settings
        $("#allQuestionPreviewModalChoiceHtml").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "fade",
            // slideLeft
            autoFocus: true,
            onFinished: function(event, currentIndex) {
                $('#allQuestionPreviewModalChoice').modal('hide');
            },
        });
        $('#allQuestionPreviewModalChoice').modal('show');
    });
});
// Document ready END;
$('#addAnotherQuestionChoice').on('click', function() {
    var totalQuestionInSurveyForm = $(".survey-form-choice").find('.question-wrap');
    if (totalQuestionInSurveyForm.length >= 5) {
        $('.toast').remove();
        toastr.warning('Five questions have been added, not allowed to add more.');
        // Prevent from adding more question.
        return;
    }
    // Get previous form value
    var currentFormId = $('#total-form-choice').val();
    // Increase form value for next iteration.
    currentFormId++;
    // var previousFormId = currentFormId - 1;
    // Get last question html source
    var $lastItem = $('.survey-form-choice .question-wrap').last();
    var previousFormId = $lastItem.attr('data-order');
    // Create new clone from lastItem
    var $newItem = $lastItem.clone(true);
    // Insert clone html after last question html
    $newItem.insertAfter($lastItem);
    // Question id increment logic
    var previousDataId = $lastItem.find('.data-id').attr('data-id');
    var questionSequence = parseInt(previousDataId) + 1;
    $newItem.find('.data-id').text('Q' + questionSequence).attr('data-id', questionSequence);
    // Replace id and name element with currentFormId
    $newItem.find('.qus-inline-choice :input').each(function() {
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
            regex = new RegExp(/score\[\d{1,2}\]/);
            if (regex.test(name)) {
                // Reset to value = 0
                $('[id="' + id + '"]').val('');
            }
        }
    });
    $newItem.find('.qus-inline-choice label').each(function() {
        $(this).attr('for', 'question_image-choice[' + currentFormId + ']');
    });
    // Reset image src
    $newItem.find('.qus-inline-choice img').each(function() {
        var id = $(this).attr('id');
        var src = APPURL + '/assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png';
        if (id) {
            var id = $(this).attr('id').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            $(this).attr({
                'src': src,
                'id': id,
                'data-id-ref': 'previewImg2[' + currentFormId + ']'
            }).data('id-ref', 'previewImg2[' + currentFormId + ']').val('');
        }
    });
    $newItem.find('.qus-inline-choice .image-selection-choice').each(function() {
        var name = $(this).attr('data-input-ref');
        if (name) {
            var name = $(this).attr('data-input-ref').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
            $(this).attr({
                'data-input-ref': name
            }).val('').removeAttr('checked');
        }
    });
    $newItem.find('.qus-choices-option :input').each(function() {
        var currentChoice = $(this).closest('.qus-choices-option').attr('data-order');
        var name = $(this).attr('name');
        if (name) {
            var name = $(this).attr('name').replace('[' + (previousFormId) + '][' + (currentChoice) + ']', '[' + (currentFormId) + '][' + (currentChoice) + ']');
            var id = $(this).attr('id').replace('[' + (previousFormId) + '][' + (currentChoice) + ']', '[' + (currentFormId) + '][' + (currentChoice) + ']');
            // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
            $(this).attr({
                'name': name,
                'id': id,
                'data-previewoptionelement': currentChoice,
                'aria-describedby': 'choice[' + currentFormId + '][' + currentChoice + ']-error'
            }).val('').removeAttr('checked');
            regex = new RegExp(/score\[\d{1,2}\]/);
            if (regex.test(name)) {
                // Reset to value = 0
                $(this).removeClass('is-valid');
                // $('[id="'+id+'"]').val("");
            }
        }
    });
    $newItem.find('.qus-choices-option label').each(function() {
        var currentChoice = $(this).closest('.qus-choices-option').attr('data-order');
        $(this).attr('for', 'image[' + (currentFormId) + '][' + (currentChoice) + '][imageId]');
    });
    // Reset image src
    $newItem.find('.qus-choices-option img').each(function() {
        var currentChoice = $(this).closest('.qus-choices-option').attr('data-order');
        var id = $(this).attr('id');
        var src = APPURL + '/assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png';
        if (id) {
            var id = $(this).attr('id').replace('[' + (previousFormId) + '][' + (currentChoice) + ']', '[' + (currentFormId) + '][' + (currentChoice) + ']');
            $(this).attr({
                'src': src,
                'id': id,
                'data-id-ref': 'previewImg3[' + (currentFormId) + '][' + (currentChoice) + ']'
            }).data('id-ref', 'previewImg3[' + (currentFormId) + '][' + (currentChoice) + ']').val('');
            $(this).attr({
                'src': src
            }).val('');
        }
    });
    $newItem.find('.qus-choices-option .image-selection-choice').each(function() {
        var currentChoice = $(this).closest('.qus-choices-option').attr('data-order');
        var name = $(this).attr('data-input-ref');
        if (name) {
            // var name = $(this).attr('data-input-ref').replace('[' + (previousQuestionChoiceValue) + ']', '[' + currentQuestionChoiceValue + ']');
            var name = $(this).attr('data-input-ref').replace('[' + (previousFormId) + '][' + (currentChoice) + ']', '[' + (currentFormId) + '][' + (currentChoice) + ']');
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
    $('#total-form-choice').val(currentFormId);
    toastr.success(singleAddMessage);
});
$('.addAnotherChoice').on('click', function() {
    var currentQustion = $(this).parent();
    var dataOrder = currentQustion.attr('data-order');
    var totalChoiceInQuestion = currentQustion.find('.qus-choices-option');
    if (totalChoiceInQuestion.length >= 7) {
        $('.toast').remove();
        toastr.warning('Seven options have been added, not allowed to add more.');
        // Prevent from adding more question.
        return;
    }
    // Get last choice html source
    var $lastItem = totalChoiceInQuestion.last().parent();
    // Get previous choice value
    var currentQuestionChoiceValue = parseInt($lastItem.find('.qus-choices-option').attr('data-order')) + 1;
    var previousQuestionChoiceValue = $lastItem.find('.qus-choices-option').attr('data-order');
    // Create new clone from lastItem
    var $newItem = $lastItem.clone(true);
    // Insert clone html after last question html
    $newItem.insertAfter($lastItem);
    // Replace id and name element with currentFormId
    $newItem.find(':input').each(function() {
        var name = $(this).attr('name');
        if (name) {
            var name = $(this).attr('name').replace('[' + (dataOrder) + '][' + (previousQuestionChoiceValue) + ']', '[' + (dataOrder) + '][' + (currentQuestionChoiceValue) + ']');
            var id = $(this).attr('id').replace('[' + (dataOrder) + '][' + (previousQuestionChoiceValue) + ']', '[' + (dataOrder) + '][' + (currentQuestionChoiceValue) + ']');
            // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
            $(this).attr({
                'name': name,
                'id': id,
                'data-previewoptionelement': currentQuestionChoiceValue,
                'aria-describedby': 'choice[' + dataOrder + '][' + currentQuestionChoiceValue + ']-error'
            }).val('').removeAttr('checked');
            regex = new RegExp(/score\[\d{1,2}\]/);
            if (regex.test(name)) {
                // Reset to value = 0
                $(this).removeClass('is-valid');
                // $('[id="'+id+'"]').val("");
            }
        }
    });
    $newItem.find('label').each(function() {
        $(this).attr('for', 'image[' + (dataOrder) + '][' + (currentQuestionChoiceValue) + '][imageId]');
    });
    // Reset image src
    $newItem.find('img').each(function() {
        var id = $(this).attr('id');
        var src = APPURL + '/assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png';
        if (id) {
            var id = $(this).attr('id').replace('[' + (dataOrder) + '][' + (previousQuestionChoiceValue) + ']', '[' + (dataOrder) + '][' + (currentQuestionChoiceValue) + ']');
            $(this).attr({
                'src': src,
                'id': id,
                'data-id-ref': 'previewImg3[' + (dataOrder) + '][' + (currentQuestionChoiceValue) + ']'
            }).data('id-ref', 'previewImg3[' + (dataOrder) + '][' + (currentQuestionChoiceValue) + ']').val('');
            $(this).attr({
                'src': src
            }).val('');
        }
    });
    $newItem.find('.image-selection-choice').each(function() {
        var name = $(this).attr('data-input-ref');
        if (name) {
            // var name = $(this).attr('data-input-ref').replace('[' + (previousQuestionChoiceValue) + ']', '[' + currentQuestionChoiceValue + ']');
            var name = $(this).attr('data-input-ref').replace('[' + (dataOrder) + '][' + (previousQuestionChoiceValue) + ']', '[' + (dataOrder) + '][' + (currentQuestionChoiceValue) + ']');
            // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
            $(this).attr({
                'data-input-ref': name
            }).val('').removeAttr('checked');
        }
    });
    // $newItem.find('.invalid-feedback').each(function() {
    //     var id = $(this).attr('id');
    //     if (id) {
    //         var id = $(this).attr('id').replace('[' + (dataOrder) + '][' + (previousQuestionChoiceValue) + ']', '[' + (dataOrder) + '][' + (currentQuestionChoiceValue) + ']');
    //         $(this).attr({
    //             'id': id,
    //         }).html(null);
    //     }
    // });
    $newItem.find('.invalid-feedback').remove();
    // This is used for identify current raw of question. , replace currentQuestionChoiceValue to input value
    $newItem.find('.qus-choices-option').attr('data-order', currentQuestionChoiceValue);
    imageRequiredForOptions('#choice-question', dataOrder);
});
// Delete single question. <- DONE
$('.question-delete-choice-options').on('click', '', function() {
    var currentQuestion = $(this).closest('.question-wrap');
    var currentQuestionOption = $(this).closest('.qus-choices-option').parent();
    var totalQuestionOptions = $(currentQuestion).find('.qus-choices-option').parent();
    if (totalQuestionOptions.length == 2) {
        // toastr.error("Question has been delete");
    } else {
        currentQuestionOption.remove();
    }
    var questionRow = currentQuestion.attr('data-order');
    imageRequiredForOptions('#choice-question', questionRow);
});
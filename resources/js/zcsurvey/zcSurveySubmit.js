function updateProgrssbar(percentage) {
    var currValue = $('#horizontalProgressBar').attr('aria-valuenow');
    if (percentage > currValue) {
        $('#horizontalProgressBar').attr('aria-valuenow', percentage).css('width', `${percentage}%`).html(`${Math.floor(percentage)}%`);
        $('#verticalProgressBar').css('height', `${percentage}%`).html(`${Math.floor(percentage)}%`);
    }
}

function updateAnswer(sId, stId, aId) {
    _aary[stId] = aId;
    sessionStorage.setItem(sId, JSON.stringify(_aary));
}

function getAnswer(sId, stId) {
    var obj = sessionStorage.getItem(sId);
    if (obj) {
        _aary = JSON.parse(sessionStorage.getItem(sId));
        return _aary[stId];
    }
    return undefined;
}
$(document).ready(function() {
    if (sessionDataExsit != null) {
        sessionStorage.removeItem(surveyId);
    }
    $("#userQuestion").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "fade",
        loadingTemplate: `<div class="align-self-center"><i class="fa fa-spinner fa-spin"></i> #text#</div>`,
        labels: {
            loading: "Loading question..."
        },
        onContentLoaded: function(event, currentIndex) {
            var qType = $(`[data-step="${currentIndex}"]`).data('q-type'),
                qId = $(`[data-step="${currentIndex}"]`).data('q'),
                _aa = getAnswer(surveyId, currentIndex);
            if (_aa != undefined) {
                if (qType == "1") {
                    $(`[name="answers[${qId}]"]`).val(_aa);
                } else if (qType == "2") {
                    $(`.question-section[data-q='${qId}'] .choices-main-box .choices-item-box:eq(${_aa}) input[type="radio"]`).prop('checked', true);
                    $(`input[name="option_id[${qId}]"]`).val($(`[name="answers[${qId}]"]:checked`).data('oid'));
                }
            }
        },
        onStepChanging: function(event, currentIndex, newIndex) {
            if (newIndex < currentIndex) {
                return true;
            }
            $('.toast').remove();
            var stepIsValid = true,
                validator = $('#submitsurvey').validate(),
                qType = $(`[data-step="${currentIndex}"]`).data('q-type'),
                qId = $(`[data-step="${currentIndex}"]`).data('q');
            $(':input', `[data-step="${currentIndex}"]`).each(function() {
                var xy = validator.element(this);
                stepIsValid = stepIsValid && (typeof xy == 'undefined' || xy);
            });
            if (!stepIsValid) {
                if (qType == "1") {
                    if ($(`[data-key-validation="true"]`, `[data-step="${currentIndex}"]`).val() == "") {
                        toastr.error('Please enter your answer.');
                    }
                } else if (qType == "2") {
                    toastr.error('Please select an option.');
                }
            } else {
                var selector = ((qType == "2") ? `[name="answers[${qId}]"]:checked` : `[name="answers[${qId}]"]`),
                    value = ((qType == "2") ? $(selector).parent().index() : $(`${selector}`).val());
                if (qType == "2") {
                    $(`input[name="option_id[${qId}]"]`).val($(`[name="answers[${qId}]"]:checked`).data('oid'));
                }
                updateAnswer(surveyId, currentIndex, value);
                updateProgrssbar(((newIndex) * 100) / totalQuestions);
            }
            return stepIsValid;
        },
        onFinishing: function(event, currentIndex) {
            $('.toast').remove();
            var stepIsValid = true,
                validator = $('#submitsurvey').validate(),
                qType = $(`[data-step="${currentIndex}"]`).data('q-type'),
                qId = $(`[data-step="${currentIndex}"]`).data('q');
            $(':input', `[data-step="${currentIndex}"]`).each(function() {
                var xy = validator.element(this);
                stepIsValid = stepIsValid && (typeof xy == 'undefined' || xy);
            });
            if (!stepIsValid) {
                var qType = $(`[data-step="${currentIndex}"]`).data('q-type');
                if (qType == "1") {
                    if ($(`[data-key-validation="true"]`, `[data-step="${currentIndex}"]`).val() == "") {
                        toastr.error('Please enter your answer.');
                    }
                } else if (qType == "2") {
                    toastr.error('Please select an option.');
                }
            } else {
                if (qType == "2") {
                    $(`input[name="option_id[${qId}]"]`).val($(`[name="answers[${qId}]"]:checked`).data('oid'));
                }
                updateProgrssbar(100);
            }
            return stepIsValid;
        },
        onFinished: function(event, currentIndex) {
            $('.toast').remove();
            $('a[href="#finish"]').parents('li').attr('aria-disabled', true).addClass('disabled');
            var _url = $('#submitsurvey').attr('action'),
                _data = $('#submitsurvey').serialize();
            $.ajax({
                url: _url,
                type: 'POST',
                dataType: 'json',
                data: _data,
            }).done(function(data) {
                if (data && data.status === 1) {
                    $('body').addClass('body-scroll-remover');
                    $('#survey_submitted').fadeIn('slow');
                } else {
                    toastr.error(data.message || "Failed to submit the survey, please try again!");
                }
            }).fail(function(error) {
                if (error.hasOwnProperty('responseJSON')) {
                    if (error.responseJSON.status == 0) {
                        toastr.error(error.responseJSON.message);
                    } else if (error.responseJSON.status == 2) {
                        // toastr.error('This survey has been expired.');
                        window.location.reload();
                    } else if (error.responseJSON.status == 3) {
                        // toastr.error('You have already submitted the survey.');
                        window.location.reload();
                    } else if (error.responseJSON.status == 4) {
                        // toastr.error('You have already submitted the survey.');
                        window.location.reload();
                    } else {
                        toastr.error("Something went wrong, please try again!");
                    }
                } else {
                    toastr.error("Something went wrong, please try again!");
                }
            }).always(function() {
                $('a[href="#finish"]').parents('li').removeAttr('aria-disabled').removeClass('disabled');
            });
        }
    });
    $(document).on('click', '.closePreview', function(e) {
        e.preventDefault();
    });
    $(document).on('change, click', 'input[type="radio"][data-skip-on-selection="true"]', function(e) {
        setTimeout(function() {
            $("#userQuestion").steps('next');
        }, 150);
    });
    $('[data-key-validation="true"]').on('keyup', function(e) {
        $(this).valid();
    });
    $(document).on('submit', '#surveyReviewForm', function(e) {
        e.preventDefault();
        if ($('#surveyReviewForm').valid()) {
            $('.toast').remove();
            $('#surveyReviewForm').css('pointer-events', 'none');
            $.ajax({
                url: $('#surveyReviewForm').attr('action'),
                type: 'POST',
                dataType: 'json',
                data: $('#surveyReviewForm').serialize(),
            }).done(function(data) {
                if (data.status && data.status === 1) {
                    $('body').addClass('body-scroll-remover');
                    $('#feedback_submitted').fadeIn('slow');
                    /*toastr.success(data.message);
                    setTimeout(function() {
                        window.location.href = $('#gth').attr('href');
                    }, 2000);*/
                } else {
                    $('#surveyReviewForm').css('pointer-events', '');
                    toastr.error(data.message || "Failed to store, please try again!");
                }
            }).fail(function(error) {
                $('#surveyReviewForm').css('pointer-events', '');
                if (error.hasOwnProperty('responseJSON')) {
                    if (error.responseJSON.status == 1) {
                        toastr.error(error.responseJSON.message);
                    } else {
                        toastr.error("Something went wrong, please try again!");
                    }
                } else {
                    toastr.error("Something went wrong, please try again!");
                }
            });
        }
    });
});
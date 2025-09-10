$(document).ready(function() {
    $("#userFeedback").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "fade",
        autoFocus: true,
        onStepChanging: function(event, currentIndex, newIndex) {
              if (newIndex < currentIndex) {
                return true;
            }
            $('.toast').remove();
            var stepIsValid = true,
                validator = $('#submitsurvey').validate();
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
            }
            return stepIsValid;
        },
        onFinished: function(event, currentIndex) {
            $('a[href="#finish"]').parents('li').attr('aria-disabled', true).addClass('disabled');
            var _url = $('#submitsurvey').attr('action'),
                _data = $('#submitsurvey').serialize();
            $.ajax({
                url: _url,
                type: 'POST',
                dataType: 'json',
                data: _data,
            }).done(function(data) {
                $('.toast').remove();
                if (data && data.status === 1) {
                    $('body').addClass('body-scroll-remover');
                    $('#survey_submitted').fadeIn('slow');
                } else {
                    toastr.error(data.message || "Failed to submit the survey, please try again!");
                }
            }).fail(function(error) {
                $('.toast').remove();
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
                        $('body').addClass('body-scroll-remover');
                        $('#comapny_expired').fadeIn('slow');
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
});
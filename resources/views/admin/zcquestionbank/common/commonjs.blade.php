<script>
    //---- Tabbing ----//
    if ($("#questionTab").length > 0) {
        $('#questionTab').easyResponsiveTabs({
            type: 'vertical', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            tabidentify: 'hor_1', // The tab groups identifier
            activetab_bg: '#B5AC5F', // background color for active tabs in this group
            inactive_bg: '#fafafa', // background color for inactive tabs in this group
            active_border_color: '#f2f2f2', // border color for active tabs heads in this group
            active_content_border_color: '#f2f2f2',
            activate: function (event) { // Callback function if tab is switched
                var pathname = window.location.pathname + location.hash;
                // trackMe('page', pathname);
                var $tab = $(this);
                var $info = $('#nested-tabInfo2');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        });
    }

    $(".category").select2().on('change', function () {
        var parentObj = $(this.form);
        var parentID = $(parentObj).attr('id');
        var value = this.value;
        if(!value) {return;}
        var _token = $('input[name="_token"]').val();
        categoryUrl = '{{ route("admin.ajax.zcSubCategories", ":id") }}'
        url = categoryUrl.replace(':id', value);
        $.ajax({
            url: url,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(result) {
                $('#'+ parentID +' .subcategories').empty();
                $('#'+ parentID +' .subcategories').attr('disabled', false);
                $('#'+ parentID +' .subcategories').val('').trigger('change').append('<option value="">Select</option>');
                $('#'+ parentID +' .subcategories').removeClass('is-valid');
                $.each(result.result, function(key, value) {
                    $('#'+ parentID +' .subcategories').append('<option value="' + value.id + '">' + value.name + '</option>');
                });
                if(!result.result) {return;}
                if (Object.keys(result.result).length == 1) {
                    $.each(result.result, function(key, value) {
                        $('#'+ parentID +' .subcategories').select2('val', value.id);
                    });
                }
            }
        })
    });

    function validateFormAndSubmit(selectors, submitSelector, mustBeValidFormSelector) {
        $('.invalid-feedback').remove();
        event.preventDefault();
        var isValid = false;
        var values = {};
        var showScoreError = false;
        var showScoreNullError = false;
        var choiceInputFlag = false;
        if (Array.isArray(selectors)) {
            selectors.forEach(function (selector) {
                var $inputs = $(selector + ' :input');
                $inputs.each(function () {
                    values[this.name] = $(this).val();
                });
            });
        } else {
            var $inputs = $(selectors + ' :input');
            $inputs.each(function () {
                values[this.name] = $(this).val();
            });
        }
        if ($(mustBeValidFormSelector).valid() && $(submitSelector).valid()) {
            $(submitSelector).find('.category').val($(submitSelector).find('.category').select2().val());
            $(submitSelector).find('.category_name').val($(submitSelector).find('.category option:selected').select2().text());

            $(submitSelector).find('.sub_category').val($(submitSelector).find('.subcategories').select2().val());
            $(submitSelector).find('.sub_category_name').val($(submitSelector).find('.subcategories option:selected').select2().text());

            var formData = $(submitSelector).serializeJSON();
            var data = JSON.stringify(formData);
            $('.form-data').val(data);

            imageRequiredForOptions(submitSelector);
            textRequiredForAllOptions(submitSelector);
            var isPreventSubmit = new Array();

            $(submitSelector).find('.image-hidden-selector').each(function (index, selector) {
                var imageVal = $(this).val();
                if (imageVal == '' || imageVal.includes('73a90acaae2b1ccc0e969709665bc62f')) {
                    $(this).parent().addClass('is-invalid');
                    $('.toast').remove();
                    toastr.warning('The question image field is required.');
                    isPreventSubmit.push(index)
                }
            });

            if(submitSelector == "#choice-question") {
                $(submitSelector).find('.question-wrap').each(function (index, selector) {
                    var qOrdreId = $(selector).data('order'),
                        scoreSum = 0;
                    $(selector).find('.score-field').each(function(sIndex, score) {
                        scoreSum += parseInt($(score).find(':selected').val());
                        
                        if($(score).find(':selected').val() == '') {
                            showScoreNullError = true;
                            $(score).addClass('is-invalid');
                        }
                    });

                    $(selector).find('.choice-input').each(function(sIndex, score) {
                        if($(score).val() == '') {
                            choiceInputFlag = true;
                            $(score).addClass('is-invalid');
                        }
                    });

                    if(scoreSum == 0) {
                        $(selector).find('.score-field').removeClass('is-valid').addClass('is-invalid');
                        isPreventSubmit.push(qOrdreId);
                        showScoreError = true;
                    } else {
                        $(selector).find('.score-field').removeClass('is-invalid');
                    }
                });
            }

            if (showScoreNullError) {
                toastr.error('Score field is required.');
                return false;
            }

            if (choiceInputFlag) {
                toastr.error('Choice field is required.');
                return false;
            }
            
            if(showScoreError) {
                toastr.warning('At Least one option must have more than "0" scores per question.');
            }

            if (showScoreNullError) {
                toastr.error('Score field is required.');
                return false;
            }

            if (choiceInputFlag) {
                toastr.error('Choice field is required.');
                return false;
            }

            $(submitSelector).find('.imageNotice').each(function (index, selector) {
                var children = this.children;
                dataOrderId = $(this).parent().attr('data-order');
                imageRequiredForOptions(submitSelector, dataOrderId);
                if (children.length > 0) {
                    isPreventSubmit.push(index)
                }
            });

            var isPreventSubmitForText = new Array();
            $(submitSelector).find('.textNotice').each(function (index, selector) {
                var childrenText = this.children;
                if (childrenText.length > 0) {
                    isPreventSubmitForText.push(index)
                }
            });
            if (isPreventSubmit.length > 0 || isPreventSubmitForText.length > 0) {
                event.preventDefault();
                return;
            } else {
                $('.form-data').val(null);
                $('.image-hidden-selector').val(null);
                $('.choice-image-hidden-selector').val(null);
                var isSaveAndNew = $(event.currentTarget).attr('name');
                if (isSaveAndNew != undefined && isSaveAndNew == 'saveAndNew') {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'isSaveAndNew',
                        name: 'isSaveAndNew',
                        value: 1
                    }).appendTo(submitSelector);
                }

                if ($(mustBeValidFormSelector).valid() && $(submitSelector).valid()) {
                    $('.saveButton').attr("disabled", false);
                    $('.saveAndNewButton').attr("disabled", false);
                }
                $(submitSelector).submit();
            }
        }
    }

    function imageRequiredForOptions(formSelector, dataOrderId) {
        if (dataOrderId == "" || dataOrderId == undefined) {
            return;
        }
        var span = $('<span />').css("color", "red").html('An image is required for all options');
        var formData = $(formSelector).serializeJSON();
        for (var imageKey in formData.image) {
            var noticSelector = $(formSelector + ' [data-order="' + dataOrderId + '"]').find('.imageNotice');
            var questionRow = $(formSelector + ' [data-order="' + dataOrderId + '"]');
            var questionOptionRow = questionRow.find('.question-offset');
            var questionOptions = questionOptionRow.find('.qus-option1');
            var minmaxlength = questionOptions.length;

            var imageObject = formData.image[dataOrderId];
            if (formSelector == '#choice-question') {
                var availableSrc = new Array();
                var defaultImages = new Array();
                for (var imageObjectChoice in imageObject) {
                    var imageSrcLink = imageObject[imageObjectChoice]["imageSrc"];
                    if (imageSrcLink == "") {
                        // defaultImages.push(imageSrcIndex);
                    } else if (imageSrcLink.includes('73a90acaae2b1ccc0e969709665bc62f')) {
                        defaultImages.push(imageSrcIndex);
                        imageSrcLink = '';
                    } else {

                    }
                    if (imageSrcLink != '') {
                        // If src not found
                    } else {
                        // src found
                        availableSrc.push(imageSrcLink);
                    }
                }
                if (defaultImages.length === minmaxlength) {
                    // all are default images are found.
                    noticSelector.html(null);
                    $('.saveButton').attr("disabled", false);
                    $('.saveAndNewButton').attr("disabled", false);
                } else {
                    if (availableSrc.length < minmaxlength) {
                        if (availableSrc.length) {
                            // Show the notice
                            noticSelector.html(span);
                        } else {
                            // remove the notice.
                            noticSelector.html(null);
                            $('.saveButton').attr("disabled", false);
                            $('.saveAndNewButton').attr("disabled", false);
                        }

                    } else {
                        // remove the notice.
                        noticSelector.html(null);
                        $('.saveButton').attr("disabled", false);
                        $('.saveAndNewButton').attr("disabled", false);
                    }
                }
            } else {
                var imageSrcvaluSelector = 'imageSrc';
                var imageSrc = imageObject[imageSrcvaluSelector];
                var availableSrc = new Array();
                var defaultImages = new Array();
                for (var imageSrcIndex in imageSrc) {
                    var imageSrcLink = imageSrc[imageSrcIndex];

                    if (imageSrcLink == "") {
                        defaultImages.push(imageSrcIndex);
                    } else if (imageSrcLink.includes('73a90acaae2b1ccc0e969709665bc62f')) {
                        defaultImages.push(imageSrcIndex);
                        imageSrcLink = '';
                    } else {

                    }

                    if (!isUrl(imageSrcLink)) {
                        // If src not found
                    } else {
                        // src found
                        availableSrc.push(imageSrcLink);
                    }
                }
                if (defaultImages.length === minmaxlength) {
                    // all are default images are found.
                    noticSelector.html(null);
                } else {
                    if (availableSrc.length < minmaxlength) {
                        // Show the notice
                        noticSelector.html(span);
                    } else {
                        // remove the notice.
                        noticSelector.html(null);
                    }
                }
            }
        }
    }

    function textRequiredForAllOptions(formSelector, dataOrderId) {
        if (dataOrderId == "" || dataOrderId == undefined) {
            return;
        }
        var span = $('<span />').css("color", "red").html('A text is required for all options');
        var formData = $(formSelector).serializeJSON();
        for (var imageKey in formData.text) {
            var noticSelector = $(formSelector + ' [data-order="' + dataOrderId + '"]').find('.textNotice');
            var questionRow = $(formSelector + ' [data-order="' + dataOrderId + '"]');
            var questionOptionRow = questionRow.find('.question-offset');
            var questionOptions = questionOptionRow.find('.qus-option1');
            var minmaxlength = questionOptions.length;

            var textObject = formData.text[dataOrderId];
            if (formSelector == '#slidingbar-question') {
                var availableText = new Array();
                for (var textObjectChoice in textObject) {
                    var currentText = textObject[textObjectChoice];
                    currentText = currentText.trim();
                    if (currentText != "" && currentText != undefined && currentText.length >= 1) {
                        availableText.push(currentText);
                    }
                }
                if (availableText.length == 0) {
                    minmaxlength = 0;
                }
                if (availableText.length < minmaxlength) {
                    // Show the notice
                    noticSelector.html(span);
                } else {
                    // remove the notice.
                    noticSelector.html(null);
                    $('.saveButton').attr("disabled", false);
                    $('.saveAndNewButton').attr("disabled", false);
                }
            }
        }
    }

    function isUrl(s) {
        var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
        return regexp.test(s);
    }

    var bar = $('#mainProgrssbar');
    var percent = $('#mainProgrssbar .progpercent');

    $('#free-text,#choice-question').ajaxForm({
        dataType: 'json',
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html('Uploading media....');
            $('.progress-loader-wrapper').show();
            $('#free-text button, #choice-question button').attr('disabled', 'disabled');
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        // beforeSubmit: function(arr, $form, options) {
        //     arr = arr.filter(item => ['_token','form-data','isSaveAndNew','question_image-free-text','question_image-choice','image'].includes(item.name));
        //     debugger;
        // },
        uploadProgress: function(event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal)
            percent.html(percentVal);
            if(percentComplete == 100) {
                $('.progress-loader-wrapper .status-text').html('Processing on media...');
            }
        },
        success: function(data) {
            $('.progress-loader-wrapper').hide();
            var percentVal = '100%';
            bar.width(percentVal)
            percent.html(percentVal);
            if(data.status && data.status == 1) {
                if(data.route) {
                    toastr.success(data.data);
                    window.setTimeout(function(){
                        window.location.replace(data.route);
                    } ,250);
                } else {
                    toastr.success(data.data);
                    window.setTimeout(function(){
                        window.location.reload();
                    } ,250);
                }
            } else {
                if(data.data && data.data != '') { toastr.error(data.data); }
                window.setTimeout(function(){
                    window.location.reload();
                } ,250);
            }
        },
        error: function(data) {
            $('.progress-loader-wrapper').hide();
            $('#free-text button, #choice-question button').removeAttr('disabled');

            if(data && data.data) {
                toastr.error(data.data);
            } else {
                toastr.error('{{ trans('labels.common_title.something_wrong_try_again') }}');
            }

            window.setTimeout(function(){
                window.location.reload();
            } ,250);

            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        }
    });
</script>
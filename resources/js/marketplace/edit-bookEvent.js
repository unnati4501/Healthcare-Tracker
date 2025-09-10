// function to load slots based on selected company, date, and time
var currentRequest;
showPageLoaderWithMessage();

$('#time-owl-carousel').owlCarousel({
    loop:false,
    margin:10,
    nav:true,
    navText: ["<i class='far fa-long-arrow-left'></i>", "<i class='far fa-long-arrow-right'></i>"],
    dots:false,
    responsive:{
        0:{
            items:1
        },
        600:{
            items:2
        },
        1000:{
            items:2
        },
        1600:{
            items:3
        }
    }
})

$(document).on('click', '.presenter-item .view-more', function(e) {
    if($(this).text() == "+ Read More"){
        $(this).text("- Read Less")
        $(this).closest(".presenter-item").find(".other-timings").css('display','inline');
    }else{
        $(this).text("+ Read More")
        $(this).closest(".presenter-item").find(".other-timings").css('display','none');
    }
})

function loadSlots() {
    if($('#createdCompany').val() != '' && ($('#companyType').val() == 'rca' || $('#companyType').val() == 'zca' || $('#companyType').val() == 'rsa')) {
        var timeFrom = moment($('#timeFrom').timepicker('getTime')),
            timeTo = moment($('#timeTo').timepicker('getTime')),
            timeDiff = (timeTo.diff(timeFrom, 'milliseconds', true) || 0);
        if (timeFrom.isSame(timeTo)) {
            return false;
        }
        if (timeDiff == 0 || timeDiff > _minDiff) {
            $('.toast').remove();
            toastr.error(`Time range must be within event duration(${$('[data-duration]').html().trim()})`);
            $('#selectedslot').val('');
            $('[data-capacity-block]').data('totalusers', 0);
            $('[data-hint-block]').show();
            $('#selectedslot-error, [data-slots-block], [data-loader-block], [data-no-slots-block], [data-register-all-user-error]').hide();
            return false;
        }
        var params = {
            company: ($('#company').val() || ""),
            date: ($('#date').val() || ""),
            from: ($('#timeFrom').val() || ""),
            to: ($('#timeTo').val() || ""),
        };
    }else{
        var params = {
            company: ($('#company').val() || ""),
            date: ($('#date').val() || ""),
        };
    }
    /*var params = {
        company: ($('#company').val() || ""),
        date: ($('#date').val() || ""),
        //from: ($('#timeFrom').val() || ""),
        //to: ($('#timeTo').val() || ""),
    };*/
    if (params.company != "" && params.date != "") {
        // set visibility of blocks
        //$('#selectedslot').val('');
        $('[data-capacity-block]').data('totalusers', 0);
        $('#selectedslot-error, [data-slots-block], [data-hint-block], [data-no-slots-block], [data-register-all-user-error]').hide();
        $('[data-loader-block]').show();
        // convert from and to time to 24 hrs
        if($('#createdCompany').val() != '' && ($('#companyType').val() == 'rca' || $('#companyType').val() == 'zca' || $('#companyType').val() == 'rsa')) {
            params.from = timeFrom.format("HH:mm:00");
            params.to = timeTo.format("HH:mm:00");
        }
        if (currentRequest && currentRequest.readyState != 4) {
            currentRequest.abort();
        }
        currentRequest = $.ajax({
            url: urls.getSlots,
            type: 'POST',
            dataType: 'json',
            data: params,
        }).done(function(data) {
            if (data && data.status) {
                // set total user count for capacity
                $('[data-capacity-block]').data('totalusers', data.coUsersCount);
                // if slots are available then show accordingly
                $('[data-hint-block], [data-no-slots-block]').hide();
                $('[data-slots-block]').show();
                _slotsCarousel.trigger('replace.owl.carousel', data.slots).trigger('refresh.owl.carousel');
                //$('.enable-me').removeClass('pe-none grayed-out-color enable-me').trigger('click');
                /*if (_companyType == 'rca') {
                    $('#selectedslot').val(1);
                }*/
            } else {
                // if slots are not available then show no result
                if (data.message != undefined && data.message != "") {
                    toastr.error(data.message);
                }
                $('[data-capacity-block]').data('totalusers', 0);
                $('[data-slots-block], [data-hint-block]').hide();
                $('[data-no-slots-block]').show();
                /*if (_companyType == 'rca') {
                    $('#selectedslot').val(1);
                }*/
            }
        }).fail(function(error) {
            // show error toastr and set visibility of blocks
            if (error.statusText != "abort") {
                $('.toast').remove();
                toastr.error(error.responseJSON.message || "Failed to load slots, Please try again!");
                $('[data-capacity-block]').data('totalusers', 0);
                $('[data-no-slots-block]').show();
                $('[data-slots-block], [data-hint-block]').hide();
                if (_companyType == 'rca') {
                    $('#selectedslot').val("");
                }
            }
        }).always(function() {
            $('[data-loader-block], [data-register-all-user-error]').hide();
        });
    } else {
        // set visibility of blocks
        $('#selectedslot').val('');
        $('[data-capacity-block]').data('totalusers', 0);
        $('[data-hint-block]').show();
        $('[data-slots-block], [data-loader-block], [data-no-slots-block], [data-register-all-user-error]').hide();
        /*if (_companyType == 'rca') {
            $('#selectedslot').val(1);
        }*/
    }
}
$(document).ready(function() {
    // set presenter visibility
    if ($('#createdCompany').val() != '' && (_companyType == 'rca' || _companyType == 'rsa' || _companyType == 'zca')) {
        $('#mainPresenterList').hide();
        $('#rcaPresenter').show();
        $('#rcaEventDates').show();
    } else {
        $('#mainPresenterList').show();
        $('#rcaPresenter').hide();
        $('#rcaEventDates').hide();
    }
    setTimeout(function() {
        loadSlots();
    }, 1000);
    // set CSRF token default in each ajax request
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(".other-timings").hide();
    // this will ignore element with class .ignore-validation while validating form
    $('#bookEvent').validate().settings.ignore = ".ignore-validation";
    // initialize date picker
    $('#date').datepicker({
        startDate: '+1d',
        endDate: '+180d',
        autoclose: true,
        format: 'dd-mm-yyyy',
    }).on('changeDate', function(e) {
        $('#date').valid();
        if (e && e.date != undefined) {
            loadSlots();
        }
    });

    $.validator.addMethod("customEmailValidate", function (value, element) {
        return this.optional(element) || /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/.test(value);
    }, 'Enter valid email address.');
    $.validator.addClassRules("customEmailValidate", {
        customEmailValidate: true,
        maxlength: 50
    });

    // initialize time picker
    $('.time').timepicker({
        showDuration: false,
        timeFormat: 'h:i A',
        step: 30,
        useSelect: true,
    });
    $('.time-range').datepair({
        'defaultTimeDelta': _minDiff // 60000 milliseconds => 1 Minute
    });
    // update from time as per the event duration considering to time
    var timeTo = moment($('#timeFrom').timepicker('getTime')).add(_minDiff, 'milliseconds');
    $('#timeTo').timepicker('setTime', timeTo.toDate());
    // hide other options
    setTimeout(() => {
        $(`[name="ui-timepicker-timeFrom"] option[value="${_maxDuration}"]`).nextAll().hide();
        hidesPageLoader();
    }, 100);
    // initialize slots carousel
    _slotsCarousel = $('[data-slots-block]').owlCarousel({
        margin: 10,
        responsiveClass: true,
        dots: false,
        nav: true,
        navText: ["<i class='fal fa-chevron-circle-left'></i>", "<i class='fal fa-chevron-circle-right'></i>"],
        responsive: {
            0: {
                items: 1,
            },
            600: {
                items: 2,
            },
            991: {
                items: 2,
            },
            1200: {
                items: 3,
            },
            1500: {
                items: 4,
            },
            1800: {
                items: 5,
            }
        }
    });
    CKEDITOR.instances.notes.on('change', function() {
        var description = CKEDITOR.instances['notes'].getData();
        description = $(description).text().trim();
        if (description.length > 500) {
            $('#notes-error-cstm').addClass('is-invalid').show();
        } else {
            $('#notes-error-cstm').removeClass('is-invalid').hide();
        }
    });
    if (CKEDITOR.instances.email_notes != undefined) {
        CKEDITOR.instances.email_notes.on('change', function() {
            var description = CKEDITOR.instances['email_notes'].getData();
            description = $(description).text().trim();
            if (description.length > 500) {
                $('#email_notes-error-cstm').addClass('is-invalid').show();
            } else {
                $('#email_notes-error-cstm').removeClass('is-invalid').hide();
            }
        });
    }
       
    $(document).on('change', '#timeFrom', function(e) {
        $('.time-range').off('change');
        var timeTo = moment($('#timeFrom').timepicker('getTime')).add(_minDiff, 'milliseconds');
        setTimeout(function() {
            $('#timeTo').timepicker('setTime', timeTo.toDate());
            loadSlots();
        }, 1);
    });
    // on slot radio selection
    /*$(document).on('change', 'input[name="slot"]', function(e) {
        var selectedSlot = ($('input[name="slot"]:checked').val() || 0);
        $('#selectedslot').val(((selectedSlot > 0) ? selectedSlot : "")).valid();
        $("#selectedslot_start_time").val(($('input[name="slot"]:checked').attr('data-slot-start-time')));
    });*/
    $(document).on('click', 'input[name="slot"]', function(e) {
        var selectedSlot = ($('input[name="slot"]:checked').val() || 0);
        $('#selectedslot').val(((selectedSlot > 0) ? selectedSlot : "")).valid();
        $(".custom-radio").removeClass('checked');
        $("."+$(this).attr('id')).addClass('checked');
        $("#selectedslot_start_time").val(($('input[name="slot"]:checked').attr('data-slot-start-time')));
        $("#selectedslot_end_time").val(($('input[name="slot"]:checked').attr('data-slot-end-time')));
    });
    // show confirm popup on book button
    $(document).on('click', '#btn-book-event', function(e) {
        var canGo = true;
        for (instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
            var description = CKEDITOR.instances[instance].getData(),
                errorElement = CKEDITOR.instances[instance].element.$.dataset.errplaceholder;
            description = $(description).text().trim();
            if (description.length > 500) {
                canGo = false;
                $(errorElement).addClass('is-invalid').show();
            } else {
                $(errorElement).removeClass('is-invalid').hide();
            }
        }
        if ($('#bookEvent').valid() && canGo) {
            $('#book-event-model-box').modal('show');
        }
    });
    // submit form on yes button click
    $(document).on('click', '#book-event-confirm', function(e) {
        $('#bookEvent').submit();
    });
    // book event form submit
    $('#bookEvent').ajaxForm({
        type: 'post',
        dataType: 'json',
        beforeSerialize: function($form, options) {
            var canGo = true;
            for (instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
                var description = CKEDITOR.instances[instance].getData();
                description = $(description).text().trim();
                if (description.length > 500) {
                    canGo = false;
                }
            }
            return canGo;
        },
        beforeSubmit: function(arr, $form, options) {
            return true;
        },
        beforeSend: function() {
            $('.toast').remove();
            $('#book-event-model-box .modal-header, #book-event-model-box .modal-footer').css('pointer-events', 'none');
            $('#book-event-confirm').html(`<i class="fa fa-spinner fa-spin"></i>`);
        },
        success: function(data) {
            if (data.status && data.status == 1) {
                window.location.replace(data.redirectTo);
            } else {
                toastr.error((data.data || _trans.swr));
            }
        },
        error: function(error) {
            $('#book-event-confirm').html(_trans.yesBtn);
            if (error.responseJSON && error.responseJSON.data && error.responseJSON.data != '') {
                toastr.error(error.responseJSON.data);
                if (error.responseJSON.status && (error.responseJSON.status == 2 || error.responseJSON.status == 3)) {
                    $('#selectedslot').val('');
                    $('[data-capacity-block]').data('totalusers', 0);
                    $('[data-hint-block]').show();
                    $('#selectedslot-error, [data-slots-block], [data-loader-block], [data-no-slots-block], [data-register-all-user-error]').hide();
                    loadSlots();
                }
            } else {
                toastr.error((error.data || _trans.swr));
            }
        },
        complete: function(xhr) {
            $('#book-event-model-box').modal('hide');
            $('#book-event-model-box .modal-header, #book-event-model-box .modal-footer').css('pointer-events', '');
            $('#book-event-confirm').html(_trans.yesBtn);
        }
    });

    //Add multiple cc email
    $('#addCCEmails').on('click', function() {
        // Get previous form value
        var currentFormId = $('#total-cc-emails').val();
        // Increase form value for next iteration.
        currentFormId++;
        // var previousFormId = currentFormId - 1;
       // Get last custom leave html source
        var $lastItem = $('.book_event_from_submit .cc-email-wrap').last();
        var previousFormId = $lastItem.attr('data-order');
        // Create new clone from lastItem
        var $newItem = $lastItem.clone(true);
        // Insert clone html after last custom leave html
        $newItem.insertAfter($lastItem);
        // cc id increment logic
        $newItem.find(':input').each(function() {
        var name = $(this).attr('name');
        if (name) {
            var name = $(this).attr('name').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            //var id = $(this).attr('id').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            var id = $(this).attr('id').replace(previousFormId, currentFormId);
            // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
            $(this).attr({
                'name': name,
                'id': id,
                'data-previewelement': currentFormId,
                'aria-describedby': name+'-error'
            }).data('previewelement', currentFormId).val('').removeAttr('checked');
        }
        });
        // This is used for identify current raw of cc email.
        $newItem.closest('.cc-email-wrap').attr('data-order', currentFormId);
            $('#total-cc-emails').val(currentFormId);
    });

    // Delete cc boxes.
    $('body').on('click', '.delete-cc-emails', function() {
        var ccEmailSelector = $(this).closest('.cc-email-wrap');
        var totalCCEmailInForm = $(".book_event_from_submit").find('.cc-email-wrap');
        if (totalCCEmailInForm.length == 1) {
            
        } else {
            ccEmailSelector.remove();
            $('#total-cc-emails').val($(".book_event_from_submit").find('.cc-email-wrap').length);
        }
    });
});
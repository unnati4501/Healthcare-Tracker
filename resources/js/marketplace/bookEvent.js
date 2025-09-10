// function to load slots based on selected company, date, and time
var currentRequest;

var start = new Date();
start.setMinutes(start.getMinutes() + 30);
end = new Date(new Date().setMonth(start.getMonth() + 3));
showPageLoaderWithMessage();
//bookEvent.validate().settings.ignore = ":disabled,:hidden";
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$('#presenter-owl-carousel').owlCarousel({
    loop: false,
    margin: 10,
    nav: true,
    navText: ["<i class='far fa-long-arrow-left'></i>", "<i class='far fa-long-arrow-right'></i>"],
    dots: false,
    items: 1,
});
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
            toastr.error(`Time range must be within event duration(${$('[data-duration]').val().trim()})`);
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
    
    if (params.company != "") {
        var type = ($('#company option:selected').data('company-type') || _companyType);
        // set visibility of blocks
        $('#selectedslot').val('');
        $('[data-capacity-block]').data('totalusers', 0);
        $('#selectedslot-error, [data-slots-block], [data-hint-block], [data-no-slots-block], [data-register-all-user-error]').hide();
        $('[data-loader-block]').show();
        $('#register_all_users').prop('checked', false).change();
        // convert from and to time to 24 hrs
        if($('#createdCompany').val() != '' && ($('#companyType').val() == 'rca' || $('#companyType').val() == 'zca' || $('#companyType').val() == 'rsa')) {
            params.from = timeFrom.format("HH:mm:00");
            params.to = timeTo.format("HH:mm:00");
        }
        
        if (currentRequest && currentRequest.readyState != 4) {
            currentRequest.abort();
        }
        currentRequest = $.ajax({
            url: urls.getSlots.replace(':event', _event),
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
                /*if (type == 'rca') {
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
                /*if (type == 'rca') {
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
                if (type == 'rca') {
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
        if (type == 'rca') {
            $('#selectedslot').val("");
        }
    }
}
$(document).ready(function() {
    // set CSRF token default in each ajax request
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#registrationdate").datetimepicker({
        format: 'yyyy-mm-dd hh:ii:00',
        startDate: start,
        endDate: end,
        autoclose: true,
        fontAwesome:true,
        minuteStep: 30,
        todayHighlight: false,
    }).on('changeDate', function () {
        $("#registrationdate").valid();
    });
    $(function() {  
        $('.actions ul li a[href="#previous"]').addClass("disabled");
    });
    $(".other-timings").hide();
    if($('#companyType').val() == 'zca' || $('#companyType').val() == 'rca') {
        loadSlots();
    }
    // set presenter visibility
    if($('#createdCompany').val() != '' && ($('#companyType').val() == 'rca' || $('#companyType').val() == 'zca' || $('#companyType').val() == 'rsa')) {
        $('#mainPresenterList').hide();
        $('#rcaPresenter').show();
        $('#rcaEventDates').show();
    } else {
        $('#mainPresenterList').show();
        $('#rcaPresenter').hide();
        $('#rcaEventDates').hide();
    }
    // this will ignore element with class .ignore-validation while validating form
    $('#bookEvent').validate().settings.ignore = ".ignore-validation";
    // initialize date picker
    $('#date').datepicker({
        startDate: '+30m',
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
    
    $(document).on('click', '.presenter-item .view-more', function(e) {
        if($(this).text() == "+ Read More"){
            $(this).text("- Read Less")
            $(this).closest(".presenter-item").find(".other-timings").css('display','inline');
        }else{
            $(this).text("+ Read More")
            $(this).closest(".presenter-item").find(".other-timings").css('display','none');
        }
    })

    $(function() {
        var editor = tinymce.init({
            selector: "#description, #email_notes, #notes",
            branding: false,
            menubar:false,
            statusbar: false,
            plugins: "code,link,lists,advlist",
            toolbar: 'formatselect | bold italic forecolor backcolor permanentpen formatpainter alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent | removeformat | code | link',
            forced_root_block : true,
            paste_as_text : true,
            setup: function (editor) {
                editor.on('change redo undo', function () {
                });
            }
        });
    });
    // load slots on change of company, date and time
    $(document).on('change', '#company', function(e) {
        var selection = ($('#company option:selected').data('feed-selection') || false),
            type = ($('#company option:selected').data('company-type') || _companyType),
            value = $(this).val();
        if (value == '' || selection == true) {
            $('#add_to_story').prop('disabled', false).parent().show();
        } else {
            $('#add_to_story').prop('disabled', true).parent().hide();
        }
        $('#selectedslot-error').hide();
        $('#companyType').val(type);
        if ($('#createdCompany').val() != '' && (type == 'rca' || type == 'zca' || type == 'rsa')) {
            $('#rcaPresenter').show();
            $('#rcaEventDates').show();
            $('#mainPresenterList').hide();
        } else {
            $('#rcaPresenter').hide();
            $('#rcaEventDates').hide();
            $('#mainPresenterList').show();
        }
        loadSlots();
    });
    $(document).on('change', '#timeFrom', function(e) {
        $('.time-range').off('change');
        var timeTo = moment($('#timeFrom').timepicker('getTime')).add(_minDiff, 'milliseconds');
        setTimeout(function() {
            $('#timeTo').timepicker('setTime', timeTo.toDate());
            loadSlots();
        }, 1);
    });

    $(document).on('click', 'input[name="slot"]', function(e) {
        var selectedSlot = ($('input[name="slot"]:checked').val() || 0);
        $('#selectedslot').val(((selectedSlot > 0) ? selectedSlot : "")).valid();
        $(".custom-radio").removeClass('checked');
        $("."+$(this).attr('id')).addClass('checked');
        $("#selectedslot_start_time").val(($('input[name="slot"]:checked').attr('data-slot-start-time')));
        $("#selectedslot_end_time").val(($('input[name="slot"]:checked').attr('data-slot-end-time')));
    });
    
    // on slot radio selection
    $(document).on('change', '#register_all_users', function(e) {
        var selector = $('[data-capacity-block]'),
            capacity = 0,
            totalUsers = 0;
        if (selector.length > 0) {
            if ($(this).is(':checked')) {
                capacity = selector.data('capacity');
                totalusers = selector.data('totalusers');
                if (totalusers > capacity) {
                    $('[data-register-all-user-error]').removeClass('highlight4s').addClass('highlight4s').html(_trans.capacityError.replace('#capacity#', capacity)).show();
                    setTimeout(function() {
                        $('[data-register-all-user-error]').removeClass('highlight4s');
                    }, 4000);
                }
            } else {
                $('[data-register-all-user-error]').hide();
            }
        }
    });
    // show confirm popup on book button
    $(document).on('click', '#btn-book-event', function(e) {
        if ($('#register_all_users').is(':checked')) {
            var selector = $('[data-capacity-block]');
            if (selector.length > 0) {
                var capacity = selector.data('capacity'),
                    totalusers = selector.data('totalusers');
                if (totalusers > capacity) {
                    $('[data-register-all-user-error]').removeClass('highlight4s').addClass('highlight4s').html(_trans.capacityError.replace('#capacity#', capacity)).show();
                    setTimeout(function() {
                        $('[data-register-all-user-error]').removeClass('highlight4s');
                    }, 4000);
                    return false;
                }
            }
        }
        var canGo = true;
    });

    //Add multiple cc email
    $(document).on('click', '#addCCEmails', function(e) {
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

    $(document).on('click', '.pick-presenter', function() {
        if (data.locationType == 'online') {
            var videoUrl = $(this).attr('videoLink');
            $('#location_type_section').removeClass('d-none');
            $('#video_link_field').val(videoUrl);
            $('#company_type').val('Zevo');
        }
    });

    $(document).on('click', '#edit-video-link', function() {
        $('#video_link_field').attr('readonly', false);
        $('#save-video-link').removeClass('d-none');
        $('#edit-video-link').addClass('d-none');
    });

    $(document).on('click', '#save-video-link', function() {
        var videoUrl = $('.pick-presenter:checked').attr('videoLink');
        var newVideoUrl = $('#video_link_field').val();
        var pattern = /^(http|https)?:\/\/[a-zA-Z0-9-\.]+\.[a-z]{2,4}/;
        if (newVideoUrl == '') {
            toastr.warning("Video link field is required");
        } else if (!pattern.test(newVideoUrl)) {
            toastr.warning("Please enter valid video link url.");
        }
        $('#video_link_field').attr('readonly', true);
        $('#save-video-link').addClass('d-none');
        $('#edit-video-link').removeClass('d-none');
        if (videoUrl != newVideoUrl) {
            $('#company_type').val('Company'); 
        }
    });

    $(document).on('click', '#edit-presenter', function() {
        $('.display-presenter').addClass('d-none');
        $('.update-presenter-section').removeClass('d-none');
        $('#updateflag').val(2);
        $('a[href="#finish"]').text('Next');
    });

    $("#bookEventAddSteps").steps({
        headerTag: "h3",
        bodyTag: "div",
        transitionEffect: "slideLeft",
        autoFocus: true,
        labels: {
            next: "Next",
            previous: "Previous",
            finish: data.buttonName,
            cancel: "Cancel",
        },
        onStepChanging: function(event, currentIndex, newIndex) {
            var nextPageFlag = true;
            var descriptionValid = true;
            console.log("Step Changing");
            console.log(currentIndex, newIndex);
            if (currentIndex == 0 && newIndex == 1) {
                var companyId = $('#company').find('option:selected').val();
                nextPageFlag = true;

                if ($("#action").val() == 'add') {
                    $.ajax({
                        type: 'GET',
                        url: url.checkCredit.replace(':company', companyId),
                        data: null,
                        crossDomain: true,
                        cache: false,
                        async: false,
                        contentType: 'json',
                        success: function(data) {
                            if (data.data) {
                                nextPageFlag = true;
                                // Disable validation on fields that are disabled or hidden.
                                bookEvent.validate().settings.ignore = ":disabled,:hidden";
                            } else {
                                nextPageFlag = false;
                                toastr.error(_trans.credit_error);
                            }
                        }
                    });
                }
                
                 
                var editor = tinymce.get('description');
                var content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim();
                var contentLength = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim().length;
                
                var patt = /^(^([^#^]*))+$/;
                if (contentLength ==  0) {
                    descriptionValid = false;
                    $('#description-required-error').show();
                    $('#description-max-error, #description-format-error').hide();
                    $('#description').next('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                }else if (contentLength >  2500) {
                    descriptionValid = false;
                    $('#description-max-error').show();
                    $('#description-required-error, #description-format-error').hide();
                    $('#description').next('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                } else if (!patt.test(content)) {
                    descriptionValid = false;
                    $('#description-format-error').show();
                    $('#description-required-error, #description-max-error').hide();
                    $('#description').next('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
                } else {
                    $('#description-max-error, #description-format-error, #description-required-error').hide();
                    $('#description').next('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
                    descriptionValid = true;
                }
            } 
            if (nextPageFlag == false || descriptionValid == false) {
                return false;
            }

            if (currentIndex > newIndex) {
                return true;
            } 

            $('#presenter-owl-carousel').owlCarousel({
                loop: false,
                margin: 10,
                nav: true,
                navText: ["<i class='far fa-long-arrow-left'></i>", "<i class='far fa-long-arrow-right'></i>"],
                dots: false,
                items: 1,
            });

            // Disable validation on fields that are disabled or hidden.
            // bookEvent.validate().settings.ignore = ":disabled,:hidden";
            return bookEvent.valid();
        },
        onStepChanged: function(event, currentIndex, priorIndex) {
            $(".page-loader-wrapper").fadeIn();

            var start = new Date();
            start.setMinutes(start.getMinutes() + 30);
            end = new Date(new Date().setMonth(start.getMonth() + 3));

            $("#registrationdate").datetimepicker({
                format: 'yyyy-mm-dd hh:ii:00',
                startDate: start,
                endDate: end,
                autoclose: true,
                fontAwesome:true,
                minuteStep: 30,
                todayHighlight: false,
            }).on('changeDate', function () {
                $("#registrationdate").valid();
            });
           
            $('.booking-steps').removeClass('current').hide();
            if (currentIndex == 1 && priorIndex == 0) {
                $(".event-details-content").addClass("completed");
                $(".registration-details-content").addClass("active");
                $('#bookEventAddSteps-p-1').show().addClass('current');
                $('.actions ul li a[href="#previous"]').removeClass("disabled");
            } else if (currentIndex == 2 && priorIndex == 1) {
                $(".registration-details-content").addClass("completed");
                $(".event-booked-content").addClass("active");
                $('#bookEventAddSteps-p-2').show().addClass('current');
            }
        
            // Prev functionality
            if (currentIndex == 0 && priorIndex == 1) {
                $(".event-details-content").addClass("active");
                $(".event-details-content").removeClass("completed");
                $(".registration-details-content").removeClass("active");
                $('#bookEventAddSteps-p-0').show().addClass('current');
                $('.actions ul li a[href="#previous"]').addClass("disabled");
            } else if (currentIndex == 1 && priorIndex == 2) {
                $(".registration-details-content").addClass("active");
                $(".registration-details-content").removeClass("completed");
                $(".event-booked-content").removeClass("active");
                $('#bookEventAddSteps-p-1').show().addClass('current');
                $('.actions ul li a[href="#previous"]').removeClass("disabled");
            } 
            $(".page-loader-wrapper").fadeOut();
        },
        onCanceled: function(event) {
            // console.log("Step Canceled");
        },
        onFinishing: async function(event, currentIndex) {
            // console.log("Step Finishing");
        },
        onFinished: function(event, currentIndex) {
            var descriptionValid = true;
            var editor = tinymce.get('notes');
            var content = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim();
            var contentLength = $(editor.getContent()).text().replace(/[\r\n]+/g, "").trim().length;
            if (contentLength >  2500) {
                descriptionValid = false;
                $('#notes-max-error').show();
                $('#notes-required-error, #description-format-error').hide();
                $('#notes').next('.tox-tinymce').addClass('is-invalid').css('border-color', '#f44436');
            } else {
                $('#notes-max-error, #description-format-error, #description-required-error').hide();
                $('#notes').next('.tox-tinymce').removeClass('is-invalid').css('border-color', '');
                descriptionValid = true;
            }

            if(bookEvent.valid() == true && descriptionValid == true) {
                bookEvent.submit();
            }
        },
    });
});
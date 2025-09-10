var todayeventcalenderlistslider = $('#todays-event-calender-list').owlCarousel({
    loop: false,
    margin: 0,
    responsiveClass: true,
    dots: false,
    nav: true,
    navText: ["<i class='fal fa-chevron-circle-left'></i>", "<i class='fal fa-chevron-circle-right'></i>"],
    responsive: {
      0: {
        items: 1,
      },
      991: {
        items: 2,
      },
      1200: {
        items: 2,
      },
      1500: {
        items: 3,
      }
    }
});
/*
 * Booking tab common AJAX call
 */
function bookingTabAjaxCall(tier, options = null) {
    var age = $('.age').val();
    age = ((age) ? age.split('_') : age);
    if($('#roleType').val() == 1) {
        if($('#industry_id').val() != '' && $('#company_id').val() != '') {
            var companyIds = $('#company_id').val();
        } else if($('#company_id').val() != '') {
             var companyIds = $('#company_id').val();
        } else {
            var companyIds = $('#companiesId').val();
        }
    } else {
        var companyIds = ($('#company_id').val() != '') ? $('#company_id').val() : null;
    }
    var days = $('#dayFilter').val();
    options = new Object({days: days});
    $.ajax({
        url: urls.booking,
        type: 'POST',
        dataType: 'json',
        data: {
            tier: tier,
            companyId: companyIds,
            options: options
        }
    }).done(function(data) {
        loadBookingTabData(data, tier);
    }).fail(function(error) {
        toastr.error('Failed to load Booking tab data.');
    })
}
/*
 * Load Booking Tab Data Tier by Tier
 */
function loadBookingTabData(data, tier) {
    switch (tier) {
        case 1:
            if (data.bookingData) {
                $('#total-upcoming-events').html(data.bookingData.upcomming_event);
            }
            if (data.bookingData) {
                $('#today-upcoming-events').html(data.todaysbookingData.upcomming_event);
            }
            if (data.bookingData) {
                $('#sevendays-upcoming-events').html(data.last7daysbookingData.upcomming_event);
            }
            if (data.bookingData) {
                $('#thirtydays-upcoming-events').html(data.last30daysbookingData.upcomming_event);
            }
            break;
        case 2:
            if (data.bookingDataEventRevenue) {
                $('#count-completed-event-revenue').html('<i class="fal fa-calendar-check me-2"></i> ' + data.bookingDataEventRevenue.countOfCompleted);
                $('#total-completed-event-revenue').html('€ ' + data.bookingDataEventRevenue.sumOfCompleted);
                $('#count-booked-event-revenue').html('<i class="fal fa-calendar-minus me-2"></i> ' + data.bookingDataEventRevenue.countOfBooked);
                $('#count-cancelled-event-revenue').html('<i class="fal fa-calendar-times me-2"></i> ' + data.bookingDataEventRevenue.countOfCancelled);
                $('#total-booked-event-revenue').html('€ ' + data.bookingDataEventRevenue.sumOfBooked);
                $('#total-cancelled-event-revenue').html('€ ' + data.bookingDataEventRevenue.sumOfCancelled);
            }
            break;
        case 3:
            if (data.length > 0) {
                $('#todays-event-calender-parent').show();
                var template = $('#today-event-calender-list-template').text().trim();
                html = "";
                $(data).each(function(index, value) {
                    html += template.replace(":day", value.day).replace(":companyName", value.companyName).replace(":eventName", value.name).replace(":bookingDate", value.displayDate).replace(":bookingStarttime", value.startTime).replace(":participantsUsers", value.participants_users).replace(":eventImageName", value.mediaImage.url);
                });
                // $('#todays-event-calender-list').append(html);
                todayeventcalenderlistslider.trigger('replace.owl.carousel', html).trigger('refresh.owl.carousel');
            } else {
                $('#todays-event-calender-parent').hide();
            }
            break;
        default:
            toastr.error('Something went wrong.!');
            break;
    }
}
/*
 * Code for 7/30/90 Filter for Events Revenue
 */
$(document).on('change', '#dayFilter', function(e) {
    var _this = $(this);
    var days = _this.val();
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.days = days;
    bookingTabAjaxCall(2, options);
});
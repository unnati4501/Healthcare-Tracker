var events = {
    skillTrendChartChart: {
        object: '',
        config: {
            type: "horizontalBar",
            data: {
                labels: [],
                datasets: [{
                    label: "Completed Events",
                    backgroundColor: ["#8393E2", "#5261AC", "#6174D2", "#475497", "#A3B2F8"],
                    data: []
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        // categoryPercentage: 0.3,
                        barThickness: 13,
                        maxBarThickness: 15,
                        // gridLines: {
                        //     display: false
                        // },
                        scaleLabel: {
                            display: true,
                            fontFamily: "Montserrat, sans-serif",
                            fontStyle: "500",
                            fontColor: '#675C53',
                            fontSize: 14
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 4
                        }
                    }],
                    yAxes: [{
                        // categoryPercentage: 0.3,
                        barThickness: 13,
                        maxBarThickness: 15,
                        gridLines: {
                            display: false
                        },
                        scaleLabel: {
                            display: false
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 2
                        }
                    }]
                },
                maintainAspectRatio: false,
                legend: {
                    display: false
                }
            }
        }
    },
};
var todayeventcalenderlistslider = $('#eventCalendarCarousel').owlCarousel({
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
      768: {
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
    var roleSlug = $("#roleSlug").val();
    var pattern = /^[0-9,]*$/g;

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

    if(roleSlug!= 'super_admin' && roleSlug != 'wellbeing_specialist' && roleSlug!= 'wellbeing_team_lead' && roleSlug!= 'counsellor'){
        companyIds = companyIds.match(pattern) ? companyIds : null ;
    }else{
        companyIds = $.isNumeric(companyIds) ? companyIds : null;
    }
    var days = $('#dayFilter li.active').data('value');
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
                $('#today-events').html(data.bookingData.todayEvent);
                $('#upcoming-events').html(data.bookingData.upcomingEvent);
                $('#completed-events').html(data.bookingData.completedEvent);
                $('#cancelled-events').html(data.bookingData.cancelledEvent);
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
        case 4:
            // initialize Skill Trend chart with blank data
            if (typeof events.skillTrendChartChart.object != "object") {
                events.skillTrendChartChart.object = new Chart($('#eventSkillTrendChart'), events.skillTrendChartChart.config);
            }
            events.skillTrendChartChart.config.data.labels = data.skillTrend ? data.skillTrend.categoriesSkill : [];
            events.skillTrendChartChart.config.data.datasets[0].data = data.skillTrend ? data.skillTrend.totalAssignUser : [];
            events.skillTrendChartChart.config.data.datasets[0].backgroundColor = data.skillTrend ? poolColors(data.skillTrend.categoriesSkill.length) : [];
            events.skillTrendChartChart.object.update();
            break;
        default:
            toastr.error('Something went wrong.!');
            break;
    }
}
/*
 * Code for 7/30/90 Filter for Events Revenue
 */
$(document).on('click', '#dayFilter li', function(e) {
    var _this = $(this);
    var days = _this.data('value');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    $('#dayFilter li').removeClass('active');
    _this.addClass('active');
    options.days = days;
    bookingTabAjaxCall(2, options);
});
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    timeout: (60000 * 20)
});
CronofyElements.DateTimePicker({
    element_token: data.token,
    target_id: "cronofy-date-time-picker",
    availability_rule_id: data.availableRuleId,
    data_center: data.dataCenter,
    event            : {
        event_id: data.eventId,
        summary: data.name,
        tzid: data.timezone,
    },
    availability_query: {
        participants:[
            {
                members:[
                    {
                        sub: data.subId,
                    }
                ],
                required: "all"
            }
        ],
        required_duration: { minutes: data.duration },
        query_periods: data.queryPeriods
    },
    config: {
      duration: data.duration
    },
    styles: {
        colors: {
            available: data.availableColor,
            unavailable: data.unavailableColor,
            buttonActive: data.buttonActive,
            buttonTextHover: data.buttonTextHover,
            buttonHover: data.buttonHover,
            buttonText: data.buttonText,
            buttonConfirm: data.buttonConfirm,
            buttonActiveText: data.buttonActiveText
        },
        prefix: ""
    },
    callback: notification => {
        $(".page-loader-wrapper").fadeIn();
        $('.error').remove();
        if (notification.notification.type == 'error') {
            $(".page-loader-wrapper").fadeOut();
            var payload = {
                wsId: data.wsId,
                companyId: data.companyId,
                errorMessage: notification.notification.message.toString(),
            };
            $.ajax({
                url: ajaxUrl.cronofyException,
                type: 'POST',
                dataType: 'json',
                data: payload
            })
            .done(function(data) {
                $('#cronofy-date-time-picker').after('<div class="error error-message text-center"><h4>' + errormessage.uielementnotfound + '</h4></div>')
            });
        } else {
            var requestPayload = {
                eventId: data.schedulingId,
                schedulingId: data.schedulingId,
                wsId: data.wsId,
                company: data.companyId,
                scheduleId: data.scheduleId,
                name: data.name,
                reschedule: data.reschedule,
                notification: notification,
                payload: data.payload,
                eventbooking_id: data.eventbooking_id,
                eventbookinglogsId: data.eventbookinglogsId,
            };
            $.ajax({
                url: ajaxUrl.createEventSlot,
                type: 'POST',
                dataType: 'json',
                data: requestPayload
            })
            .done(function(data) {
                if (data.status) {
                    toastr.success('', data.data, {
                        timeOut: 1000,
                        preventDuplicates: true,
                        positionClass: 'toast-top-center',
                        // Redirect
                        onHidden: function() {
                            window.location.href = url.cronofyIndex;
                        }
                    });
                } else {
                    toastr.error('', data.data, {
                        timeOut: 1000,
                        preventDuplicates: true,
                        positionClass: 'toast-top-center',
                        // Redirect
                        onHidden: function() {
                            window.location.href = url.cronofyIndex;
                        }
                    });
                }
            });
        }
    }
});
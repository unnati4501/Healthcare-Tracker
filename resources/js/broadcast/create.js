var start = new Date();
start.setMinutes(start.getMinutes() + 15);
end = new Date(new Date().setMonth(start.getMonth() + 3));
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('change', '#instant_broadcast', function(e) {
        var checked = $(this).is(":checked");
        if(checked) {
            $('#scheduled_wrapper').addClass('d-none');
        } else {
            $('#scheduled_wrapper').removeClass('d-none');
        }
    });

    $("#schedule_date_time").datetimepicker({
        format: 'yyyy-mm-dd hh:ii:00',
        startDate: start,
        endDate: end,
        autoclose: true,
        fontAwesome:true,
        minuteStep: 15,
        todayHighlight: true,
    }).on('changeDate', function () {
        $("#schedule_date_time").valid();
    });

    $(document).on('change', '#group_type', function(e) {
        var group = $('#group_type').val();
        if(group != "") {
            $.ajax({
                url: url.dataTable,
                type: 'POST',
                dataType: 'html',
                data: {
                    type: $('#group_type').val(),
                },
            })
            .done(function(data) {
                $('#group').html(data).removeAttr('disabled').select2('destroy').select2();
            })
            .fail(function(error) {
                toastr.error('Failed to load groups, please try again');
            });
        } else {

            $('#group').html('').attr('disabled', 'disabled').select2('destroy').select2();
        }
    });

    $('#broadcastMessageAdd').ajaxForm({
        beforeSend: function() {
            $('.page-loader-wrapper').show();
        },
        success: function(data) {
            $('.progress-loader-wrapper').hide();
            if(data.status && data.status == 1) {
                window.location.replace(url.redirect);
            }
        },
        error: function(data) {
            toastr.error(data?.responseJSON?.message || message.something_wrong_try_again);
        },
        complete: function(xhr) {
            $('.page-loader-wrapper').hide();
            $('#zevo_submit_btn').removeAttr('disabled');
        }
    });
});
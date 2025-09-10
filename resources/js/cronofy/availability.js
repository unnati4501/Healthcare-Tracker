$(document).ready(function() {
	$('#availabilityAdd').ajaxForm({
        beforeSend: function() {
            $('.page-loader-wrapper').show();
        },
        success: function(data) {
            if(data.status) {
                $('.page-loader-wrapper').hide();
                window.location.replace(url.dashboad);
                toastr.success(message.availability_message);
            }
        },
        error: function(data) {
            if (data?.responseJSON?.errors?.slots != undefined) {
                toastr.error(data?.responseJSON?.errors?.slots);
            }
            if (data?.responseJSON?.errors?.presenter_slots != undefined) {
                toastr.error(data?.responseJSON?.errors?.presenter_slots);
            }
            if (data?.responseJSON?.errors?.presenter_slots == undefined && data?.responseJSON?.errors?.slots == undefined) {
                toastr.error(message.something_wrong);
            }
        },
        complete: function(xhr) {
            $('.page-loader-wrapper').hide();
        }
    });
});
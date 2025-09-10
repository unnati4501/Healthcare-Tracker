$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#bookedEvents').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            type : 'POST',
            url: urls.getBookedEvents,
            data: {
                name: $('#bookeTabdEventName').val(),
                presenter: $('#bookedTabEventPresenter').val(),
                company: $('#bookedTabEventCompany').val(),
                category: $('#bookedTabEventCategory').val(),
                eventStatus: $('#eventStatus').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'logo',
            name: 'logo'
        }, {
            data: 'event_name',
            name: 'event_name'
        }, {
            data: 'company_name',
            name: 'company_name',
        }, {
            data: 'subcategory_name',
            name: 'subcategory_name',
        }, {
            data: 'presenter',
            name: 'presenter',
            visible: visibleHealthCoach && visibleWellbeingSpecialist
        }, {
            data: 'duration',
            name: 'duration',
            class: 'text-center',
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format("MMM DD, YYYY") + '<br />' + moment.utc(data).tz(timezone).format("hh:mm A") + " - " + moment.utc(row.end_time).tz(timezone).format("hh:mm A");
            }
        }, {
            data: 'users_count',
            name: 'users_count',
            class: 'text-center',
            visible: visibleCompany
        },
        {
            data: 'eventStatus', name: 'eventStatus', class: 'text-center',
            render: function (data, type, row) {
                if (row.eventStatus == 'Booked' || row.eventStatus == 'Paused') {
                    return `<span class="text-warning">${row.eventStatus}</span>`;
                } else if (row.eventStatus == 'Completed') {
                    return `<span class="text-success">${row.eventStatus}</span>`;
                }else {
                    return `<span class="text-danger">${row.eventStatus}</span>`;
                }
            }
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false,
            visible: visibleHealthCoach
        }],
        paging: true,
        pageLength: dataTableConf.pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#exportBookingsbtn").hide();
            }else {
                $("#exportBookingsbtn").show();
            }
        },
        language: {
            paginate: {
                previous: dataTableConf.pagination.previous,
                next: dataTableConf.pagination.next,
            }
        }
    });

    $(document).on('click', '#exportBookingsbtn', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        $('#email').val(loginemail).removeClass('error');
        $('#event_pop').val($('#bookeTabdEventName').val());
        $('#event_company_pop').val($('#bookedTabEventCompany').val());
        $('#event_category_pop').val($('#bookedTabEventCategory').val());
        $('#event_status_pop').val($('#eventStatus').val());
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $('#exportBookings').validate({
        errorClass: 'error text-danger',
        errorElement: 'span',
        highlight: function(element, errorClass, validClass) {
            $('span#emailError').addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function(element, errorClass, validClass) {
            $('span#emailError').removeClass(errorClass).addClass(validClass);
        },
        rules: {
            email: {
                email: true,
                required: true
            }
        }
    });

    $('#exportBookings').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#exportBookings .card-footer button, .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#exportBookings .card-footer button, .card-footer a').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#exportBookings .card-footer button, .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#exportBookings .card-footer button, .card-footer a').removeAttr('disabled');
        }
    });

    // reset cancel event reason from once popup is closed
    $(document).on('click', '#cancel-book-event', function(e) {
        $('#cancel_reason').val('').removeClass('is-invalid is-valid');
        $('#cancel_reason-error').remove();
        $('#cancel-event-model-box').data("id", $(this).data('id'));
        $('#cancel-event-model-box').modal('show');
    });

    // cancel event on yes button of cancel modal
    $(document).on('click', '#event-cancel-model-box-confirm', function(e) {
        
        var objectId = $('#cancel-event-model-box').data("id");
        var cancelReason = $('#cancel_reason').val();

        if (cancelReason.length > 100) {
            toastr.error('The reason field may not be greater than 100 character limit');
            return false;
        }

        $('.page-loader-wrapper').show();
        $.ajax({
            url: urls.cancelEvents,
            type: 'POST',
            dataType: 'json',
            data: {
                event: objectId,
                cancel_reason: $('#cancel_reason').val(),
                referrer: 'bookingPage'
            },
        }).done(function(data) {
            $('.page-loader-wrapper').hide();
            if (data.cancelled == true) {
                location.reload();
            } else {
                toastr.error((data.message || 'Failed to cancel an event, Please try again!'));
            }
            $('#cancel-event-model-box').modal('hide');
        }).fail(function(error) {
            $('#cancel-event-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
            toastr.error((error?.responseJSON?.message || `Failed to cancel an event.`));
        }).always(function() {
            $('#cancel-event-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
            $('#cancel-event-model-box .modal-header, #cancel-event-model-box .modal-footer').css('pointer-events', '');
            $('#event-cancel-model-box-confirm').html(`Yes`);
        });
    });

    // Copy booked events
    $("#cloneHtml").hide();
    var clipboardhtml = "";
    $(document).on('click', '.clone', function(t) {
        $("#cloneHtml").show();
        $("#cloneHtml .additional_notes, .video_link").hide();
        var id = $(this).data('id');
        $("#cloneHtml #event_name").text($(this).data('eventname'));
        $("#cloneHtml #booking_date").text($(this).data('eventdate')+" UTC");
        $("#cloneHtml #duration").text($(this).data('duration')+" Minutes");
        $("#cloneHtml #presenter").text($(this).data('presenter'));
        if ($(this).data('notes').length > 0){
            var additionalNotes = $(this).data('notes');
            $("#cloneHtml #additional_notes").text($(additionalNotes).text());
            $("#cloneHtml .additional_notes").show();
        }
        if ($(this).data('eventtype') == 1) {
            $("#cloneHtml #video_link").text($(this).data('videolink'));
            $("#cloneHtml .video_link").show();
        }

        var body = document.body,
        range, sel;
        if (document.createRange && window.getSelection) {
            range = document.createRange();
            sel = window.getSelection();
            sel.removeAllRanges();
            range.selectNodeContents(cloneHtml);
            sel.addRange(range);
        }
        document.execCommand("Copy");
        toastr.success(labels.eventDetailsCopied);
        $("#cloneHtml").hide();
    });
});
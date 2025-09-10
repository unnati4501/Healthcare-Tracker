$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.dateranges').datepicker({
        format: "mm/dd/yyyy",
        todayHighlight: false,
        autoclose: true,
    });

    $('.daterangesFromExportModel').datepicker({
        format: "yyyy-mm-dd",
        todayHighlight: false,
        autoclose: true,
        endDate: new Date(),
        clearBtn: true,
    });

    $('#detailedReportManagement').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            type: 'POST',
            url: url.dataTable,
            data: {
                presenter: $("#presenter").val(),
                fromdate: $("#fromdate").val(),
                todate: $("#todate").val(),
                status: $("#status").val(),
                complementary: $("#complementary").val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'event_name',
            name: 'event_name',
        }, {
            data: 'presenter',
            name: 'presenter',
        }, {
            data: 'date_time',
            name: 'date_time',
            className: 'text-center',
            render: function (data, type, row) {
                return moment.utc(data).tz(timezone).format("MMM DD, YYYY")  + '<br />' + moment.utc(data).tz(timezone).format("hh:mm A") + " - " + moment.utc(row.end_time).tz(timezone).format("hh:mm A");
            }
        }, {
            data: 'created_by',
            name: 'created_by',
        }, {
            data: 'location_type',
            name: 'location_type',
        }, {
            data: 'participants',
            name: 'participants',
            className: 'text-center',
        }, {
            data: 'fees',
            name: 'fees',
            className: 'text-center',
            render: function (data, type, row) {
                var iData = $.fn.dataTable.render.number(',').display(data);
                return '€ ' + iData;
            }
        }, {
            data: 'is_complementary',
            name: 'is_complementary',
        }, {
            data: 'status',
            name: 'status',
            className: 'text-center',
        }, {
            data: 'actions',
            name: 'actions',
            className: 'text-center',
        }, {
            data: 'cancelled_by_name',
            name: 'cancelled_by_name',
            className: 'hidden'
        }, {
            data: 'cancelled_on',
            name: 'cancelled_on',
            className: 'hidden'
        }, {
            data: 'cancel_reason',
            name: 'cancel_reason',
            className: 'hidden'
        }],
        paging: true,
        pageLength: parseInt(pagination.value),
        lengthChange: true,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        searching: false,
        ordering: true,
        searching: false,
        order: [],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: -1,
            visible: false
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        },
        dom: (data.isExportButton == true) ? 'lBfrtip' : '',
        async:false,
        buttons: [],
        stateSave: false,
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#exportBookingHistoryReport").hide();
            }else {
                $("#exportBookingHistoryReport").show();
            }
        },
    });

    // show cancelled evetn details on view cancel details button click
    $(document).on('click', '.view-cancel-event-details', function(e) {
        $(".page-loader-wrapper").fadeIn();
        var bid = $(this).data('bid');
        $.ajax({
            type: 'POST',
            url: url.cancelUrl.replace(":bid", bid),
            dataType: 'json',
        }).done(function(data) {
            if (data?.status) {
                $('#cancelled_by').html(data?.cancelled_by);
                $('#cancelled_at').html(data?.cancelled_at);
                $('#cancelation_reason').html(data?.cancelation_reason);
                $('#cancel-event-details-model-box').modal('show');
            } else {
                toastr.error((data.message || message.failed_to_load));
            }
        }).fail(function(error) {
            toastr.error((error?.responseJSON?.message || message.failed_to_load));
        }).always(function() {
            $(".page-loader-wrapper").fadeOut();
        });
    });

    $(document).on('click', '#exportBookingHistoryReport', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        $('#email').val(loginemail).removeClass('error');
        $('#queryString').val(JSON.stringify(get_query()));
        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $("#exportNpsReport").attr('action', url.exportBookingHistoryReportUrl);
        $('.loadingMsg').remove();
        $('#export-model-box-confirm').prop('disabled', false);
        $('#exportNpsReportMsg').hide();
        $('#exportNps').show();
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $('#exportNpsReport').validate({
        errorClass: 'error text-danger',
        errorElement: 'span',
        highlight: function(element, errorClass, validClass) {
            $('span#email-error').addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function(element, errorClass, validClass) {
            $('span#email-error').removeClass(errorClass).addClass(validClass);
        },
        rules: {
            email: {
                email: true,
                required: true
            }
        }
    });
    $('#exportNpsReport').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
        }
    });
    
});

function get_query(){
    $('.daterangesFromExportModel').show();
    var url = document.location.href;
    var qs = url.substring(url.indexOf('?') + 1).split('&');
    for(var i = 0, result = {}; i < qs.length; i++){
        qs[i] = qs[i].split('=');
        if(qs[i][1] != undefined ){
            $('.daterangesFromExportModel').hide();
        }
        result[qs[i][0]] = decodeURIComponent(qs[i][1]);
    }
    return result;
}
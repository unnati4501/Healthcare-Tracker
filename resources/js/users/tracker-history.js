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
        startDate: new Date(firstSyncDate),
        endDate: new Date(),
    });

    $('.daterangesFromExportModel').datepicker({
        format: "yyyy-mm-dd",
        todayHighlight: false,
        autoclose: true,
        endDate: new Date(),
        clearBtn: true,
    });

    $('#trackerhistoryManagement').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            type: 'POST',
            url: datatable,
            data: {
                trackername: $("#trackername").val(),
                fromdate: $("#fromdate").val(),
                todate: $("#todate").val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'tracker_name',
            name: 'tracker_name',
        }, {
            data: 'tracker_change_date_time',
            name: 'tracker_change_date_time',
        }],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#trackerHistoryExport").hide();
            }else {
                $("#trackerHistoryExport").show();
            }
        },
        paging: true,
        pageLength: parseInt(pagination.value),
        lengthChange: true,
        lengthMenu: [
            [25, 50, 100],
            [25, 50, 100]
        ],
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
        buttons: [],
        stateSave: false,
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
            lengthMenu: "Entries per page _MENU_",
        },
    });

    $(document).on('click', '#trackerHistoryExport', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        $('#email').val(loginemail).removeClass('error');
        $('#queryString').val(JSON.stringify(get_query()));
        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $("#exportNpsReport").attr('action', url.trackerHistoryExportUrl);
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
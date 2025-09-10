$(document).ready(function() {
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

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#userManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.getUsersDt,
            data: {
                status: 1,
                recordName: $('#recordName').val(),
                recordEmail: $('#recordEmail').val(),
                recordStatus: $('#status').val(),
                company: $('#company').val(),
                rolename: $('#rolename').val(),
                rolegroup: $('#rolegroup').val(),
                team: $('#team').val(),
                role: $('#role').val(),
                fromDate: $('#fromDate').val(),
                toDate: $('#toDate').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            { data: 'updated_at', name: 'updated_at' , visible: false },
            { data: 'fullName', name: 'fullName',
                render:function(data, type, row) {
                    return row.fullName;
                }
            },
            { data: 'email', name: 'email' },
            { data: 'roleName', name: 'roleName' },
            { data: 'roleGroup', name: 'roleGroup' },
            { data: 'companyName', name: 'companyName',
                render:function(data, type, row) {
                    return ((data) ? data : "-");
                }
            },
            { data: 'departmentName', name: 'departmentName',
                render:function(data, type, row) {
                    return ((data) ? data : "-");
                }
            },
            { data: 'locationName', name: 'locationName',
                render:function(data, type, row) {
                    return ((data) ? data : "-");
                }
            },
            { data: 'teamName', name: 'teamName',
                render:function(data, type, row) {
                    return ((data) ? data : "-");
                }
            },
            { data: 'created_at', name: 'registrationDate', 
                render: function(data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            }
        ],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#userRegistrationExport").hide();
            }else {
                $("#userRegistrationExport").show();
            }
        },
        lengthMenu: [ [25, 50, 100], [25, 50, 100] ],
        pageLength: 25,
        lengthChange: true,
        searching: false,
        order: [],
        info: true,
        autoWidth: true,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            sLengthMenu: "Show _MENU_", // remove entries text
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        buttons: []
    });

    $(document).on('click', '#userRegistrationExport', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        $('#email').val(loginemail).removeClass('error');
        $('#queryString').val(JSON.stringify(get_query()));
        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $("#exportNpsReport").attr('action', url.userRegistrationExportUrl);
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
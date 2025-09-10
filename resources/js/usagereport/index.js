$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    $('#usageReport').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.dataTable,
            data: {
                company: $('#company').val(),
                location: $('#location').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            {data: 'company_name', name: 'company_name'},
            {data: 'location', name: 'location'},
            {data: 'registed_user', name: 'registed_user', className: 'text-center'},
            {data: 'active_7_days', name: 'active_7_days', className: 'text-center'},
            {data: 'active_30_days', name: 'active_30_days', className: 'text-center'}
        ],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#exportUsageReportbtn").hide();
            }else {
                $("#exportUsageReportbtn").show();
            }
        },
        paging: true,
        pageLength: parseInt(pagination.value),
        lengthChange: true,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
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
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        }
    });

    $(document).on('change.select2', '#company', function(e) {
        var company_id = $(this).val(),
            urlLocation = url.locUrl.replace(':id', company_id),
            options = '';
        $('#location').empty();
        $.get(urlLocation, function(data) {
            if (data && data.code == 200) {
                $.each(data.result, function(index, location) {
                    options += `<option value='${location.id}'>${location.name}</option>`;
                });
                $('#location').attr('disabled', false).empty().append(options).val('');
            } else {
                $('#location').attr('disabled', true).empty();
            }
        }, 'json');
    });
    
    $(document).on('click', '#exportUsageReportbtn', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        $('#email').val(loginemail).removeClass('error');
        $('#company_popup').val($('#company').val());
        $('#location_popup').val($('#location').val());
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $('#usagereportform').validate({
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
    $('#usagereportform').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#usagereportform .card-footer button, .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#usagereportform .card-footer button, .card-footer a').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#usagereportform .card-footer button, .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#usagereportform .card-footer button, .card-footer a').removeAttr('disabled');
        }
    });
});
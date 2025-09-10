$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    $('.dateranges').datepicker({
        format: "mm/dd/yyyy",
        todayHighlight: false,
        autoclose: true,
    });
    $('#occupationalHealthReport').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.dataTable,
            data: {
                company: $('#company').val(),
                fromDate: $('#fromDate').val(),
                toDate: $('#toDate').val(),
                userName: $('#userName').val(),
                wellbeingSpecialist: $('#wellbeingSpecialist').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            {name: 'id', data: null,render: (data, type, row, meta) => meta.row+1, sortable: false},
            {data: 'user_name', name: 'user_name'},
            {data: 'user_email', name: 'user_email'},
            {data: 'company_name', name: 'company_name'},
            {data: 'log_date', name: 'log_date'},
            {data: 'is_confirmed', name: 'is_confirmed'},
            {data: 'confirmation_date', name: 'confirmation_date'},
            {data: 'note', name: 'note', sortable: false},
            {data: 'is_attended', name: 'is_attended'},
            {data: 'ws_name', name: 'ws_name'},
            {data: 'referred_by', name: 'referred_by'}
        ],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#exportOccupationalHealthReportbtn").hide();
            }else {
                $("#exportOccupationalHealthReportbtn").show();
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

    //Display the model popup for view client notes
    $(document).on('click', '.diplay-note-model', function(event) {
        var id = $(this).attr('id');
        var content = $(this).attr('data-content');
        $('#diplay-note-model').attr("data-id", id);
        $('#modal_body').html(content);
        $('#diplay-note-model').show();
    });
    
    $(document).on('click', '#exportOccupationalHealthReportbtn', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        $('#email').val(loginemail).removeClass('error');
        $('#company_popup').val($('#company').val());
        $('#fromdate_popup').val($('#fromDate').val());
        $('#todate_popup').val($('#toDate').val());
        $('#wellbeing_specialist_popup').val($('#wellbeingSpecialist').val());
        $('#username_popup').val($('#userName').val());
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $('#occupationalHealthReportForm').validate({
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
    $('#occupationalHealthReportForm').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#occupationalHealthReportForm .card-footer button, .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#occupationalHealthReportForm .card-footer button, .card-footer a').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#occupationalHealthReportForm .card-footer button, .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#occupationalHealthReportForm .card-footer button, .card-footer a').removeAttr('disabled');
        }
    });
});
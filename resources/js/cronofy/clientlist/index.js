$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#clientListManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                getQueryString: window.location.search,
                name: $('#name').val(),
                email: $('#email').val(),
                company: $('#company').val(),
                location: $('#location').val(),
                ws: $('#ws').val(),
            },
        },
        columns: [{
            data: 'client_name',
            name: 'client_name',
        }, {
            data: 'email',
            name: 'email',
        }, {
            data: 'company_name',
            name: 'company_name',
        }, {
            data: 'location_name',
            name: 'location_name',
        }, {
            data: 'completed_session',
            name: 'completed_session',
            className: 'text-center'
        }, {
            data: 'upcoming',
            name: 'upcoming',
            className: 'text-center'
        }, {
            data: 'cancelled_sessions',
            name: 'cancelled_sessions',
            className: 'text-center'
        }, {
            data: 'short_cancel',
            name: 'short_cancel',
            className: 'text-center'
        }, {
            data: 'no_show',
            name: 'no_show',
            className: 'text-center'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false,
            visible: (role != 'super_admin')
        }],
        paging: true,
        pageLength: pagination.value,
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
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
            lengthMenu: pagination.entry_per_page + " _MENU_",
            emptyTable: message.noDataExists,
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        drawCallback: function(settings) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#exportClientList").hide();
            }else {
                $("#exportClientList").show();
            }
        }
    });
    $(document).on('change', '#company', function(e) {
        $('#location').prop({'disabled': false});
        var __companyid = $(this).val();
        var _token = $('input[name="_token"]').val();
        var getLocationByCompany = url.getLocationByCompany.replace(':id', __companyid);
        $.ajax({
            url: getLocationByCompany,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(result) {
                $('#location').empty().select2("val", "");
                if (result.result) {
                    $.each(result.locations, function(key, value) {
                        $('#location').append('<option value="' + key + '">' + value + '</option>');
                    });
                    $('#location').val('').select2();
                }

            }
        })
    });
    $(document).on('click', '#exportClientList', function(t) {
        var userActivityForm = $( ".userActivityForm" ).serialize();
        var exportConfirmModalBox = '#export-clientlist-model-box';
        $(exportConfirmModalBox).find('#email').val(loginemail).removeClass('error');
        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $('.loadingMsg').remove();
        $('#export-model-box-confirm').prop('disabled', false);
        $(exportConfirmModalBox).modal('show');
    });

    $('#exportClient').validate({
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
    $('#exportClientReport').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#exportClientReport .card-footer button, .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#exportClientReport .card-footer button, .card-footer a').removeAttr('disabled');
            $('#export-clientlist-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#exportClientReport .card-footer button, .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#exportClientReport .card-footer button, .card-footer a').removeAttr('disabled');
        }
    });
});
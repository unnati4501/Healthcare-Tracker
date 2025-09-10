$(document).ready(function() {
    var bar = $('#mainProgrssbar'),
        percent = $('#mainProgrssbar .progpercent'),
        start = new Date(new Date().setDate(-15)),
        end = new Date(new Date().setDate(15));
    $('#exportIntercompanychallenge').validate({
        rules: {
            email: {
                email: true
            },
            start_date: {
                required: true
            },
            end_date: {
                required: true
            }
        },
        messages: {
            start_date: "From date field is required.",
            end_date: "To date field is required.",
        }
    });
    $('#exportIntercompanychallenge').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#exportIntercompanychallenge .card-footer button, #exportIntercompanychallenge .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#exportIntercompanychallenge .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#exportIntercompanychallenge .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#exportIntercompanychallenge .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
        }
    });
    $('#challengeStatus').select2({
        placeholder: "Select challenge status",
        allowClear: true
    });
    $('#challengeCategory').select2({
        placeholder: "Select challenge category",
        allowClear: true
    });
    $('#recursive').select2({
        placeholder: "Is Recursive?",
        allowClear: true
    });
    $('#challengeManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                challengeStatus: $('#challengeStatus').val(),
                recursive: $('#recursive').val(),
                challengeName: $('#challengeName').val(),
                challengeCategory: $('#challengeCategory').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false
        }, {
            data: 'logo',
            name: 'logo',
            searchable: false,
            sortable: false
        }, {
            data: 'title',
            name: 'title'
        }, {
            data: 'challengecategory',
            name: 'challengecategory'
        }, {
            data: 'target',
            name: 'target'
        }, {
            data: 'start_date',
            name: 'start_date',
            render: function(data, type, row) {
                return `${moment.utc(row.start_date).tz(timezone).format(date_format)} - ${moment.utc(row.end_date).tz(timezone).format(date_format)}`;
            }
        }, {
            data: 'recurring',
            name: 'recurring'
        }, {
            data: 'challengeStatus',
            name: 'challengeStatus'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [
            [0, 'desc']
        ],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }, {
            targets: 6,
            visible: currentRoute == 'challenges' ? true : false,
        }, {
            targets: [8],
            className: 'text-center',
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        stateSave: false
    });
    $("#start_date").datepicker({
        format: 'yyyy-mm-dd',
        minDate: new Date(),
        maxDate: new Date(),
        autoclose: true,
        fontAwesome: true,
        todayHighlight: false,
        pickerPosition: "top-right",
        setDate: new Date()
    })
    $("#end_date").datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        fontAwesome: true,
        todayHighlight: false,
        pickerPosition: "top-right",
        setDate: new Date()
    })
    $(document).on('click', '#challengeDelete', function(t) {
        var deleteConfirmModalBox = '#delete-model-box';
        $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
        $(deleteConfirmModalBox).modal('show');
    });
    $(document).on('click', '#challengeCancel', function(t) {
        var cancelConfirmModalBox = '#cancel-model-box';
        $(cancelConfirmModalBox).attr("data-id", $(this).data('id'));
        $(cancelConfirmModalBox).modal('show');
    });
    $(document).on('keyup', '#cancel_reason', function(t) {
        var __reason = $('#cancel_reason').val();
        $('.cancel_reason').hide();
        $('#cancel_reason').css('border', '1px solid #e0e1e6');
        if (__reason.length <= 0) {
            $('#cancel_reason-error').show();
            $('#cancel_reason').css('border', '1px solid red');
            return;
        } else if(__reason.length > 500) {
            $('#cancel_reason-error-max-character').show();
            $('#cancel_reason').css('border', '1px solid red');
            return;
        }
        return;
    });
    $(document).on('click', '#cancel-model-box-confirm', function(t) {
        var id = $('#cancel-model-box').data('id');
        var cancelConfirmModalBox = '#cancel-model-box';
        var __reason = $('#cancel_reason').val();
        $('.cancel_reason').hide();
        $('#cancel_reason').css('border', '1px solid #e0e1e6');
        if (__reason.length <= 0) {
            $('#cancel_reason-error').show();
            $('#cancel_reason').css('border', '1px solid red');
            return;
        } else if(__reason.length > 500) {
            $('#cancel_reason-error-max-character').show();
            $('#cancel_reason').css('border', '1px solid red');
            return;
        }
        $('.page-loader-wrapper').show();
        $.ajax({
            type: 'POST',
            url: url.cancelChallenge + '/' + id,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                reason: __reason,
            },
            success: function(data) {
                $('#challengeManagment').DataTable().ajax.reload(null, false);
                if (data['cancellation']) {
                    $(cancelConfirmModalBox).modal('hide');
                    $('.page-loader-wrapper').hide();
                    toastr.success(message.cancellation);
                } else {
                    toastr.error(message.somethingWentWrong);
                }

            },
            error: function(data) {}
        });
    });
    $(document).on('click', '#challengeExport', function(t) {
        var id = $(this).data('id');
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        $('#email').val(loginemail).removeClass('error');
        $('#start_date,#end_date').removeClass('error');
        $('.error').remove();
        if (new Date(__endDate) > new Date()) {
            __endDate = new Date();
        }
        var start = __startDate;
        var end = __endDate;
        __startDate = new Date(new Date(__startDate).setDate(new Date(__startDate).getDate() - startDate));
        __endDate = new Date(new Date(__endDate).setDate(new Date(__endDate).getDate() + parseInt(endDate)));
        if (new Date(__endDate) > new Date()) {
            __endDate = new Date();
        }
        $("#start_date,#end_date").datepicker("destroy");
        $("#start_date").datepicker({
            dateFormat: "yyyy-mm-dd",
        });
        $("#end_date").datepicker({
            dateFormat: "yyyy-mm-dd",
        });
        $("#start_date").datepicker('setStartDate', moment(__startDate).format('YYYY-MM-D'));
        $("#start_date").datepicker('setEndDate', moment(__endDate).format('YYYY-MM-D'));
        $("#end_date").datepicker('setStartDate', moment(__startDate).format('YYYY-MM-D'));
        $("#end_date").datepicker('setEndDate', moment(__endDate).format('YYYY-MM-D'));
        $("#start_date").datepicker("update", moment(start).format('YYYY-MM-D'));
        $("#end_date").datepicker("update", moment(end).format('YYYY-MM-D'));
        $("#start_date,#end_date").datepicker("refresh");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'GET',
            url: url.exportHistory + '/' + id,
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function(data) {
                if (data == 1) {
                    $('#exportChallenge').hide();
                    $('#exportChallengeMsg').show();
                    $(exportConfirmModalBox).modal('show');
                } else {
                    $('.loadingMsg').remove();
                    $('#challengeId').after('<div class="loadingMsg" style="text-align: center;color: red;">Data Loading... Please wait</div>');
                    $('#export-model-box-confirm').prop('disabled', true);
                    $('#exportChallengeMsg').hide();
                    $('#exportChallenge').show();
                    $('#challengeId').val(id);
                    $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
                    $(exportConfirmModalBox).modal('show');
                }
                //Set Accurate Data in Intercompany challenge
                $.ajax({
                    type: 'GET',
                    url: url.setAccurateData + '/' + id,
                    data: null,
                    crossDomain: true,
                    cache: false,
                    contentType: 'json',
                    success: function(data) {
                        $('.loadingMsg').remove();
                        $('#export-model-box-confirm').prop('disabled', false);
                    },
                    error: function(data) {}
                });
            },
            error: function(data) {}
        });
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var deleteConfirmModalBox = '#delete-model-box';
        var objectId = $(deleteConfirmModalBox).attr("data-id");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'DELETE',
            url: url.delete + '/' + objectId,
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function(data) {
                $('#challengeManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(message.deleted);
                } else if (data['deleted'] == 'use') {
                    toastr.error(message.inUse);
                } else {
                    toastr.error(message.somethingWentWrong);
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function(data) {
                $('#challengeManagment').DataTable().ajax.reload(null, false);
                toastr.error(message.unauthorized);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });
});
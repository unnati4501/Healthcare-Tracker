$(document).ready(function() {
    $('#userlist').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.getMembersList,
            data: {
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'logo',
            name: 'logo'
        }, {
            data: 'participant_name',
            name: 'participant_name'
        }, {
            data: 'points',
            name: 'points'
        }, {
            data: 'rank',
            name: 'rank'
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: true,
        ordering: true,
        order: [
            [3, 'asc']
        ],
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
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false
    });
    $('#teamlist').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.getTeamMembersList,
            data: {
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'logo',
            name: 'logo'
        }, {
            data: 'name',
            name: 'name'
        }, {
            data: 'totalUsers',
            name: 'totalUsers'
        }, {
            data: 'totalPoints',
            name: 'totalPoints'
        }, {
            data: 'rank',
            name: 'rank'
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: true,
        ordering: true,
        order: [
            [3, 'asc']
        ],
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
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false
    });
    $('#companylist').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.getCompanyMembersList,
            data: {
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'logo',
            name: 'logo'
        }, {
            data: 'name',
            name: 'name'
        }, {
            data: 'totalTeams',
            name: 'totalTeams'
        }, {
            data: 'totalUsers',
            name: 'totalUsers'
        }, {
            data: 'totalPoints',
            name: 'totalPoints'
        }, {
            data: 'rank',
            name: 'rank'
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: true,
        ordering: true,
        order: [
            [3, 'asc']
        ],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }, {
            targets: [2, 3],
            visible: !(roleGroup == 'company')
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

    $(document).on('click', '#exportChallengeDetailHistory', function(t) {
        var id = $(this).data('id');
        var totalmembers    = $(this).data('totalmembers');
        var totalteams      = $(this).data('totalteams');
        var totalcompanies  = $(this).data('totalcompanies');
        var exportConfirmModalBox = '#export-model-box';
        $('#email').val(loginemail).removeClass('error');
        $('.start-date, .end-date').hide();
        $('.error').remove();
        $(exportConfirmModalBox).modal('show');
        $(".modal-title").html('Export Challenge Leaderboard Data');
        $("#exportIntercompanychallenge").attr('action', url.exportChallengeDetails);
        $('.loadingMsg').remove();
        $('#export-model-box-confirm').prop('disabled', false);
        $('#challengeId').val(id);
        $('#totalmembers').val(totalmembers);
        $('#totalteams').val(totalteams);
        $('#totalcompanies').val(totalcompanies);
        $('#exportFrom').val('challenge-detail');
        $('#exportRoute').val(url.routeUrl);
        $('#exportChallengeMsg').hide();
        $('#exportChallenge').show();
    });

    $('#exportIntercompanychallenge').validate({
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
                required: true,
                email: true
            }
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
});
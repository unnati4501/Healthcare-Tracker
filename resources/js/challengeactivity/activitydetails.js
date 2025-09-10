function userDailySummary() {
    var fileName = "report";
    $('#challengeUserActivity').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                logdate: data.logdate,
                user_id: data.user,
                challenge_id: data.challenge,
                type: data.type,
                columnName: data.columnName,
                modelId: data.modelId,
                uom: data.uom,
                challengeStatus: data.challengeStatus,
                trackerFilter: $('#trackerFilter').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            {data: 'tracker', name: 'tracker'},
            {data: 'type', name: 'type'},
            {data: 'achivedValue', name: 'achivedValue'},
            {data: 'points', name: 'points'},
            {data: 'created_at', name: 'created_at'},
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [[25, 50, 100, 1000, 5000], [25, 50, 100, 1000, 5000]],
        searching: false,
        ordering: true,
        searching: false,
        order: [[4, 'desc']],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        },
        stateSave: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        buttons: [],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#challengeUserActivityReport").hide();
            }else {
                $("#challengeUserActivityReport").show();
            }
        },
    });
}

$(document).ready(function() {
    $('.select2').select2();
    userDailySummary();

    $(document).on('click','#userDailySummary',function(){
        userDailySummary();
    });
    $(document).on('click','#resetuserDailySummary',function(){
        window.location.reload();
    });
    $(document).on('click', '#challengeUserActivityReport', function (t) {
        $("#emailError").hide();
        $("#emailError").html("");
        $('#email').val(loginemail);
        var exportConfirmModalBox = '#export-model-box';
        $("#model-title").html("Export Challenge User Activity Report");
        $(".daterangesFromExportModel").hide();
        $("#exportNpsReport").attr('action', url.exportReport);
        $("#export-model-box-confirm").attr('type','button');
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $(document).on('click', '#export-model-box-confirm', function (e) {
        var userEmail = $('#email').val();
        var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i
        
        if(userEmail.trim() == ''){
            $("#emailError").show();
            $("#emailError").html(message.enter_email);
        }else if(!pattern.test(userEmail)){
            $("#emailError").show();
            $("#emailError").html(message.enter_valid_email);
        }else{
            $("#emailError").hide();
            $("#emailError").html("");
            exportChallengeUserActivityReport();
        }
    });
});


function exportChallengeUserActivityReport() {
    $('.page-loader-wrapper').show();
    var exportConfirmModalBox = '#export-model-box';

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.ajax({
        type: 'POST',
        url: url.exportReport,
        data: {
            status: 1,
            logdate: data.logdate,
            user_id: data.user,
            challenge_id: data.challenge,
            type: data.type,
            columnName: data.columnName,
            modelId: data.modelId,
            uom: data.uom,
            challengeStatus: data.challengeStatus,
            trackerFilter: $('#trackerFilter').val(),
            email: $('#email').val()
        },
        crossDomain: true,
        cache: false,
        success: function (data) {
            $('#challengeUserActivity').DataTable().ajax.reload(null, false);
            if (data['status'] == 1) {
                toastr.success(data['data']);
            } else {
                toastr.success(data['data']);
            }
            $(exportConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
            window.location.reload();
        },
        error: function (data) {
            $('#challengeUserActivity').DataTable().ajax.reload(null, false);
            toastr.error("This action is unauthorized.");
            $(exportConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        }
    });
}
$(document).ready(function() {
    $('#challengeManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                challengeName: $('#challengeName').val(),
                challengeType: $('#challengeType').val(),
                subType: $('#subtype').val(),
                recursive: $('#recursive').val(),
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
            data: 'duration',
            name: 'duration'
        }, {
            data: 'created_by',
            name: 'created_by'
        }, {
            data: 'challenge_type',
            name: 'challenge_type'
        }, {
            data: 'type',
            name: 'type'
        }, {
            data: 'joined',
            name: 'joined'
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
            targets: [6],
            className: 'text-center',
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
});
$(document).on('click', '#deleteModal', function(t) {
    var deleteConfirmModalBox = '#delete-model-box';
    $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
    $(deleteConfirmModalBox).modal('show');
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
$(document).on('change','#challengeType', function() {
    var type = $(this).val();
    var data = [];
    $('#recursive').parent().show();
    if(type == 'routine') {
        data = jQuery.parseJSON(datarecords.personalRoutineChallengeSubType);
    } else if(type == 'habit') {
        $('#recursive').parent().hide();
        data = jQuery.parseJSON(datarecords.personalHabitChallengeSubType);
    } else {
        data = jQuery.parseJSON(datarecords.personalFitnessChallengeSubType);
    }
    dataSubType(data);
});
function dataSubType(data) {
    var options = '';
    $.each(data, function(index, element) {
        options += `<option value="${index}">${element}</option>`;
    });
    $('#subtype').html(options).val('').prop('disabled', false);
    $('#subtype').select2('destroy').select2();

    $('#subtypeRecords').show();
    if ($("#subtype").length > 0 && $(".no-default-select2").length == 0) {
        $('#subtype').select2({
            placeholder: "Select",
            allowClear: true,
            width: '100%'
        });
    }
}
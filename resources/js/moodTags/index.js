$(document).ready(function() {
    $('#moodManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false
        }, {
            data: 'tag',
            name: 'tag'
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
            targets: [2],
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
            $('#moodManagement').DataTable().ajax.reload(null, false);
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
            $('#moodManagement').DataTable().ajax.reload(null, false);
            toastr.error(message.unauthorized);
            var deleteConfirmModalBox = '#delete-model-box';
            $(deleteConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        }
    });
});
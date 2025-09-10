$(document).ready(function() {
    $('#goalManagement').DataTable({
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
            data: 'logo',
            name: 'logo',
            searchable: false,
            sortable: false
        }, {
            data: 'title',
            name: 'title',
            sortable: true
        }, {
            data: 'feed',
            name: 'feed',
            sortable: true
        }, {
            data: 'masterclass',
            name: 'masterclass',
            sortable: true
        }, {
            data: 'recipe',
            name: 'recipe',
            sortable: true
        }, {
            data: 'meditation',
            name: 'meditation',
            sortable: true
        }, {
            data: 'webinar',
            name: 'webinar',
            sortable: true
        }, {
            data: 'total',
            name: 'total',
            sortable: true
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
            targets: [1, 3],
            className: 'text-center',
        }],
        stateSave: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
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
            url: url.delete.replace(':id', objectId),
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function(data) {
                location.reload(true);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
                $('#goalManagement').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(message.goalsDeleted);
                } else {
                    toastr.error(message.something_wrong_try_again);
                }

            },
            error: function(data) {
                $('.page-loader-wrapper').hide();
                $('#goalManagement').DataTable().ajax.reload(null, false);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                toastr.error(message.action_unauthorized);
            }
        });
    });
});
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#roleManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                roleName: $('#roleName').val(),
                roleGroup: $('#roleGroup').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false
        }, {
            data: 'name',
            name: 'name'
        }, {
            data: 'group',
            name: 'group'
        }, {
            data: 'description',
            name: 'description',
            searchable: false
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
        }],
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });
    $(document).on('click', '.roleDelete', function(t) {
        $('#delete-model-box').data("id", $(this).data('id')).modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#delete-model-box').data("id");
        $.ajax({
            type: 'DELETE',
            url: url.delete.replace(':id', objectId),
            contentType: 'json',
            success: function(data) {
                $('#roleManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(message.deleted);
                } else if (data['deleted'] == 'use') {
                    toastr.error(message.in_user);
                } else {
                    toastr.error(message.delete_fail);
                }
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function(data) {
                toastr.error(message.delete_fail);
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });
});
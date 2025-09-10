$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#broadcastManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                title: $('#title').val(),
                group_type: $('#group_type').val(),
                group_name: $('#group_name').val(),
                status: $('#status').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            { data: 'title', name: 'title' },
            { data: 'message', name: 'message' },
            {
                data: 'created_at', name: 'created_at',
                className: 'text-center',
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format(format);
                }
            },
            { data: 'group_type', name: 'group_type' },
            { data: 'group_name', name: 'group_name' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', searchable: false, sortable: false, className: 'text-center' }
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        columnDefs: [],
        stateSave: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
    });

    $(document).on('click', '.delete-broadcast', function (e) {
        $('#delete-model-box').data("id", $(this).data('id')).modal('show');
    });

    $(document).on('click', '#delete-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#delete-model-box').data("id");

        $.ajax({
            type: 'DELETE',
            url: url.delete + `/${objectId}`,
            crossDomain: true,
            cache: false,
            contentType: 'json'
        })
        .done(function(data) {
            if (data && data.deleted == true) {
                $('#broadcastManagment').DataTable().ajax.reload(null, false);
                toastr.success(message.broadcast_deleted);
            } else if (data && data.deleted == 'time_limit') {
                toastr.error(message.delete_broadcast_message);
            } else {
                toastr.error(message.failed_delete_broadcast);
            }
        })
        .fail(function(data) {
            toastr.error(message.failed_delete_broadcast);
        })
        .always(function() {
            $('#delete-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });
});
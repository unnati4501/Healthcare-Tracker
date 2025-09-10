$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#themeManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
        },
        columns: [{
            data: 'name',
            name: 'name'
        }, {
            data: 'link',
            name: 'link'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false,
            className: 'text-center',
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
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
            paginate: {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
    });

    $(document).on('click', '.deleteTheme', function(t) {
        $('#delete-model-box').data("id", $(this).data('id')).modal('show');
    });

    $(document).on('click', '#delete-model-box-confirm', function(e) {
        var id = $('#delete-model-box').data("id");
        $('.page-loader-wrapper').show();
        $.ajax({
            type: 'DELETE',
            url: url.delete.replace(":id", id),
            contentType: 'json',
        }).done(function(data) {
            toastr.success((data?.data ?? message.data_deleted_failed));
        }).fail(function(error) {
            toastr.error((error?.responseJSON?.data || message.data_deleted_failed));
        }).always(function() {
            $('#themeManagement').DataTable().ajax.reload(null, false);
            $('.page-loader-wrapper').hide();
            $('#delete-model-box').modal('hide');
        });
    });
});
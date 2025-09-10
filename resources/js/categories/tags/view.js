$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#tagsManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.tags,
        },
        columns: [{
            data: 'name',
            name: 'name'
        }, {
            data: 'mapped_content',
            name: 'mapped_content',
            searchable: false
        }, {
            data: 'actions',
            name: 'actions',
            className: 'no-sort',
            searchable: false,
            sortable: false
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
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });
});
$(document).on('click', '.delete-tag', function(t) {
    $('#delete-model-box').attr("data-id", $(this).data('id')).modal('show');;
});
$(document).on('click', '#delete-model-box-confirm', function(e) {
    $('.page-loader-wrapper').show();
    const objectId = $('#delete-model-box').attr("data-id");
    $.ajax({
        type: 'DELETE',
        url: url.delete.replace(':id', objectId),
        dataType: 'json',
    }).done(function(data) {
        $('#tagsManagment').DataTable().ajax.reload(null, false);
        if (data['deleted'] == 'true') {
            toastr.success(messages.deleted);
        } else if (data['deleted'] == 'use') {
            toastr.error(messages.in_use);
        }
    }).fail(function(error) {
        toastr.error((error?.responseJSON?.data || messages.something_wrong_try_again));
    }).always(function() {
        $('#delete-model-box').modal('hide');
        $('.page-loader-wrapper').hide();
    });
});
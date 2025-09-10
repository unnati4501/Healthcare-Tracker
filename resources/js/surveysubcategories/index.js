$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#surveysubCategoryManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                category: _category,
                getQueryString: window.location.search,
                subcategoryName: $('#subcategoryName').val(),
                isPrimum: $('#isPrimum').val(),
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
            data: 'display_name',
            name: 'display_name'
        }, {
            data: 'questions',
            name: 'questions'
        }, {
            data: 'premium',
            name: 'premium'
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
            [0, 'DESC']
        ],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
    });
    $(document).on('click', '.surveysubCategoryDelete', function(t) {
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
                $('#surveysubCategoryManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(messages.deleted);
                } else if (data['deleted'] == 'use') {
                    toastr.error(messages.in_use);
                } else {
                    toastr.error(messages.delete_fail);
                }
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
                location.reload();
            },
            error: function(data) {
                toastr.error(messages.delete_fail);
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });
});
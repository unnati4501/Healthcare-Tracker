$(document).ready(function() {
    $('#subCategoryManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: subCategoriesListUrl,
            data: {
                category: categoryId,
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
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false
        }],
        paging: true,
        pageLength: pagination,
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
        }, {
            targets: 2,
            className: 'text-center',
        }],
        stateSave: false,
        language: {
            paginate: {
                previous: "<i class='far fa-angle-left page-arrow align-middle me-2'></i><span class='align-middle'>Prev</span>",
                next: "<span class='align-middle'>Next</span><i class='far fa-angle-right page-arrow align-middle ms-2'></i> "
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });
});
$(document).on('click', '#subCategoryDelete', function(t) {
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
        url: subCategoriesDeleteUrl + '/' + objectId,
        data: null,
        crossDomain: true,
        cache: false,
        contentType: 'json',
        success: function(data) {
            $('#subCategoryManagment').DataTable().ajax.reload(null, false);
            if (data['deleted'] == 'true') {
                toastr.success(deletedMessage);
            } else if (data['deleted'] == 'use') {
                toastr.error(inUseMessage);
            } else {
                toastr.error(somethingWentWrongMessage);
            }
            var deleteConfirmModalBox = '#delete-model-box';
            $(deleteConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        },
        error: function(data) {
            if (data == 'Forbidden') {
                toastr.error(unauthorizedMessage);
            }
            var deleteConfirmModalBox = '#delete-model-box';
            $(deleteConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        }
    });
});
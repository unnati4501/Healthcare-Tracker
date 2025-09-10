$(document).ready(function() {
    $('#categoryManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: categoriesListUrl,
            data: {
                status: 1,
                categoryName: $('#categoryName').val(),
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
            data: 'subcategory',
            name: 'subcategory',
            searchable: false
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
            [0, 'desc']
        ],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }, {
            targets: 3,
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
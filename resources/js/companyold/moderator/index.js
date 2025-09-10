$(document).ready(function() {
    $('#moderatorsManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                recordName: $('#recordName').val(),
                recordEmail: $('#recordEmail').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'name',
            name: 'name'
        }, {
            data: 'email',
            name: 'email'
        }, ],
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
});
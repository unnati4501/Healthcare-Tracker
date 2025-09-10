$(document).ready(function() {
    $('#userlist').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                getQueryString: window.location.search
            },
        },
        columns: [
            {data: 'updated_at', name: 'updated_at' , visible: false},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'}
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: true,
        ordering: true,
        order: [[0, 'desc']],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        stateSave: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "infoFiltered": ""
        },
    });
});
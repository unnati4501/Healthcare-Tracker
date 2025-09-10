$(document).ready(function() {
    $('#reportAbuse').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                userName: $('#userName').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            {data: 'updated_at', name: 'updated_at' , visible: false},
            {data: 'fullName', name: 'fullName'},
            {data: 'email', name: 'email'},
            {data: 'reason', name: 'reason'},
            {data: 'message', name: 'message'}
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
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
            }
        }
    });
});
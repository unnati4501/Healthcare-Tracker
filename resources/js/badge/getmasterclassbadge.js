$(document).ready(function() {
    $('#badgeManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                badgeName: $('#badgeName').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            { data: 'updated_at', name: 'updated_at', visible: false },
            { data: 'title', name: 'title' },
            { data: 'awarded_badge', name: 'awarded_badge', searchable: false, sortable: false }
        ],
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
        }, {
            targets: 0,
            className: 'text-center',
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
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    var subCategories = null;
    $('#consentFormManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                getQueryString: window.location.search
            },
        },
        columns: [
            { name: 'id', data: null,render: (data, type, row, meta) => meta.row+1, sortable: false},
            { data: 'title', name: 'title' },
            { data: 'category', name: 'category' },
            { data: 'actions', name: 'actions', className: 'text-center', searchable: false, sortable: false }
        ],
        order: [],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
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
            },
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false
    });

});
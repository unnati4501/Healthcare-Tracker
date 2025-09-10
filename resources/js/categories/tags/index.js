$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#categoryTagsManagment').DataTable({
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
            data: 'total_tags',
            name: 'total_tags',
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
            },
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });
});
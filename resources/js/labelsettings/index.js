$(document).ready(function(){
    $('#labelstrings').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
        },
        columns: [
            {data: 'updated_at', name: 'updated_at', visible: false},
            {data: 'module', name: 'module'},
            {data: 'field_name', name: 'field_name'},
            {data: 'label_name', name: 'label_name'}
        ],
        paging: false,
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
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false,
        language: {
            paginate: {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
    });
});
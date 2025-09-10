$(document).ready(function() {
    $('#AppSettingsManagment').DataTable({
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
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'key', name: 'key'},
            {data: 'value', name: 'value'}
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
        }, {
            targets: 0,
            className: 'text-center',
        }],
        stateSave: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        }
    });
    $('#roleGroup').select2({
        placeholder: message.select_group
    });
});
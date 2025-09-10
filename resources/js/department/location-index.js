$(document).ready(function() {
    $('#departmentLocation').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.getLocationList,
            data: {
                status: 1,
                getQueryString: window.location.search
            },
        },
        columns: [
            {data: 'updated_at', name: 'updated_at' , visible: false},
            {data: 'locationname', name: 'locationname'},
            {data: 'country', name: 'country'},
            {data: 'state', name: 'state'},
            {data: 'time_zone', name: 'time_zone'},
            {data: 'address', name: 'address'}
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
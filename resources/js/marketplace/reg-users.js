$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#eventRegUsersManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                status: 1,
                name: $('#name').val(),
                email: $('#email').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'username',
            name: 'username'
        }, {
            data: 'email',
            name: 'email'
        }, {
            data: 'created_at',
            name: 'created_at',
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [
            [25, 50, 100],
            [25, 50, 100]
        ],
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
            lengthMenu: pagination.entry_per_page + " _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        buttons: [{
            extend: 'excel',
            text: button.export,
            className: 'btn btn-primary',
            title: `${labels.exportFile} ${Date.now()}`,
            download: 'open',
            orientation: 'landscape',
        }]
    });
});
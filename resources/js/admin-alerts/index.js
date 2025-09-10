$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    var subCategories = null;
    $('#adminAlertList').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                getQueryString: window.location.search
            },
        },
        columns: [
            { data: 'title', name: 'title' },
            { data: 'notify_users', name: 'notify_users', searchable: false, sortable: false },
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
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
        },
        stateSave: false
    });

    $('#userlist_tbl').DataTable({
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'user_name',
            name: 'user_name'
        }, {
            data: 'user_email',
            name: 'user_email'
        }],
        order: [
            [0, 'asc']
        ],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
            sInfo: "Entries _START_ to _END_",
            infoFiltered: ""
        },
    });

    $(document).on('click', '.preview_users', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if (_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));
            $(_data).each(function(index, el) {
                dataJson.push({
                    id: (index + 1),
                    user_name: el.user_name,
                    user_email: el.user_email
                });
            });
            $('#userlist_tbl').DataTable().clear().rows.add(dataJson).order([0, 'asc']).search('').draw();
            $('#user_preview').modal('show')
        }
    });

});
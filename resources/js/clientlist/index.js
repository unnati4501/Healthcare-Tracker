$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#clientListManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                getQueryString: window.location.search,
                name: $('#name').val(),
                email: $('#email').val(),
                company: $('#company').val(),
            },
        },
        columns: [{
            data: 'client_name',
            name: 'client_name',
        }, {
            data: 'email',
            name: 'email',
        }, {
            data: 'wellbeing_specialist',
            name: 'wellbeing_specialist',
            visible: (role == 'super_admin')
        }, {
            data: 'company_name',
            name: 'company_name',
            visible: (role == 'super_admin')
        }, {
            data: 'completed_session',
            name: 'completed_session',
            className: 'text-center'
        }, {
            data: 'upcoming',
            name: 'upcoming',
            className: 'text-center'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false,
            visible: (role != 'super_admin')
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
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        }
    });
});
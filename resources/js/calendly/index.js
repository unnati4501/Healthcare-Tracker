$(document).ready(function() {
    $('#calendlyManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                getQueryString: window.location.search,
                email: $('#email').val(),
                user: $('#user').val(),
                company: $('#company').val(),
                duration: $('#duration').val(),
                status: $('#status').val(),
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false
        }, {
            data: 'name',
            name: 'name',
        }, {
            data: 'user',
            name: 'user',
        }, {
            data: 'email',
            name: 'email',
        }, {
            data: 'therapist',
            name: 'therapist',
            visible: companyVisibility
        }, {
            data: 'company',
            name: 'company',
            visible: companyVisibility
        }, {
            data: 'duration',
            name: 'duration'
        }, {
            data: 'datetime',
            name: 'datetime',
        }, {
            data: 'status',
            name: 'status'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false,
            visible: !companyVisibility
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [
            [0, 'desc']
        ],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }, {
            targets: [9],
            className: 'text-center',
        }],
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false
    });
});
$(document).on('click', '#completeModal', function(e) {
    $('.page-loader-wrapper').show();
    var objectId = $(this).attr("data-id");
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'PATCH',
        url: url.complete + '/' + objectId,
        data: null,
        crossDomain: true,
        cache: false,
        contentType: 'json',
        success: function(data) {
            $('#calendlyManagement').DataTable().ajax.reload(null, false);
            if (data['completed'] == 'true') {
                toastr.success(message.completed);
            } else {
                toastr.error(message.somethingWentWrong);
            }
            $('.page-loader-wrapper').hide();
        },
        error: function(data) {
            $('#calendlyManagement').DataTable().ajax.reload(null, false);
            toastr.error(message.somethingWentWrong);
            $('.page-loader-wrapper').hide();
        }
    });
});
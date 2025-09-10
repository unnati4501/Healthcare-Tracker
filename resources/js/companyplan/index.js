$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#companyPlan').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                grouptype: $('#grouptype').val(),
                companyplan: $('#companyplan').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false
        }, {
            data: 'company_plan',
            name: 'company_plan',
        }, {
            data: 'mapped_companies',
            name: 'mapped_companies',
            className: 'text-center'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false
        }],
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
            paginate: {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
    });
});

$(document).on('click', '#companyplanDelete', function(e) {
    $('#delete-model-box').data("id", $(this).data('id'));
    $('#delete-model-box').modal('show');
});

$(document).on('click', '#delete-model-box-confirm', function(e) {
    $('.page-loader-wrapper').show();
    var objectId = $('#delete-model-box').data("id");

    $.ajax({
        type: 'DELETE',
        url: url.delete.replace(':id', objectId),
        data: null,
        crossDomain: true,
        cache: false,
        contentType: 'json',
    })
    .done(function(data) {
        if (data.deleted == 'true') {
            $('#companyPlan').DataTable().ajax.reload(null, false);
            toastr.success(message.companyplan_deleted);
        } else if(data.alreadyUse == 'true') {
            toastr.error(message.already_in_use);
        } else {
            toastr.error(message.unable_to_delete_companyplan);
        }
    })
    .fail(function(data) {
        toastr.error(message.unable_to_delete_companyplan);
    })
    .always(function() {
        $('#delete-model-box').modal('hide');
        $('.page-loader-wrapper').hide();
    });
});

$(document).ready(function() {
    $('#domainManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                domainName: $('#domainName').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            {data: 'updated_at', name: 'updated_at' , className: 'hide'},
            {data: 'domain', name: 'domain'},
            {data: 'actions', name: 'actions', searchable: false, sortable: false}
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
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
    });

    $('#roleGroup').select2({
        placeholder: message.select_group
    });
    $(document).on('click', '#domainDelete', function (t) {
        var deleteConfirmModalBox = '#delete-model-box';
        $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
        $(deleteConfirmModalBox).modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function (e) {
        $('.page-loader-wrapper').show();
        var deleteConfirmModalBox = '#delete-model-box';
        var objectId = $(deleteConfirmModalBox).attr("data-id");

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $.ajax({
            type: 'DELETE',
            url: url.delete + '/' + objectId,
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function (data) {
                $('#domainManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(message.domain_deleted);
                } else if(data['deleted'] == 'use') {
                    toastr.error(message.domain_in_use);
                } else {
                    toastr.error(message.unable_to_delete_domain);
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function (data) {
                $('#domainManagment').DataTable().ajax.reload(null, false);
                toastr.error(message.unable_to_delete_domain);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });
});
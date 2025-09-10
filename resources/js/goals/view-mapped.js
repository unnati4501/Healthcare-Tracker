$(document).ready(function() {
    $('#goalManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
            	goal_id: data.goal_id,
            	title: $('#title').val(),
                type: $('#tagtype').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false,
        }, {
            data: 'logo',
            name: 'logo',
            searchable: false,
            sortable: false
        }, {
            data: 'title',
            name: 'title',
            sortable: true
        }, {
            data: 'type',
            name: 'type',
            sortable: true
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
        order: [
            [0, 'desc']
        ],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
    });
    $(document).on('click', '#deleteModal', function(t) {
        var deleteConfirmModalBox = '#delete-model-box';
        $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
        $(deleteConfirmModalBox).attr("data-type", $(this).data('type'));
        $(deleteConfirmModalBox).modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var deleteConfirmModalBox = '#delete-model-box';
        var objectId = $(deleteConfirmModalBox).attr("data-id");
        var objectType = $(deleteConfirmModalBox).attr("data-type");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // Unmapped goal tag
        $.ajax({
            type: 'delete',
            url: url.delete + '/' + objectId + '/' + objectType,
            data: null,
            crossDomain: true,
            cache: false,
            dataType: 'json',
            contentType: 'json',
            success: function(data) {
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
                $('#goalManagement').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(message.goal_tag_unmapped_successfully);
                } else {
                    toastr.error(message.something_wrong_try_again);
                }
            },
            error: function(data) {
                $('.page-loader-wrapper').hide();
                $('#goalManagement').DataTable().ajax.reload(null, false);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                toastr.error(message.action_unauthorized);
            }
        });
    });
});
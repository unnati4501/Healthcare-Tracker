$(document).ready(function() {
    $('#badgeManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                badgeName: $('#badgeName').val(),
                badgeType: $('#badgeType').val(),
                willExpire: $('#willExpire').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            { data: 'updated_at', name: 'updated_at', visible: false },
            { data: 'logo', name: 'logo', className: 'text-center', searchable: false, sortable: false },
            { data: 'title', name: 'title' },
            { data: 'type', name: 'type' },
            { data: 'activity', name: 'activity', searchable: false, sortable: false },
            { data: 'target_value', name: 'target_value', visible: condition.isSA, searchable: false, sortable: false },
            { data: 'awarded_badge', name: 'awarded_badge', searchable: false, sortable: false },
            { data: 'actions', name: 'actions', searchable: false, sortable: false, className: 'text-center', visible: condition.isSA }
        ],
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
        }, {
            targets: 0,
            className: 'text-center',
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

    $(document).on('click', '#badgeDelete', function (t) {
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
            url: url.delete.replace(':id', objectId),
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function (data) {
                $('#badgeManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(message.badge_deleted);
                } else if(data['deleted'] == 'use') {
                    toastr.error(message.badge_in_use);
                } else {
                    toastr.error(message.unable_to_badge);
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function (data) {
                $('#badgeManagment').DataTable().ajax.reload(null, false);
                toastr.error(message.unable_to_badge);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });
});
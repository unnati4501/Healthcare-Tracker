$(document).ready(function() {
    $('#exerciseManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                exerciseName: $('#exerciseName').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            {data: 'updated_at', name: 'updated_at' , visible: false},
            {data: 'logo', name: 'logo', searchable: false, sortable: false},
            {data: 'title', name: 'title'},
            {data: 'description', name: 'description'},
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
        }, {
            targets: 1,
            className: 'text-center',
        }, {
            targets: 4,
            className: 'text-center',
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
    $(document).on('click', '#exerciseDelete', function (t) {
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
                $('#exerciseManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(message.exercise_deleted);
                } else if(data['deleted'] == 'use') {
                    toastr.error(message.exercise_in_use);
                } else {
                    toastr.error(message.unable_to_delete_exercise);
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function (data) {
                $('#exerciseManagment').DataTable().ajax.reload(null, false);
                toastr.error(message.unable_to_delete_exercise);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });
});
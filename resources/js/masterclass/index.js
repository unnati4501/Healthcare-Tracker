$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#courseManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.datatable,
            data: {
                status: 1,
                recordName: $('#recordName').val(),
                recordCoach: $('#recordCoach').val(),
                recordCategory: $('#recordCategory').val(),
                recordTag: $('#recordTag').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false
        }, {
            data: 'logo',
            searchable: false,
            sortable: false,
        }, {
            data: 'title',
            name: 'title'
        }, {
            data: 'subcategory',
            name: 'subcategory'
        }, {
            data: 'coachName',
            name: 'coachName'
        }, {
            data: 'visible_to_company',
            name: 'visible_to_company',
            searchable: false,
            sortable: false,
        }, {
            data: 'category_tag',
            name: 'category_tag',
            visible: (roleGroup == 'zevo'),
        }, {
            data: 'total_members',
            name: 'total_members',
            searchable: false
        }, {
            data: 'totalLessions',
            name: 'totalLessions',
            searchable: false
        }, {
            data: 'totalDurarion',
            name: 'totalDurarion',
            searchable: false
        }, {
            data: 'totalLikes',
            name: 'totalLikes',
            searchable: false
        }, {
            data: 'status',
            name: 'status',
            searchable: false,
            sortable: false
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
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });
    $('#visible_to_company_tbl').DataTable({
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'group_type',
            name: 'group_type'
        }, {
            data: 'company',
            name: 'company'
        }],
        order: [
            [0, 'asc']
        ],
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
            sInfo: "Entries _START_ to _END_",
            "infoFiltered": ""
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });
    $(document).on('click', '.courseDelete', function(e) {
        $('#delete-model-box').data("id", $(this).data('id')).modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#delete-model-box').data("id");
        $.ajax({
            type: 'DELETE',
            url: url.delete.replace(':id', objectId),
            contentType: 'json',
            success: function(data) {
                $('#courseManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(messages.deleted);
                } else if (data['deleted'] == 'use') {
                    toastr.error(messages.in_use);
                } else {
                    toastr.error(messages.delete_fail);
                }
            },
            error: function(data) {
                $('#courseManagment').DataTable().ajax.reload(null, false);
                toastr.error(messages.delete_fail);
            }
        }).always(function() {
            $('#delete-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });
    $(document).on('click', '.publishCourse', function(e) {
        var action = $(this).data("action");
        $('#publish-course-model-box, #unpublish-course-model-box').data("id", $(this).data('id'));
        $('#publish-course-model-box, #unpublish-course-model-box').data("action", action);
        if (action == "unpublish") {
            $('#unpublish-course-model-box').modal('show');
        } else if (action == "publish") {
            $('#publish-course-model-box').modal('show');
        }
    });
    $(document).on('click', '#course-model-box-confirm, #course-model-unpublish-box-confirm', function(e) {
        var _this = $(this),
            objectId = $('#publish-course-model-box').data("id"),
            action = $('#publish-course-model-box').data("action");
        _this.prop('disabled', 'disabled');
        $.ajax({
            type: 'POST',
            url: url.publish.replace(':id', objectId),
            data: $.param({
                action: action
            }),
            dataType: 'json',
        }).done(function(data) {
            if (data.published == true) {
                $('#courseManagment').DataTable().ajax.reload(null, false);
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        }).fail(function(data) {
            toastr.error(messages.failed_action.replace(':action', action));
        }).always(function() {
            _this.removeAttr('disabled');
            $('#unpublish-course-model-box').modal('hide');
            $('#publish-course-model-box').modal('hide');
        });
    });
    $(document).on('click', '.preview_companies', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if (_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));
            $(_data).each(function(index, el) {
                dataJson.push({
                    id: (index + 1),
                    group_type: el.group_type,
                    company: el.name
                });
            });
            $('#visible_to_company_tbl').DataTable().clear().rows.add(dataJson).order([0, 'asc']).search('').draw();
            $('#company_visibility_preview').modal('show')
        }
    });
});
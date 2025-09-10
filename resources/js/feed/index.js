$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#feedManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.datatable,
            data: {
                status: 1,
                sheduled_content: $('#sheduled_content').val(),
                feedName: $('#feedName').val(),
                feedCompany: $('#feedCompany').val(),
                recordCategory: $('#recordCategory').val(),
                type: $('#type').val(),
                tag: $('#tag').val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            { data: 'logo', name: 'logo', searchable: false, sortable: false },
            { data: 'title', name: 'title' },
            { data: 'companyName', name: 'companyName', visible: condition.visibletocompanyvisibility },
            { data: 'companiesName', name: 'companiesName', sortable: false, searchable: false, visible: condition.visibletocompanyvisibility },
            { data: 'category_tag', name: 'category_tag', visible: (roleGroup == 'zevo') },
            { data: 'subcategory', name: 'subcategory' },
            { data: 'health_coach', name: 'health_coach' },
            {
                data: 'start_date', name: 'start_date', searchable: false,
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            },
            {
                data: 'end_date', name: 'end_date', searchable: false,
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            },
            { data: 'totalLikes', name: 'totalLikes', searchable: false, visible: condition.companyColVisibility },
            { data: 'is_stick', name: 'is_stick', searchable: false },
            { data: 'view_count', name: 'view_count', visible: condition.companyColVisibility },
            { data: 'actions', name: 'actions', searchable: false, sortable: false }
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }, {
            targets: 0,
            className: 'text-center',
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        order: [],
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
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
        order: [[0, 'asc']],
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        columnDefs: [{
            "targets": 'no-sort',
            "orderable": false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
             "sInfo": "Entries _START_ to _END_",
             "infoFiltered": ""
        }
    });

    $(document).on('click', '#feedDelete', function(e) {
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
                $('#feedManagment').DataTable().ajax.reload(null, false);
                toastr.success(message.feed_deleted);
            } else if (data.deleted == 'use') {
                toastr.error(message.feed_in_use);
            } else {
                toastr.error(message.unable_to_feed_group);
            }
        })
        .fail(function(data) {
            toastr.error(message.unable_to_feed_group);
        })
        .always(function() {
            $('#delete-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });

    $(document).on('click', '.stick-feed', function(e) {
        var _action = $(this).data('action');
        $('#stick-model-box, #unstick-model-box').data("id", $(this).data('id'));
        $('#stick-model-box, #unstick-model-box').data("action", _action);

        if(_action == "stick") {
            $('#stick-model-box').modal('show');
        } else if(_action == "unstick") {
            $('#unstick-model-box').modal('show');
        }
    });

    $(document).on('click', '#stick-model-box-confirm, #unstick-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#stick-model-box').data("id"),
            action = $('#stick-model-box').data("action");

        $.ajax({
            type: 'POST',
            url: url.stickunstick.replace(':id', objectId),
            data: { action: action },
            dataType: 'json',
        })
        .done(function(data) {
            if (data.status == true) {
                $('#feedManagment').DataTable().ajax.reload(null, false);
                toastr.success(data.message);
            } else {
                toastr.error(((data.message && data.message != '') ? data.message : message.failed_feed_action.replace(':action', action)));
            }
        })
        .fail(function(data) {
            toastr.error(((data.message && data.message != '') ? data.message : message.failed_feed_action.replace(':action', action)));
        })
        .always(function() {
            $('#stick-model-box').modal('hide');
            $('#unstick-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });

    $(document).on('click', '.preview_companies', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if(_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));
            $(_data).each(function(index, el) {
                dataJson.push({
                    id: (index + 1),
                    group_type: el.group_type,
                    company: el.name
                });
            });
            $('#visible_to_company_tbl').DataTable()
                .clear()
                .rows
                .add(dataJson)
                .order([0, 'asc'])
                .search('')
                .draw();
            $('#company_visibility_preview').modal('show')
        }
    });
});
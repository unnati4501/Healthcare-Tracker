$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var _eapLessonManagment = $('#eapManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false,
        }, {
            data: 'logo',
            searchable: false,
            sortable: false,
        }, {
            data: 'title',
            name: 'title',
            className: 'allow-reorder',
        }, {
            data: 'companiesName',
            name: 'companiesName',
            sortable: false,
            searchable: false,
            visible: data.visabletocompanyVisibility
        },
        {
            data: 'view_count',
            name: 'view_count',
            className: 'allow-reorder',
        }, {
            data: 'telephone',
            name: 'telephone',
            className: 'allow-reorder',
        }, {
            data: 'email',
            name: 'email',
            className: 'allow-reorder',
        }, {
            data: 'website',
            name: 'website',
            className: 'allow-reorder',
        }, {
            data: 'created_at',
            name: 'created_at',
            searchable: false,
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }
        }, {
            data: 'sticky',
            name: 'sticky',
            className: 'allow-reorder',
            searchable: false,
            sortable: false,
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false,
            className: 'text-center'
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: data.reordering,
        order: [],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            }
        },
        rowReorder: {
            enable: data.roworder,
            update: false,
            selector: '.allow-reorder',
            responsive: true
        },
        drawCallback: function( settings ) {
            $('[data-bs-toggle="tooltip"]').tooltip();
            if(data.roworder == true) {
                _eapLessonManagment.rowReorder.enable();
            }
        }
    });

    $('#visible_to_company_tbl').DataTable({
        lengthChange: false,
        pageLength: pagination.value,
        autoWidth: false,
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'group_type',
            name: 'group_type'
        },{
            data: 'company',
            name: 'company'
        }],
        order: [[0, 'asc']],
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
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

    _eapLessonManagment.on('row-reorder', function(e, movedElements, element) {
        var finalArray = {};
        $(movedElements).each(function(index, movedElement) {
            var rowData = _eapLessonManagment.row(movedElement.node).data();
            finalArray[rowData.id] = {
                'oldPosition': (movedElement.oldPosition + 1),
                'newPosition': (movedElement.newPosition + 1),
            };
        });
        if (Object.keys(finalArray).length > 0) {
            _eapLessonManagment.rowReorder.disable();
            toastr.clear();
            $.ajax({
                url: url.reorderingEap,
                type: 'POST',
                dataType: 'json',
                data: { positions: finalArray },
            })
            .done(function(data) {
                if(data && data.status === true) {
                    toastr.success(data.message);
                } else {
                    toastr.error((data.message) ? data.message : message.something_went_wrong);
                }
            })
            .fail(function(error) {
                toastr.error((error.message) ? error.message : message.something_went_wrong);
            })
            .always(function() {
                _eapLessonManagment.ajax.reload(null, false);
            });
        }
    });

    $(document).on('click', '.stick-support', function(e) {
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
                $('#eapManagment').DataTable().ajax.reload(null, false);
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

    $(document).on('click', '#companyDelete', function (t) {
        var deleteConfirmModalBox = '#delete-model-box';
        $(deleteConfirmModalBox).data("id", $(this).data('id'));
        $(deleteConfirmModalBox).modal('show');
    });

    $(document).on('click', '#delete-model-box-confirm', function(e) {
        var _this = $('#delete-model-box'),
            objectId = _this.data("id");
        _this.prop('disabled', true);
        $('.page-loader-wrapper').show();
        $.ajax({
            type: 'DELETE',
            url: url.delete.replace(':id', objectId),
            crossDomain: true,
            cache: false,
            contentType: 'json'
        })
        .done(function(data) {
            $('#eapManagment').DataTable().ajax.reload(null, false);
            if (data.deleted == true) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        })
        .fail(function(data) {
            if (data == 'Forbidden') {
                toastr.error(message.delete_error);
            }
        })
        .always(function() {
            _this.prop('disabled', false);
            $('#delete-model-box').modal('hide');
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
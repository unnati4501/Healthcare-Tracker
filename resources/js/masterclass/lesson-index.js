$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var _courseLessonManagment = $('#courseLessonManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                recordName: $('#recordName').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'order_priority',
            name: 'order_priority',
            className: 'text-center allow-reorder'
        }, {
            data: 'id',
            name: 'id',
            className: 'allow-reorder',
            visible: false
        }, {
            data: 'title',
            name: 'title',
            className: 'allow-reorder'
        }, {
            data: 'duration',
            name: 'duration',
            searchable: false,
            className: 'allow-reorder'
        }, {
            data: 'status',
            name: 'status',
            searchable: false,
            sortable: false,
            className: 'allow-reorder'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        searching: false,
        ordering: enableOrdering,
        order: [
            [0, 'asc']
        ],
        autoWidth: false,
        rowReorder: {
            enable: enableRowReorder,
            update: false,
            selector: '.allow-reorder',
            responsive: true
        },
        drawCallback: function(settings) {
            $('[data-bs-toggle="tooltip"]').tooltip();
            if (enableRowReorder == true) {
                _courseLessonManagment.rowReorder.enable();
            }
        },
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
            lengthMenu: "Entries per page _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });
    _courseLessonManagment.on('row-reorder', function(e, movedElements, element) {
        var finalArray = {};
        $(movedElements).each(function(index, movedElement) {
            var rowData = _courseLessonManagment.row(movedElement.node).data();
            finalArray[rowData.id] = {
                'oldPosition': (movedElement.oldPosition + 1),
                'newPosition': (movedElement.newPosition + 1),
            };
        });
        if (Object.keys(finalArray).length > 0) {
            _courseLessonManagment.rowReorder.disable();
            toastr.clear();
            $.ajax({
                url: url.reorder,
                type: 'POST',
                dataType: 'json',
                data: {
                    positions: finalArray
                },
            }).done(function(data) {
                if (data && data.status === true) {
                    toastr.success(data.message);
                } else {
                    toastr.error((data.message) ? data.message : messages.something_wrong_try_again);
                }
            }).fail(function(error) {
                toastr.error((error.message) ? error.message : messages.something_wrong_try_again);
            }).always(function() {
                _courseLessonManagment.ajax.reload(null, false);
            });
        }
    });
    $('#courseSurveyManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.dtSurvey,
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false
        }, {
            data: 'type',
            name: 'type'
        }, {
            data: 'title',
            name: 'title'
        }, {
            data: 'status',
            name: 'status'
        }, {
            data: 'actions',
            name: 'actions'
        }, ],
        paging: false,
        searching: false,
        ordering: false,
        autoWidth: false,
        drawCallback: function(settings) {
            $('[data-bs-toggle="tooltip"]').tooltip();
        },
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });
    $(document).on('click', '.courseLessonDelete', function(t) {
        $('#delete-model-box').attr("data-id", $(this).data('id'));
        $('#delete-model-box').modal('show');
    });
    $(document).on('click', '#remove-survey-model-box-confirm', function(t) {
        $('.page-loader-wrapper').show();
        $.ajax({
            url: url.deleteSurvey,
            type: 'DELETE',
            dataType: 'json',
            crossDomain: true,
            cache: false,
        }).done(function(data) {
            if (data.deleted && data.deleted == true) {
                $('#courseSurveyManagment').DataTable().ajax.reload(null, false);
                window.location.reload();
            } else {
                toastr.error(messages.delete_fail_survey);
            }
        }).fail(function(data) {
            toastr.error(messages.delete_fail_survey);
        }).always(function() {
            $('.page-loader-wrapper').hide();
            $("#remove-survey-model-box").modal('hide');
        });
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#delete-model-box').attr("data-id");
        $.ajax({
            type: 'DELETE',
            url: url.deleteLesson.replace(':id', objectId),
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function(data) {
                $('#courseLessonManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(messages.deleted);
                } else {
                    toastr.error(messages.delete_fail);
                }
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function(data) {
                if (data == 'Forbidden') {
                    toastr.error(messages.delete_fail);
                }
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });
    $(document).on('click', '.publishlesson', function(e) {
        $('#publish-lesson-modal-box').data("id", $(this).data('id'));
        $('#publish-lesson-modal-box').modal('show');
    });
    $(document).on('click', '#publish-lesson-modal-box-confirm', function(e) {
        var _this = $(this);
        _this.prop('disabled', 'disabled');
        var objectId = $('#publish-lesson-modal-box').data("id");
        $.ajax({
            type: 'POST',
            url: url.publishLesson.replace(':id', objectId),
            crossDomain: true,
            cache: false,
            contentType: 'json'
        }).done(function(data) {
            if (data.published == true) {
                $('#courseLessonManagment').DataTable().ajax.reload(null, false);
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        }).fail(function(data) {
            if (data == 'Forbidden') {
                toastr.error(messages.lession_publish_failed);
            }
        }).always(function() {
            _this.removeAttr('disabled');
            $('#publish-lesson-modal-box').modal('hide');
        });
    });
});
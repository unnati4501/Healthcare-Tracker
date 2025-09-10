$(document).ready(function() {
    userImport();
    if (window.location.hash) {
        var hash = window.location.hash;
        if ($('.nav-tabs a[href="' + hash + '"]').length > 0) {
            $('.nav-tabs a[href="' + hash + '"]').tab('show');
            userImport();
            questionImport();
        }
    }
    $(document).on('show.bs.tab', '#myTab.nav-tabs a', function(e) {
        var target = $(e.target).attr("href");
        if (target) {
            window.location.hash = target;
            userImport();
            questionImport();
        }
    });
});

function userImport() {
    $('#userImportManagement').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: getImportsRoute,
            data: {
                module: 'users',
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            className: 'd-none'
        }, {
            data: 'company',
            name: 'company',
            visible: visibleCompany,
        }, {
            data: 'uploaded_file',
            searchable: false,
            "render": function(data, type, row) {
                return '<a href="' + row.uploaded_file_link + '">' + data + '</a>';
            }
        }, {
            data: 'validated_file',
            searchable: false,
            "render": function(data, type, row) {
                return (data != null) ? '<a href="' + row.validated_file_link + '">' + data + '</a>' : '';
            }
        }, {
            data: 'in_process',
            name: 'in_process'
        }, {
            data: 'is_processed',
            name: 'is_processed'
        }, {
            data: 'is_imported_successfully',
            name: 'is_imported_successfully'
        }, {
            data: 'created_at',
            name: 'created_at',
            searchable: false,
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }
        }, {
            data: 'actions',
            name: 'actions',
            sortable: false
        }, ],
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
        }, {
            targets: [4, 5, 6],
            className: 'text-center',
        }],
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
    });
}

function questionImport() {
    $('#questionImportManagement').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: getImportsRoute,
            data: {
                module: 'questions',
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            className: 'd-none'
        }, {
            data: 'uploaded_file',
            searchable: false,
            "render": function(data, type, row) {
                return '<a href="' + row.uploaded_file_link + '">' + data + '</a>';
            }
        }, {
            data: 'validated_file',
            searchable: false,
            "render": function(data, type, row) {
                return (data != null) ? '<a href="' + row.validated_file_link + '">' + data + '</a>' : '';
            }
        }, {
            data: 'in_process',
            name: 'in_process'
        }, {
            data: 'is_processed',
            name: 'is_processed'
        }, {
            data: 'is_imported_successfully',
            name: 'is_imported_successfully'
        }, {
            data: 'created_at',
            name: 'created_at',
            searchable: false,
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }
        }, {
            data: 'actions',
            name: 'actions',
            sortable: false
        }, ],
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
        }, {
            targets: [3, 4, 5],
            className: 'text-center',
        }],
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
    });
}
$(document).on('click', '#importDelete', function(t) {
    var deleteConfirmModalBox = '#delete-model-box';
    $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
    $(deleteConfirmModalBox).modal('show');
});
$(document).on('click', '#delete-model-box-confirm', function(e) {
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
        url: deleteImportRoute + '/' + objectId,
        data: null,
        crossDomain: true,
        cache: false,
        contentType: 'json',
        success: function(data) {
            userImport();
            questionImport();
            if (data['deleted'] == 'true') {
                toastr.success(message.file_deleted);
            } else {
                toastr.error(message.delete_error);
            }
            var deleteConfirmModalBox = '#delete-model-box';
            $(deleteConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        },
        error: function(data) {
            if (data == 'Forbidden') {
                toastr.error("delete error.");
            }
            var deleteConfirmModalBox = '#delete-model-box';
            $(deleteConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        }
    });
});
$('input[type="file"]').change(function(e) {
    if (!e.target.files[0]) {
        return;
    }
    var fileName = e.target.files[0].name;
    if (fileName.length > 40) {
        fileName = fileName.substr(0, 40);
    }
    var allowedExt = ['xlsx'];
    var ext = fileName.split('.').pop();
    if (!allowedExt.includes(ext)) {
        toastr.error(message.uploading_valid_excel_file);
        $(e.currentTarget).empty().val('');
        $(this).parent('div').find('.custom-file-label').val('');
    } else if (e.target.files[0].size > 10485760) {
        toastr.error(message.uploading_valid_excel_file);
        $(e.currentTarget).empty().val('');
        $(this).parent('div').find('.custom-file-label').val('');
    } else {
        $(this).parent('div').find('.custom-file-label').html(fileName);
    }
});
$('a[data-bs-toggle="tab"]').on('click', function(e) {
    var id = $(this).attr("href");
    $('.form-group input[type="text"]').val('');
    if (id == '#userImport') {
        userImport();
    }
    if (id == '#questionImport') {
        questionImport();
    }
});
var bar = $('#mainProgrssbar');
var percent = $('#mainProgrssbar .progpercent');
$('#userImportData,#questionImportData').ajaxForm({
    dataType: 'json',
    beforeSubmit: function(arr, $form, options) {
        if ((arr[2] && arr[2].value == "") || (arr[3] && arr[3].value == "")) {
            return false;
        }
    },
    beforeSend: function() {
        $('.progress-loader-wrapper .status-text').html(message.uploading_file);
        $('.progress-loader-wrapper').show();
        var percentVal = '0%';
        bar.width(percentVal)
        percent.html(percentVal);
    },
    uploadProgress: function(event, position, total, percentComplete) {
        var percentVal = percentComplete + '%';
        bar.width(percentVal)
        percent.html(percentVal);
        if (percentComplete == 100) {
            $('.progress-loader-wrapper .status-text').html(message.processing_on_file);
        }
    },
    success: function(data) {
        $('.progress-loader-wrapper').hide();
        var percentVal = '100%';
        bar.width(percentVal)
        percent.html(percentVal);
        $('.custom-file-label').empty().html(message.choose_file);
        $('#userImportFile').empty().val('');
        $('#questionImportFile').empty().val('');
        userImport();
        questionImport();
        if (data.status == 0) {
            toastr.error(data.data);
        } else if (data.status == 1) {
            toastr.success(data.data);
        } else {
            toastr.warning(data.data);
        }
    },
    error: function(data) {
        $('.progress-loader-wrapper').hide();
        $('.custom-file-label').empty().html(message.choose_file);
        $('#userImportFile').empty().val('');
        $('#questionImportFile').empty().val('');
        if (data && data.data) {
            toastr.error(data.data);
        } else {
            toastr.error(message.something_went_wrong);
        }
        var percentVal = '0%';
        bar.width(percentVal)
        percent.html(percentVal);
    }
});
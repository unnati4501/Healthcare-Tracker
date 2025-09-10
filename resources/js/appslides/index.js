function addCount() {
    var targetType = $('#myTab.nav-tabs a.active').attr('aria-controls');
    var count = $('#app-tab').attr('app-count');
    var portalCount = $('#portal-tab').attr('portal-count');
    var eapCount = $('#eap-tab').attr('eap-count');
    if (targetType == '') {
        targetType = 'app';
    }
    if (targetType == 'app') {
        $('#addOnBoardingBtn').attr('href', url.appSlidesApp);
    } else if (targetType == 'portal') {
        $('#addOnBoardingBtn').attr('href', url.appSlidesPortal);
    } else {
        $('#addOnBoardingBtn').attr('href', url.appSlidesApp);
        // $('#addOnBoardingBtn').attr('href', url.appSlidesEAP);
    }
    if (targetType == 'app' && count >= 3) {
        $('#addOnBoardingBtn').hide();
    } else if (targetType == 'portal' && portalCount >= 3) {
        $('#addOnBoardingBtn').hide();
    } else if (targetType == 'eap' && eapCount >= 5) {
        $('#addOnBoardingBtn').hide();
    } else {
        $('#addOnBoardingBtn').show();
    }
}
$(document).ready(function() {
    var _slideManagment = null;
    addCount();
    if (window.location.hash) {
        var hash = window.location.hash;
        $('#myTab.nav-tabs a').removeClass('active');
        if ($(hash + '-tab').length > 0) {
            $(hash + '-tab').addClass('active');
        } else {
            $('#app-tab').addClass('active');
        }
        addCount();
        if ($('.nav-tabs a[href="' + hash + '"]').length > 0) {
            $('.nav-tabs a[href="' + hash + '"]').tab('show');
        }
    }
    $(document).on('click', '#myTab.nav-tabs a', function(e) {
        $('#myTab.nav-tabs a').removeClass('active');
        var target = $(e.target).addClass('active').attr("href");
        var targetType = $(e.target).attr('aria-controls');
        if (target) {
            var scroll = $(window).scrollTop();
            window.location.hash = targetType;
            $(window).scrollTop(scroll);
            _slideManagment.ajax.reload(null, false);
            addCount();
        }
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    _slideManagment = $('#slideManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                type: function() {
                    return ($('#myTab').find('.active').attr('aria-controls') || 'app');
                },
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'order_priority',
            name: 'order_priority',
            className: 'allow-reorder'
        }, {
            data: 'id',
            name: 'id',
            className: 'allow-reorder',
            visible: false
        }, {
            data: 'content',
            name: 'content',
            className: 'allow-reorder'
        }, {
            data: 'slideImage',
            name: 'slideImage',
            searchable: false,
            sortable: false,
            className: 'allow-reorder'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false,
            className: 'text-center'
        }],
        paging: false,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        rowReorder: {
            enable: true,
            update: false,
            selector: '.allow-reorder',
            responsive: true
        },
        stateSave: false,
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        createdRow: function(row, data, indice) {
            $(row).attr('data-id', data.id);
        },
        drawCallback: function(settings) {
            _slideManagment.rowReorder.enable();
        }
    });
    _slideManagment.on('row-reorder', function(e, movedElements, element) {
        var finalArray = {};
        $(movedElements).each(function(index, movedElement) {
            var rowData = movedElement.node.dataset;
            finalArray[rowData.id] = {
                'oldPosition': (movedElement.oldPosition + 1),
                'newPosition': (movedElement.newPosition + 1),
            };
        });
        if (Object.keys(finalArray).length > 0) {
            _slideManagment.rowReorder.disable();
            toastr.clear();
            $.ajax({
                url: url.reorderingScreen,
                type: 'POST',
                dataType: 'json',
                data: {
                    positions: finalArray,
                    type: ($('#myTab').find('.active').attr('aria-controls') || 'app')
                },
            }).done(function(data) {
                if (data && data.status === true) {
                    toastr.success(data.message);
                } else {
                    toastr.error((data.message) ? data.message : message.something_wrong);
                }
            }).fail(function(error) {
                toastr.error((error.message) ? error.message : message.something_wrong);
            }).always(function() {
                _slideManagment.ajax.reload(null, false);
            });
        }
    });
});
$(document).on('click', '#slideDelete', function(t) {
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
        url: url.appSlidesDelete + '/' + objectId,
        data: null,
        crossDomain: true,
        cache: false,
        contentType: 'json',
        success: function(data) {
            $('#slideManagment').DataTable().ajax.reload(null, false);
            if (data['deleted'] == 'true') {
                toastr.success(message.onboarding_side_deleted);
            } else {
                toastr.error(message.delete_error);
            }
            var targetType = $('#myTab.nav-tabs a.active').attr('aria-controls');
            $('#app-tab').attr('app-count', data['onBoardingappCount']);
            $('#portal-tab').attr('portal-count', data['onBoardingportalCount']);
            if (targetType == 'app' && data['onBoardingappCount'] >= 3) {
                $('#addOnBoardingBtn').hide();
            } else if (targetType == 'portal' && data['onBoardingportalCount'] >= 3) {
                $('#addOnBoardingBtn').hide();
            } else {
                $('#addOnBoardingBtn').show();
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
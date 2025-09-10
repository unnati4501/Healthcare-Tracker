$(document).ready(function() {
    var _bannerManagment = null;
    $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    _bannerManagment = $('#dtBannerManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                company: companyId,
                companyType : $("#companyType").val(),
                getQueryString: window.location.search
            },
        },
        columns: [
            {
                data: 'updated_at', name: 'updated_at' , visible: false
            },
            {
                data: 'order_priority',
                name: 'order_priority',
                className: 'allow-reorder'
            },
            { 
                data: 'description',
                name: 'description',
                className: 'allow-reorder'
            },
            {   data: 'banner',
                name: 'banner',
                searchable: false,
                sortable: false,
                className: 'allow-reorder'
            },
            {data: 'actions', name: 'actions', searchable: false, sortable: false}

        ],
        paging: false,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [
            [1, 'asc']
        ],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        rowReorder: {
            enable: true,
            update: false,
            selector: '.allow-reorder',
            responsive: true
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
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
            _bannerManagment.rowReorder.enable();
        }
    });

    _bannerManagment.on('row-reorder', function(e, movedElements, element) {
        var finalArray = {};
        $(movedElements).each(function(index, movedElement) {
            var rowData = movedElement.node.dataset;
            finalArray[rowData.id] = {
                'oldPosition': (movedElement.oldPosition + 1),
                'newPosition': (movedElement.newPosition + 1),
            };
        });
        if (Object.keys(finalArray).length > 0) {
            _bannerManagment.rowReorder.disable();
            toastr.clear();
            $.ajax({
                url: url.reorderingScreen,
                type: 'POST',
                dataType: 'json',
                data: {
                    positions: finalArray,
                    companyId: companyId
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
                _bannerManagment.ajax.reload(null, false);
            });
        }
    });

    $(document).on('click', '.bannerDelete', function (t) {
        $('#delete-model-box').data("id", $(this).data('id'));
        $('#delete-model-box').modal('show');
    });

    $(document).on('click', '#delete-model-box-confirm', function (e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#delete-model-box').data("id");
        $.ajax({
            type: 'DELETE',
            url: url.deleteBanner.replace(':id', objectId),
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function (data) {
                $('#dtBannerManagement').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(messages.banner_deleted);
                } else if (data['deleted'] == 'use') {
                    toastr.error(messages.required_one_banner);
                } else {
                    toastr.error(messages.delete_error);
                }
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function (data) {
                if (data == 'Forbidden') {
                    toastr.error("delete error.");
                }
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });
});
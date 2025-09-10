$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#mapLibrary').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
        },
        columns: [{
            data: 'image',
            searchable: false,
            sortable: false,
        }, {
            data: 'name',
            name: 'name'
        }, {
            data: 'total_distance',
            name: 'total_distance'
        }, {
            data: 'locations',
            name: 'locations'
        }, {
            data: 'description',
            name: 'description',
            visible: (data.roleGroup == 'company')
        }, {
            data: 'status',
            name: 'status',
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
        ordering: true,
        order: [],
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
    $('#map_location_name').DataTable({
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'location_name',
            name: 'location_name'
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
    $(document).on('click', '.preview_map_locations', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if(_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));

            $(_data).each(function(index, el) {
                console.log(index, el);
                dataJson.push({
                    id: (index + 1),
                    location_name: el.location_name
                });
            });
            $('#map_location_name').DataTable()
                .clear()
                .rows
                .add(dataJson)
                .order([0, 'asc'])
                .search('')
                .draw();
            $('#map_location_name_preview').modal('show')
        }
    });
    $(document).on('click', '.delete-map', function(t) {
        $('#delete-model-box').data("id", $(this).data('id'));
        $('#delete-model-box').modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        var _this = $(this),
            objectId = $('#delete-model-box').data("id");
        _this.prop('disabled', true);
        $('.page-loader-wrapper').show();
        $.ajax({
            type: 'DELETE',
            url: url.delete + `/${objectId}`,
            crossDomain: true,
            cache: false,
            contentType: 'json'
        }).done(function(data) {
            $('#mapLibrary').DataTable().ajax.reload(null, false);
            if (data.deleted == true) {
                toastr.success(message.deleted);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        }).fail(function(data) {
            if (data == 'Forbidden') {
                toastr.error(message.somethingWentWrong);
            }
        }).always(function() {
            _this.prop('disabled', false);
            $('#delete-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });
    $(document).on('click', '.archive-map', function(t) {
        $('#archive-model-box').data("id", $(this).data('id'));
        $('#archive-model-box').modal('show');
    });
    $(document).on('click', '#archive-model-box-confirm', function(e) {
        var _this = $(this),
            objectId = $('#archive-model-box').data("id");
        _this.prop('disabled', true);
        $('.page-loader-wrapper').show();
        $.ajax({
            type: 'DELETE',
            url: url.archive + `/${objectId}`,
            crossDomain: true,
            cache: false,
            contentType: 'json'
        }).done(function(data) {
            $('#mapLibrary').DataTable().ajax.reload(null, false);
            if (data.archived == true) {
                toastr.success(message.archived);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        }).fail(function(data) {
            if (data == 'Forbidden') {
                toastr.error(message.somethingWentWrong);
            }
        }).always(function() {
            _this.prop('disabled', false);
            $('#archive-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });
    $(document).on('click', '.view-map', function() {
        var id = $(this).data('id');
        $('.page-loader-wrapper').show();
        locationArray = [];
        initMap();
        $.ajax({
            type: 'GET',
            url: url.getMapLocation + `/${id}`,
            crossDomain: true,
            cache: false,
            contentType: 'json'
        }).done(function(data) {
            $('#view-model-box').data("id", id);
            $(data).each(function(key, value) {
                locationArray.push(new google.maps.LatLng(value.lat, value.lng));
            });
            initMap();
            $('.page-loader-wrapper').hide();
            $('#view-model-box').modal('show');
        }).fail(function(data) {
            if (data == 'Forbidden') {
                toastr.error(message.somethingWentWrong);
            }
            $('#view-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        }).always(function() {
            $('#view-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });
    $(document).on('click', '.preview_description', function() {
        var id = $(this).data('cid');
        var description = $(this).data('rowdata');
        $('#view-description').html(description);
        $('#view-description-model-box').modal('show');
    });
});
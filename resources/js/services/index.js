$(document).ready(function() {
    $('#serviceManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: servicesListUrl,
            data: {
                status: 1,
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false
        },
        { 
            data:'logo', searchable : false, className: 'text-center',
            render: function (data, type) {
                let hideIconCls="";
                if(data.icon == null || data.icon.match("boxed-bg.png")){
                    hideIconCls="hide";
                }
                return `<div class="table-img table-img-l"><img src="${data.logo}" /><span class="logo-overlay ${hideIconCls}" style="background-image: url(${data.icon});"></span></div>`;
            }
        },
        {
            data: 'name',
            name: 'name'
        }, {
            data: 'service_type',
            name: 'service_type'
        }, {
            data: 'subcategory',
            name: 'subcategory',
            sortable: false
        },
        {
            data: 'wellbeing_specialist',
            name: 'wellbeing_specialist',
            searchable: false,
            sortable: false
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false
        }],
        paging: true,
        pageLength: pagination,
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
            targets: 3,
            className: 'text-center',
        }],
        stateSave: false,
        language: {
            paginate: {
                previous: "<i class='far fa-angle-left page-arrow align-middle me-2'></i><span class='align-middle'>Prev</span>",
                next: "<span class='align-middle'>Next</span><i class='far fa-angle-right page-arrow align-middle ms-2'></i> "
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });

    $(document).on('click', '#serviceDelete', function(t) {
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
            url: serviceDeleteUrl + '/' + objectId,
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function(data) {
                $('#serviceManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(deletedMessage);
                } else if (data['deleted'] == 'use') {
                    toastr.error(inUseMessage);
                }  else {
                    toastr.error(somethingWentWrongMessage);
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function(data) {
                if (data == 'Forbidden') {
                    toastr.error(unauthorizedMessage);
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });

    $(document).on('click', '.preview_subcategories', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if(_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));
            $(_data).each(function(index, el) {
                dataJson.push({
                    id: (index + 1),
                    subcategory: el.name
                });
            });
            $('#visible_to_subcategory_tbl').DataTable()
                .clear()
                .rows
                .add(dataJson)
                .order([0, 'asc'])
                .search('')
                .draw();
            $('#subcategory_visibility_preview').modal('show')
        }
    });

    $('#visible_to_subcategory_tbl').DataTable({
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'subcategory',
            name: 'subcategory'
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

    $(document).on('click', '.preview_wellbeing_specialist', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if(_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));
            console.log(_data);
            $(_data).each(function(index, el) {
                dataJson.push({
                    id: (index + 1),
                    wellbeing_sp: el.name
                });
            });
            $('#visible_to_wellbeing_specialist_tbl').DataTable()
                .clear()
                .rows
                .add(dataJson)
                .order([0, 'asc'])
                .search('')
                .draw();
            $('#wellbeing_specialist_visibility_preview').modal('show')
        }
    });

    $('#visible_to_wellbeing_specialist_tbl').DataTable({
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'wellbeing_sp',
            name: 'wellbeing_sp'
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
});
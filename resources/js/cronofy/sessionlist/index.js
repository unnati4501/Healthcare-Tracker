$(document).ready(function() {
    $('.usersearch').hide();
    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results==null) {
           return null;
        }
        return decodeURI(results[1]) || 0;
    }
    
    var currentTab = $.urlParam('tab');
    if (currentTab == 'single') {
        $("#tab").val('single');
        $('.nav-link').removeClass('active');
        $("#tab-single-session").addClass('active');

        $('.usersearch').show();
    } else if(currentTab == 'group') {
        $("#tab").val('group');
        $('.nav-link').removeClass('active');
        $("#tab-group-session").addClass('active');
        $('.usersearch').hide();
    } else {
        if(roleSlug == 'wellbeing_team_lead' || roleSlug == 'wellbeing_specialist'){
            $("#tab").val('single');
            $("#tab-single-session").addClass('active');
            $('.usersearch').show();
        }else{
            $("#tab").val('none');
            $('.usersearch').hide();
        }
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    var subCategories = null;
    $('#sessionManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.datatable,
            data: {
                tab:$("#tab").val(),
                getQueryString: window.location.search,
                service: $('#service').val(),
                sub_category: $('#sub_category').val(),
                user: $('#user').val(),
                ws: $('#ws').val(),
                company: $('#company').val(),
                duration: $('#duration').val(),
                status: $('#status').val(),
            },
        },
        columns: [{
            data: 'client_name',
            name: 'client_name',
            visible: ((roleSlug == 'wellbeing_team_lead' || roleSlug == 'wellbeing_specialist') && $("#tab").val() == 'single'),
        },{
            data: 'client_email',
            name: 'client_email',
            visible: ((roleSlug == 'wellbeing_team_lead' || roleSlug == 'wellbeing_specialist') && $("#tab").val() == 'single'),
        },
        {
            data: 'client_timezone',
            name: 'client_timezone',
            visible: ((roleSlug == 'wellbeing_team_lead' || roleSlug == 'wellbeing_specialist') && $("#tab").val() == 'single'),
        },
        {
            data: 'company',
            name: 'company',
            visible: (roleSlug == 'wellbeing_specialist' || roleSlug == 'wellbeing_team_lead' || roleSlug == 'super_admin' || (roleGroup == 'reseller' && isParentCompany == '')) // 
        },
        {
            data: 'name',
            name: 'name',
        }, {
            data: 'sub_category',
            name: 'sub_category',
        },  {
            data: 'participants',
            name: 'participants',
            className: 'text-center',
            sortable: false,
            visible : ($("#tab").val() != 'single'),
        }, {
            data: 'wellbeing_specialist',
            name: 'wellbeing_specialist',
            visible: (roleGroup == 'company' || roleSlug == 'super_admin' || roleSlug == 'wellbeing_team_lead' || roleGroup == 'reseller' )
        }, {
            data: 'start_time',
            name: 'start_time',
            sType: "date" , 
            bSortable: "true"
        }, {
            data: 'status',
            name: 'status'
        }, {
            data: 'actions',
            name: 'actions',
            className: 'text-center',
            searchable: false,
            sortable: false
        }],
        order: [],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        info: true,
        autoWidth: false,
        columnDefs: [
            {
                targets: 'no-sort',
                orderable: false,
            }
        ],
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false
    });


    $('#sessionManagementWbtl').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.datatable,
            data: {
                tab:$("#tab").val(),
                getQueryString: window.location.search,
                service: $('#service').val(),
                sub_category: $('#sub_category').val(),
                user: $('#user').val(),
                ws: $('#ws').val(),
                company: $('#company').val(),
                duration: $('#duration').val(),
                status: $('#status').val(),
            },
        },
        columns: [{
            data: 'client_name',
            name: 'client_name',
            visible: (roleSlug == 'wellbeing_team_lead' && $("#tab").val() == 'single'),
        },{
            data: 'client_email',
            name: 'client_email',
            visible: (roleSlug == 'wellbeing_team_lead' && $("#tab").val() == 'single'),
        },{
            data: 'client_timezone',
            name: 'client_timezone',
            visible: (roleSlug == 'wellbeing_team_lead' && $("#tab").val() == 'single'),
        },
        {
            data: 'company',
            name: 'company',
        },
        {
            data: 'wellbeing_specialist',
            name: 'wellbeing_specialist',
        },{
            data: 'name',
            name: 'name',
        }, {
            data: 'sub_category',
            name: 'sub_category',
        },{
            data: 'participants',
            name: 'participants',
            className: 'text-center', 
            sortable: false,
            visible : ($("#tab").val() != 'single'),
        }, {
            data: 'start_time',
            name: 'start_time',
        }, {
            data: 'status',
            name: 'status'
        }, {
            data: 'actions',
            name: 'actions',
            className: 'text-center',
            searchable: false,
            sortable: false
        }],
        order: [],
        paging: true,
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
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false
    });

    if(roleSlug == 'wellbeing_team_lead'){
        $('#sessionManagement').addClass('d-none');
        $('#sessionManagementWbtl').removeClass('d-none');
    }

    $('#service').change(function() {
        $('#sub_category').prop({'disabled': false});
        var selectedId = $(this).val();
        var _token = $('input[name="_token"]').val();
        var url = ajaxUrl.getSubCategories.replace(':id', selectedId);
        $.ajax({
            url: url,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(res) {
                $('#sub_category').empty().select2("val", "");
                var num = 0;
                if (res.response.result) {
                    $.each(res.response.subcategory, function(key, value) {
                        if (num <= 0) {
                            subCategories = key;
                            $('#sub_category').append('<option value="' + key + '" selected="selected">' + value + '</option>');
                        } else {
                            $('#sub_category').append('<option value="' + key + '">' + value + '</option>');
                        }
                        num++;
                    });
                    $('#sub_category').val('').select2();
                }
            }
        });
    });
    
    $('#visible_to_participate_tbl').DataTable({
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'name',
            name: 'name'
        }, {
            data: 'email',
            name: 'email'
        }],
        order: [],
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

    $(document).on('click', '.preview_participants', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if(_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));
            $(_data).each(function(index, el) {
                dataJson.push({
                    id: (index + 1),
                    name: el.name,
                    email: el.email
                });
            });
            $('#visible_to_participate_tbl').DataTable()
                .clear()
                .rows
                .add(dataJson)
                .order([0, 'asc'])
                .search('')
                .draw();
            $('#participate_visibility_preview').modal('show')
        }
    });
});

$(document).on('click', '#completeModal', function(e) {
    $('.page-loader-wrapper').show();
    var objectId = $(this).attr("data-id");
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: 'PATCH',
        url: url.complete + '/' + objectId,
        data:null,
        crossDomain: true,
        cache: false,
        contentType: 'json',
        success: function(data) {
            $('#sessionManagement').DataTable().ajax.reload(null, false);
            if (data['completed'] == 'true') {
                toastr.success(message.completed);
            } else {
                toastr.error(message.somethingWentWrong);
            }
            $('.page-loader-wrapper').hide();
        },
        error: function(data) {
            $('#sessionManagement').DataTable().ajax.reload(null, false);
            toastr.error(message.somethingWentWrong);
            $('.page-loader-wrapper').hide();
        }
    });
});
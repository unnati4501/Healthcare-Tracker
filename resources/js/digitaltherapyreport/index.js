$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
	$('.dateranges').datepicker({
        format: "mm/dd/yyyy",
        todayHighlight: false,
        autoclose: true,
    });
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
        $('.createdByDiv').show();
    } else if (currentTab == 'group') {
        $("#tab").val('group');
        $('.nav-link').removeClass('active');
        $("#tab-group-session").addClass('active');
        $('.usersearch').hide();
        $('.createdByDiv').hide();
    } else {
        if (roleSlug == 'wellbeing_team_lead') {
            $("#tab").val('single');
            $("#tab-single-session").addClass('active');
            $('.usersearch').show();
            $('.createdByDiv').show();
        } else {
            $("#tab").val('none');
            $('.usersearch').hide();
            $('.createdByDiv').hide();
        }
    }
    if(roleSlug == 'wellbeing_team_lead'){
        $('#detailedDigitalTherapyReport').addClass('d-none');
        $('#detailedDigitalTherapyReportwbtl').removeClass('d-none');
    }
    $('#detailedDigitalTherapyReport').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.dataTable,
            data: {
                tab:$("#tab").val(),
                company: $('#company').val(),
                dtFromdate: $('#dtFromdate').val(),
                dtTodate: $('#dtTodate').val(),
                dtStatus: $('#dtStatus').val(),
                dtService: $('#dtService').val(),
                user: $('#user').val(),
                wellbeingSpecialist: $('#wellbeingSpecialist').length ? $('#wellbeingSpecialist').val() : null,
                getQueryString: window.location.search
            },
        },
        columns: [
            
            {data: 'company_name', name: 'company_name'},
            {data: 'service_name', name: 'service_name'},
            {data: 'issue', name: 'issue'},
            {data: 'location', name: 'location'},
            {
                data: 'department_name', 
                name: 'department_name'
            },
            {data: 'created_at', name: 'created_at',
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }},
            {data: 'start_time', name: 'start_time',render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }},
            {data: 'duration', name: 'duration'},
            {data: 'mode_of_service', name: 'mode_of_service', sortable : false},
            {data: 'wellbeing_specialist_name', name: 'wellbeing_specialist_name'},
            {data: 'ws_timezone', name: 'ws_timezone'},
            {data: 'ws_shift', name: 'ws_shift', sortable : false},
            {data: 'number_of_participants', name: 'number_of_participants', sortable : false},
            {data: 'status', name: 'status'},
        ],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#exportdigitaltherapyReportbtn").hide();
            }else {
                $("#exportdigitaltherapyReportbtn").show();
            }
        },
        paging: true,
        pageLength: parseInt(pagination.value),
        lengthChange: true,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        searching: false,
        ordering: true,
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
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        }
    });
    $('#detailedDigitalTherapyReportwbtl').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.dataTable,
            data: {
                tab:$("#tab").val(),
                company: $('#company').val(),
                dtFromdate: $('#dtFromdate').val(),
                dtTodate: $('#dtTodate').val(),
                dtStatus: $('#dtStatus').val(),
                dtService: $('#dtService').val(),
                user: $('#user').val(),
                created_by: $('#created_by').val(),
                wellbeingSpecialist: $('#wellbeingSpecialist').length ? $('#wellbeingSpecialist').val() : null,
                getQueryString: window.location.search
            },
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        columns: [
            {
                data: 'client_name',
                name: 'client_name',
                visible: (roleSlug == 'wellbeing_team_lead' && $("#tab").val() == 'single'),
            },{
                data: 'client_email',
                name: 'client_email',
                visible: (roleSlug == 'wellbeing_team_lead' && $("#tab").val() == 'single'),
            },
            {data: 'company_name', name: 'company_name'},
            {data: 'service_name', name: 'service_name'},
            {data: 'issue', name: 'issue'},
            {data: 'location', name: 'location'},
            {
                data: 'department_name', 
                name: 'department_name'
            },
            {data: 'created_at', name: 'created_at',
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }},
            {data: 'start_time', name: 'start_time',render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }},
            {data: 'duration', name: 'duration'},
            {data: 'mode_of_service', name: 'mode_of_service', sortable : false},
            {data: 'wellbeing_specialist_name', name: 'wellbeing_specialist_name'},
            {data: 'ws_timezone', name: 'ws_timezone'},
            {data: 'ws_shift', name: 'ws_shift', sortable : false},
            {data: 'number_of_participants', name: 'number_of_participants', sortable : false, visible : ($("#tab").val() != 'single')},
            {data: 'status', name: 'status'},
            {data: 'created_by', name: 'created_by', visible : ($("#tab").val() == 'single')}
        ],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#exportdigitaltherapyReportbtn").hide();
            }else {
                $("#exportdigitaltherapyReportbtn").show();
            }
        },
        paging: true,
        pageLength: parseInt(pagination.value),
        lengthChange: true,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
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
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        }
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
    $(document).on('click', '#exportdigitaltherapyReportbtn', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        $('#email').val(loginemail).removeClass('error');
        $('#company_popup').val($('#company').val());
        $('#dt_fromdate_popup').val($('#dtFromdate').val());
        $('#dt_todate_popup').val($('#dtTodate').val());
        $('#dt_status_popup').val($('#dtStatus').val());
        $('#dt_service_popup').val($('#dtService').val());
        $('#dt_tab_popup').val($('#tab').val());
        $('#dt_created_by').val($('#created_by').val());
        $('#wellbeing_specialist_popup').val($('#wellbeingSpecialist').val());
        $('#user_popup').val($('#user').val());
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });
    $('#exportDigitalTherapyReport').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#exportDigitalTherapyReport .card-footer button, .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#exportDigitalTherapyReport .card-footer button, .card-footer a').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#exportDigitalTherapyReport .card-footer button, .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#exportDigitalTherapyReport .card-footer button, .card-footer a').removeAttr('disabled');
        }
    });
});
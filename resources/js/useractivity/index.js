$(document).ready(function() {
    
    $('.dateranges').datepicker({
        format: "mm/dd/yyyy",
        todayHighlight: false,
        autoclose: true,
    });

    stepSearch();

    $('a[data-bs-toggle="tab"]').on('click', function (e) {
        var id = $(this).attr("href");
        // $('.form-group input[type="text"]').val('');
        if(id == '#steps'){
            $("#isFiltered").val(0);
            stepSearch();
        } else if(id == '#exercises'){
            $("#isFiltered").val(0);
            exercisesSearch();
        } else if(id == '#meditations'){
            $("#isFiltered").val(0);
            meditationsSearch();
        }
    });
    $(document).on('keypress',function(e) {
        if(e.which == 13) {
            event. preventDefault();
            var id = $('#userActivityTab').find('.active').attr("href");
            if(id == '#steps'){
               stepSearch();
            }
            if(id == '#exercises'){
                exercisesSearch();
            }
            if(id == '#meditations'){
                meditationsSearch();
            }
        }
    });
    $(document).on('click','#stepSearch',function(){
        $("#isFiltered").val(1);
        stepSearch();
    });
    $(document).on('click','#resetStepSearch',function(){
        $("#isFiltered").val(0);
        resetStepSearch();
    });

    $(document).on('click','#exercisesSearch',function(){
        $("#isFiltered").val(1);
        exercisesSearch();
    });
    $(document).on('click','#resetExercisesSearch',function(){
        $("#isFiltered").val(0);
        resetExercisesSearch();
    });

    $(document).on('click','#meditationsSearch',function(){
        $("#isFiltered").val(1);
        meditationsSearch();
    });
    $(document).on('click','#resetMeditationsSearch',function(){
        $("#isFiltered").val(0);
        resetMeditationsSearch();
    });

    $('.daterangesFromExportModel').datepicker({
        format: "yyyy-mm-dd",
        todayHighlight: false,
        autoclose: true,
        endDate: new Date(),
        clearBtn: true,
    });

    $(document).on('click', '#exportUserActivityReport', function(t) {
        var userActivityForm = $( ".userActivityForm" ).serialize();
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        var tab = $(this).attr('data-tab');
        $('#email').val(loginemail).removeClass('error');
        $("#tab").val(tab);
        
        if(tab == 'steps'){
            $("#start_date_label").html('Steps Log Date From');
            $("#to_date_label").html('Steps Log Date To');
            
        }else if(tab == 'exercise'){
            $("#start_date_label").html('From Date');
            $("#to_date_label").html('To date');
        }else{
            $("#start_date_label").html('Log Date From');
            $("#to_date_label").html('Log Date To');
        }

        if($("#isFiltered").val() == '1'){
            $('#queryString').val(JSON.stringify(queryStringToObject('?'+userActivityForm)));
        }else{
            $('#queryString').val('');
            $('.daterangesFromExportModel').show();
        }

        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $("#exportNpsReport").attr('action', url.userActivityExportUrl);
        $('.loadingMsg').remove();
        $('#export-model-box-confirm').prop('disabled', false);
        $('#exportNpsReportMsg').hide();
        $('#exportNps').show();
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $('#exportNpsReport').validate({
        errorClass: 'error text-danger',
        errorElement: 'span',
        highlight: function(element, errorClass, validClass) {
            $('span#email-error').addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function(element, errorClass, validClass) {
            $('span#email-error').removeClass(errorClass).addClass(validClass);
        },
        rules: {
            email: {
                email: true,
                required: true
            }
        }
    });
    $('#exportNpsReport').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
        }
    });

    
});

function stepSearch() {
    $('#stepTextSearchTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: url.getUserStepsData,
            data: {
                status: 1,
                searchText: $('#stepTextSearch').val(),
                fromdate: $("#dtFromdate").val(),
                todate: $("#dtTodate").val(),
            },
        },
        columns: [
            {data: 'id', name: 'id' , visible: false},
            {data: 'fullName', name: 'fullName'},
            {data: 'email', name: 'email'},
            {data: 'tracker', name: 'tracker'},
            {data: 'steps', name: 'steps'},
            {data: 'distance', name: 'distance'},
            {data: 'calories', name: 'calories'},
            {
                data: 'log_date',
                name: 'log_date',
                render: function (data, type, row) {
                    return moment.utc(row.log_date).format(date_format);
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                render: function (data, type, row) {
                    return moment.utc(row.created_at).format(date_format);
                }
            },
            {
                data: 'step_authentication',
                name: 'step_authentication',
                searchable: false,
                sortable: false
            }
        ],
        drawCallback: function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $(".exportUserActivityStepReport").hide();
            }else {
                $(".exportUserActivityStepReport").show();
            }
        },
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        searching: false,
        ordering: true,
        order: [[0, 'desc']],
        info: true,
        autoWidth: false,
        stateSave: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next,
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        }
    });
    
    $("#stepSearch").val($('#stepTextSearch').val());
}

function resetStepSearch() {
    $('#stepTextSearch').val('');
    $('#dtFromdate, #dtTodate').val('');
    stepSearch();
}

function exercisesSearch() {
    $('#exercisesReportListTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: url.getUserExercisesData,
            data: {
                status: 1,
                searchText: $('#exercisesTextSearch').val(),
                fromdate: $("#exerciseFromdate").val(),
                todate: $("#exerciseTodate").val(),
            },
        },
        columns: [
            {data: 'id', name: 'id' , visible: false},
            {data: 'fullName', name: 'fullName'},
            {data: 'email', name: 'email'},
            {data: 'tracker', name: 'tracker'},
            {data: 'title', name: 'title'},
            {data: 'distance', name: 'distance'},
            {data: 'calories', name: 'calories'},
            {data: 'duration', name: 'duration'},
            {
                data: 'start_date',
                name: 'start_date',
                render: function (data, type, row) {
                    return `${moment.utc(row.start_date).format(date_format)} - ${moment.utc(row.end_date).format(date_format)}`;
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                render: function (data, type, row) {
                    return moment.utc(row.created_at).format(date_format);
                }
            }
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        searching: false,
        ordering: true,
        order: [[0, 'desc']],
        info: true,
        autoWidth: false,
        stateSave: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next,
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        },
        drawCallback: function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $(".exportUserActivityExerciseReport").hide();
            }else {
                $(".exportUserActivityExerciseReport").show();
            }
        },
    });
}

function resetExercisesSearch() {
    $('#exercisesTextSearch').val('');
    $('#exerciseFromdate, #exerciseTodate').val('');
    exercisesSearch();
}

function meditationsSearch()
{
    $('#meditationsReportListTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: url.getUserMeditationsData,
            data: {
                status: 1,
                searchText: $('#meditationsTextSearch').val(),
                fromdate: $("#meditationFromdate").val(),
                todate: $("#meditationTodate").val(),
            },
        },
        columns: [
            {data: 'id', name: 'id' , visible: false},
            {data: 'fullName', name: 'fullName'},
            {data: 'email', name: 'email'},
            {data: 'title', name: 'title'},
            {data: 'duration_listened', name: 'duration_listened'},
            {
                data: 'created_at',
                name: 'created_at',
                render: function (data, type, row) {
                    return moment.utc(row.created_at).format(date_format);
                }
            }
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        searching: false,
        ordering: true,
        order: [[0, 'desc']],
        info: true,
        autoWidth: false,
        stateSave: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next,
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        },
        drawCallback: function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $(".exportUserActivityMeditationReport").hide();
            }else {
                $(".exportUserActivityMeditationReport").show();
            }
        },
    });
}

function resetMeditationsSearch() {
    $('#meditationsTextSearch').val('');
    $('#meditationFromdate, #meditationTodate').val('');
    meditationsSearch();
}

function queryStringToObject(queryString) {
    $('.daterangesFromExportModel').show();
    const pairs = queryString.substring(1).split('&');
    var array = pairs.map((el) => {
      const parts = el.split('=');
      if(parts[1] != ''){
        $('.daterangesFromExportModel').hide();
      }
      return parts;
    });
    return Object.fromEntries(array);
  }
  


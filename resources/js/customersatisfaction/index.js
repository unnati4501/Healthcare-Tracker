$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    if (window.location.hash) {
        var hsahq = window.location.hash;
        if ($('.nav-tabs a[href="' + hsahq + '"]').length > 0) {
            $('.nav-tabs a[href="' + hsahq + '"]').click();
        }
    }
    if ($('.nav-tabs a[href="#appTab"]').length >= 1) {
        npsSearch();
        appChart(chartJson);
    } else if ($('.nav-tabs a[href="#portalTab"]').length >= 1) {
        npsSearchPortal();
        portalChart(chartJsonPortal);
    } else {
        projectSearch();
    }
    $('#start_date').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd',
    }).on('changeDate', function() {
        var stdate = new Date();
        if ($(this).val() != '') stdate = $(this).val();
        $('#end_date').datepicker('setStartDate', new Date(stdate));
        if (new Date($('#end_date').val()) < new Date($('#start_date').val())) {
            $('#end_date').val('');
            $('#end_date').datepicker('setDate', null);
        }
        $('#start_date').valid();
    });
    $('#end_date').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd',
    }).on('changeDate', function() {
        $('#end_date').valid();
    });
    Highcharts.wrap(Highcharts.seriesTypes.pie.prototype, 'render', function(proceed) {
        proceed.call(this);
        if (!this.circle) {
            this.circle = this.chart.renderer.circle(0, 0, 0).add(this.group);
        }
        if (this.total === 0) {
            this.circle.attr({
                cx: this.center[0],
                cy: this.center[1],
                r: this.center[2] / 2,
                fill: 'none',
                stroke: 'silver',
                'stroke-width': 2
            });
        } else {
            this.circle.attr({
                'stroke-width': 0
            });
        }
    });
    $(document).on('click', '#npsSearch', function() {
        $("#isFiltered").val(1);
        npsSearch();
    });
    $(document).on('click', '#resetNPSSearch', function() {
        $("#isFiltered").val(0);
        resetNPSSearch();
    });
    $(document).on('click', '#npsSearchPortal', function() {
        $("#isFiltered").val(1);
        npsSearchPortal();
    });
    $(document).on('click', '#resetNPSSearchPortal', function() {
        $("#isFiltered").val(0);
        resetNPSSearchPortal();
    });
    $(document).on('click', '#projectSearch', function() {
        $("#isFiltered").val(1);
        projectSearch();
    });
    $(document).on('click', '#resetprojectSearch', function() {
        $("#isFiltered").val(0);
        resetprojectSearch();
    });
    $(document).on('change', '#projectList', function() {
        getGraphData();
    });
    $(document).on('click', '#copySurveyLink', function() {
        var url = $(this).data('url');
        copySurveyLink(url);
    });

    $('.daterangesFromExportModel').datepicker({
        format: "yyyy-mm-dd",
        todayHighlight: false,
        autoclose: true,
        endDate: new Date(),
        clearBtn: true,
    });

    $(document).on('click', '#appExport', function(t) {
        var isPortal = $(this).data('isportal');
        var activeTab = $(this).data('tab');
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        $("#model-title").html($(this).data('title'));
        $('#email').val(loginemail).removeClass('error');
        if(isPortal == 0){
            $('#queryString').val("");
            var npsExportForm = $(".npsAppForm" ).serialize();
        }else{
            $('#queryString').val("");
            var npsExportForm = $(".npsPortalForm" ).serialize();
        }
        $('#isPortal').val(isPortal);
        $('.error').remove();

        if(activeTab !='undefined' && activeTab == 'project'){
            $("#exportNpsReport").attr('action', url.npsProjectExportUrl);
            var npsExportForm = $(".npsProjectForm" ).serialize();
        }
        //$('#queryString').val(JSON.stringify(queryStringToObject('?'+npsExportForm))); 

        if($("#isFiltered").val() == '1'){
            $('#queryString').val(JSON.stringify(queryStringToObject('?'+npsExportForm)));
        }else{
            $('#queryString').val('');
            $('.daterangesFromExportModel').show();
        }

        $('.loadingMsg').remove();
        $('#export-model-box-confirm').prop('disabled', false);
        $('#exportNpsReportMsg').hide();
        $('#exportNps').show();
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
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

function npsSearch() {
    $('#NPSFeedBackTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: url.getNpsData,
            data: {
                status: 1,
                isPortal: '0',
                company: $('#company').val(),
                feedBackType: $('#feedBackType').val(),
                getQueryString: window.location.search,
            },
        },
        columns: [{
            data: 'survey_received_on',
            name: 'survey_received_on',
            class: 'hidden',
            visible: false
        }, {
            data: 'companyName',
            name: 'companyName'
        }, {
            data: 'logo',
            name: 'logo'
        }, {
            data: 'feedback_emoji',
            name: 'feedback_emoji'
        }, {
            data: 'feedback',
            name: 'feedback'
        }, {
            data: 'survey_received_on',
            name: 'survey_received_on',
            render: function(data, type, row) {
                return moment.utc(row.survey_received_on).format(date_format);
            }
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [
            [25, 50, 100],
            [25, 50, 100]
        ],
        searching: false,
        ordering: true,
        order: [
            [0, 'desc']
        ],
        info: true,
        autoWidth: false,
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        buttons: [
           /* {
            extend: 'excel',
            text: button.export,
            className: 'btn btn-primary',
            title: 'AppTab Feedback' + Date.now(),
            download: 'open',
            orientation: 'landscape',
            exportOptions: {
                columns: [1, 3, 4, 5],
                order: 'current',
            }
        }*/
        ],
        drawCallback: function(settings) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#app-tab-result-block .appExport").hide();
            }else {
                $("#app-tab-result-block .appExport").show();
            }
        }
    });
}

function npsSearchPortal() {
    $('#NPSFeedBackTablePortal').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: url.getNpsData,
            data: {
                status: 1,
                isPortal: '1',
                company: $('#companyPortal').val(),
                feedBackType: $('#feedBackTypePortal').val()
            },
        },
        columns: [{
            data: 'survey_received_on',
            name: 'survey_received_on',
            class: 'hidden',
            visible: false
        }, {
            data: 'companyName',
            name: 'companyName'
        }, {
            data: 'logo',
            name: 'logo'
        }, {
            data: 'feedback_emoji',
            name: 'feedback_emoji'
        }, {
            data: 'feedback',
            name: 'feedback'
        }, {
            data: 'survey_received_on',
            name: 'survey_received_on',
            render: function(data, type, row) {
                return moment.utc(row.survey_received_on).format(date_format);
            }
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [
            [25, 50, 100],
            [25, 50, 100]
        ],
        searching: false,
        ordering: true,
        order: [
            [0, 'desc']
        ],
        info: true,
        autoWidth: false,
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        buttons: [],
        drawCallback: function(settings) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#portal-tab-result-block .portalExport").hide();
            }else {
                $("#portal-tab-result-block .portalExport").show();
            }
        }
    });
}

function projectSearch() {
    $('#ProjectFeedBackTable').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            type: "POST",
            url: url.getProjectData,
            data: {
                status: 1,
                projecttextSearch: $('#projecttextSearch').val(),
                projectStatus: $('#projectStatus').val(),
                projectcompany: (data.isSuperAdmin) ? $('#projectcompany').val() : [],
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                getQueryString: window.location.search,
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            class: 'hidden',
            visible: false
        }, {
            data: 'title',
            name: 'title'
        }, {
            data: 'type',
            name: 'type'
        }, {
            data: 'start_date',
            name: 'start_date'
        }, {
            data: 'end_date',
            name: 'end_date'
        }, {
            data: 'response',
            name: 'response'
        }, {
            data: 'proj_status',
            name: 'proj_status'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false
        }],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [
            [25, 50, 100],
            [25, 50, 100]
        ],
        searching: false,
        ordering: true,
        order: [
            [0, 'desc']
        ],
        info: true,
        autoWidth: false,
        stateSave: false,
        columnDefs: [{
            targets: 7,
            orderable: false,
        }],
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        buttons: [],
        drawCallback: function(settings) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#project-tab-result-block .projectExport").hide();
            }else {
                $("#project-tab-result-block .projectExport").show();
            }
        }
    });
}

function resetNPSSearch() {
    $('#company').val('').trigger('change');
    $('#feedBackType').val('').trigger('change');
    npsSearch();
}

function resetNPSSearchPortal() {
    $('#companyPortal').val('').trigger('change');
    $('#feedBackTypePortal').val('').trigger('change');
    npsSearchPortal();
}

function resetprojectSearch() {
    $('#projecttextSearch').val('');
    $('#start_date').val('');
    $('#end_date').val('');
    if (data.isSuperAdmin) {
        $('#projectcompany').val('').trigger('change');
    }
    $('#projectStatus').val('').trigger('change');
    projectSearch();
}
$('a[data-bs-toggle="tab"]').on('click', function(e) {
    var id = $(this).attr("href");
    if (id == '#appTab') {
        $('.form-group input[type="text"]').val('');
        $('#company').val('').trigger('change');
        $('#feedBackType').val('').trigger('change');
        npsSearch();
        appChart(chartJson);
    } else if (id == '#portalTab') {
        $('.form-group input[type="text"]').val('');
        $('#companyPortal').val('').trigger('change');
        $('#feedBackTypePortal').val('').trigger('change');
        portalChart(chartJsonPortal);
        npsSearchPortal();
    }
    if (id == '#projectTab') {
        resetprojectSearch();
    }
});
$(document).on('click', '#projectSurveyDelete', function(t) {
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
        url: url.projectSurveyDelete + '/' + objectId,
        data: null,
        crossDomain: true,
        cache: false,
        contentType: 'json',
        success: function(data) {
            $('#ProjectFeedBackTable').DataTable().ajax.reload(null, false);
            if (data['deleted'] == 'true') {
                toastr.success(message.project_survey_deleted);
            } else if (data['deleted'] == 'use') {
                toastr.error(message.project_survey_is_use);
            } else {
                toastr.error(message.unable_delete_project_data);
            }
            var deleteConfirmModalBox = '#delete-model-box';
            $(deleteConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        },
        error: function(data) {
            $('#ProjectFeedBackTable').DataTable().ajax.reload(null, false);
            toastr.error(message.unable_delete_project_data);
            var deleteConfirmModalBox = '#delete-model-box';
            $(deleteConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        }
    });
});

function copySurveyLink(link) {
    var x = document.createElement("INPUT");
    $("body").append(x);
    x.setAttribute("type", "text");
    x.setAttribute("value", link);
    x.select();
    document.execCommand("copy");
    toastr.success(message.survey_link_copied);
}

function getGraphData() {
    if ($('#projectList option:selected').val() != '') {
        var _token = $('input[name="_token"]').val();
        $.ajax({
            type: 'GET',
            url: url.getGraphData + '/' + $('#projectList option:selected').val(),
            data: {
                _token: _token
            },
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function(result) {
                var data = JSON.parse(result.result);
                projectChart(data);
                if (result.result != '' && result.result != null && result.result != undefined && data.length > 0) {
                    toastr.success(message.graph_data_get_successfully);
                } else {
                    toastr.error(message.unable_get_graph_data);
                }
            },
            error: function(data) {
                projectChart([]);
                toastr.error(message.unable_get_graph_data);
            }
        });
    } else {
        projectChart([]);
    }
}

function appChart(chartJsonApp) {
    if (chartJsonApp.length > 0) {
        var graphTemplate = $('#appGraphTemplate').text().trim(),
            graphBarTemplate = $('#appgraphBarTemplate').text().trim(),
            graphLegendTemplate = $('#appgraphLegendTemplate').text().trim(),
            bars = legends = "";
        $('#app-graph-loader').show();
        $('#app-graph-area').empty();
        $(chartJsonApp).each(function(index, bar) {
            bars += graphBarTemplate.replace(/\#feedbackClass#/g, bar.class + ((length == index && bar.y <= 5) ? " small" : "")).replace(/\#percentage#/g, bar.y.toFixed(2)).replace(/\#tooltip#/g, `${bar.name}: ${bar.y.toFixed(2)}%`);
            legends += graphLegendTemplate.replace(/\#feedbackClass#/g, bar.class).replace(/\#feedbackName#/g, bar.name);
        });
        graphTemplate = graphTemplate.replace('#bar#', bars).replace('#legend#', legends);
        $('#app-graph-area').html(graphTemplate);
        $('#app-graph-loader').hide();
    } else {
        $('#app-graph-loader').hide();
    }
}

function portalChart(chartJsonPortal) {
    if (chartJsonPortal.length > 0) {
        var graphTemplate = $('#graphTemplate').text().trim(),
            graphBarTemplate = $('#graphBarTemplate').text().trim(),
            graphLegendTemplate = $('#graphLegendTemplate').text().trim(),
            bars = legends = "";
        $('#graph-loader').show();
        $('#portal-graph-area').empty();
        $(chartJsonPortal).each(function(index, bar) {
            bars += graphBarTemplate.replace(/\#feedbackClass#/g, bar.class + ((length == index && bar.y <= 5) ? " small" : "")).replace(/\#percentage#/g, bar.y.toFixed(2)).replace(/\#tooltip#/g, `${bar.name}: ${bar.y.toFixed(2)}%`);
            legends += graphLegendTemplate.replace(/\#feedbackClass#/g, bar.class).replace(/\#feedbackName#/g, bar.name);
        });
        graphTemplate = graphTemplate.replace('#bar#', bars).replace('#legend#', legends);
        $('#portal-graph-area').html(graphTemplate);
        $('#graph-loader').hide();
    } else {
        $('#graph-loader').hide();
    }
}

function projectChart(chartJsonProject) {
    if (chartJsonProject.length > 0) {
        var graphTemplate = $('#projectgraphTemplate').text().trim(),
            graphBarTemplate = $('#projectgraphBarTemplate').text().trim(),
            graphLegendTemplate = $('#projectgraphLegendTemplate').text().trim(),
            bars = legends = "";
        $('#project-graph-loader').show();
        $('#project-graph-area').empty();
        $(chartJsonProject).each(function(index, bar) {
            bars += graphBarTemplate.replace(/\#feedbackClass#/g, bar.class + ((length == index && bar.y <= 5) ? " small" : "")).replace(/\#percentage#/g, bar.y.toFixed(2)).replace(/\#tooltip#/g, `${bar.name}: ${bar.y.toFixed(2)}%`);
            legends += graphLegendTemplate.replace(/\#feedbackClass#/g, bar.class).replace(/\#feedbackName#/g, bar.name);
        });
        graphTemplate = graphTemplate.replace('#bar#', bars).replace('#legend#', legends);
        $('#project-graph-area').html(graphTemplate);
        $('#project-graph-loader').hide();
    } else {
        $('#project-graph-loader').hide();
        $('#project-graph-area').html('<p>' + message.select_project_message + '</p>');
    }
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
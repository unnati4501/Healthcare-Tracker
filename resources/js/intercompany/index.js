function loadDataTable(table, data) {
    var fileName = ((table == "ICReportCompany") ? 'company_wise_inter_company_report' : ((table == "ICReportTeam") ? 'team_wise_inter_company_report' : 'report')),
        options = { ...{
                destroy: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: url.getICReportChallengeData,
                    data: {
                        type: $('#myTab .nav-link.active').data('for'),
                        challenge: $('#challenge').val(),
                        company: $('#company').val(),
                        getQueryString: window.location.search
                    },
                },
                columns: [],
                paging: true,
                pageLength: pagination.value,
                lengthChange: true,
                lengthMenu: [
                    [25, 50, 100, -1],
                    [25, 50, 100, 'All']
                ],
                searching: false,
                ordering: true,
                order: [
                    [0, 'asc']
                ],
                info: true,
                autoWidth: false,
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false,
                }],
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
                drawCallback: function( fileName ) {
                    var tab = $('#myTab .nav-link.active').data('for');
                    var api = this.api();
                    if(api.rows().count()==0){
                        if(tab=='company'){
                            $(".ICReportCompany-export").hide();
                        }else{
                           
                           $(".ICReportTeam-export").hide();
                        }
                    }else{
                        if(tab=='company'){
                            $(".ICReportCompany-export").show();
                        }else{
                           $(".ICReportTeam-export").show();
                        }
                    }

                    // Output the data for the visible rows to the browser's console
                    //console.log( api.rows( {page:'current'} ).data() );
                }
            },
            ...data
        };
    $('#' + table).DataTable(options);
}

function loadCompany(challengeId) {
    $.get(url.getICReportChallengeComapnies, {
        challengeId: challengeId
    }, function(data, textStatus, jqXHR) {
        if (jqXHR.status == 200 && data && data.status == 1) {
            var options = '';
            $.each(data.data, function(index, element) {
                options += `<option value="${index}">${element}</option>`;
            });
            $('#company').html(options).val('').prop('disabled', false);
            $('#company').select2('destroy').select2();
        } else {
            toastr.clear()
            toastr.error(message.failed_load_companies)
        }
    });
}
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#company').prop('disabled', true);
    $('.select2').select2();
    $(".ICReportTeam-export").hide();
    $(".ICReportCompany-export").hide();
    $(document).on('shown.bs.tab', '#myTab a[data-bs-toggle="tab"]', function(e) {
        var newTab = $(e.target).data('for'),
            // challengeId = $('#challenge').val(),
            table = ((newTab == "company") ? "ICReportCompany" : "ICReportTeam");
            if(newTab == "company"){
                $(".ICReportCompany-export").hide();
                $(".ICReportTeam-export").hide();
            }else{
                $(".ICReportCompany-export").hide();
                $(".ICReportTeam-export").hide();
            }
        if ($.fn.DataTable.isDataTable('#ICReportCompany')) {
            $('#ICReportCompany').DataTable().clear();
            $('#ICReportCompany').DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable('#ICReportTeam')) {
            $('#ICReportTeam').DataTable().clear();
            $('#ICReportTeam').DataTable().destroy();
        }
        $(`#${table} tbody`).html(`<tr><td class="text-center no-data-table" colspan="5">` + message.please_select_challenge + `</td></tr>`)
        $("#ICReportCompany, #ICReportTeam").parent().toggleClass('hidden');
        $('#challenge').val('').trigger('change');
        $('#company').empty().prop('disabled', true);
        $('#company').select2('destroy').select2();
    });
    $(document).on('change', '#challenge', function(e) {
        var challengeId = $('#challenge').val();
        type = $('#myTab .nav-link.active').data('for');
        if (challengeId != '') {
            if (type == 'team' || type == 'company') {
                loadCompany(challengeId);
            }
        } else {
            $('#company').empty();
            $('#company').select2('destroy').select2();
        }
    });
    $(document).on('submit', '#interCompanyReportSearch', function(e) {
        e.preventDefault();
        if ($('#challenge').val() != "") {
            var type = $('#myTab .nav-link.active').data('for'),
                table = ((type == "company") ? "ICReportCompany" : "ICReportTeam"),
                options = {},
                cols = [];
            if (type == 'company') {
                cols = [{
                    data: 'rank',
                    name: 'rank'
                }, {
                    data: 'company_name',
                    name: 'company_name'
                }, {
                    data: 'total_teams',
                    name: 'total_teams'
                }, {
                    data: 'total_users',
                    name: 'total_users'
                }, {
                    data: 'points',
                    name: 'points'
                }];
            } else if (type == 'team') {
                cols = [{
                    data: 'rank',
                    name: 'rank'
                }, {
                    data: 'team_name',
                    name: 'team_name'
                }, {
                    data: 'company_name',
                    name: 'company_name'
                }, {
                    data: 'points',
                    name: 'points'
                }];
            }
            options.columns = cols;
            loadDataTable(table, options);
        }
    });

    $('.daterangesFromExportModel').datepicker({
        format: "yyyy-mm-dd",
        todayHighlight: false,
        autoclose: true,
        endDate: new Date(),
        clearBtn: true,
    });

    $(document).on('click', '#exportInterCompanyReport', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        var interCompanyReportForm = $( "#interCompanyReportSearch" ).serialize();
        $('#queryString').val(JSON.stringify(queryStringToObject('?'+interCompanyReportForm)));
        var tab = $(this).attr('data-tab');
        $('#email').val(loginemail).removeClass('error');
        $("#tab").val(tab);
        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $("#exportNpsReport").attr('action', url.interCompanyReportExportUrl);
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
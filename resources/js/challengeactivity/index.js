function loadCompany(challengeId) {
    $.get(url.getICReportChallengeComapnies, { challengeId: challengeId }, function (data, textStatus, jqXHR) {
        if(jqXHR.status == 200 && data && data.status == 1) {
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

function getChallengeList() {
    if($('#challengeStatus').val() != '' && $('#challengeType').val() != '') {
        getChallenges($('#challengeStatus').val(), $('#challengeType').val());
    }
}

var teamList = [];
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.select2').select2();
    $(".challengeSummaryReport-export").show();
    $(".challengeDetailsReport-export").hide();
    $(".challengeUserWiseReport-export").hide();

    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results==null) {
           return null;
        }
        return decodeURI(results[1]) || 0;
    }
    
    var currentTab = $.urlParam('tab');
    /*if($.urlParam('challenge') != null){
        getChallengeParticipants($.urlParam('challenge'));
    }*/

    /*if($.urlParam('challengeStatus') != null && $.urlParam('challengeType') != null){
        getChallenges($.urlParam('challengeStatus'), $.urlParam('challengeType'));
    }*/
    
    if(currentTab == 'summary'){
        $("#tab").val('summary');
        var table = "challengeSummaryReport";
        $('.nav-link').removeClass('active');
        $("#tab-summary").addClass('active');
        $(`#${table} tbody`).html(`<tr><td class="text-center no-data-table" colspan="5">`+message.select_challenge_view_report+`</td></tr>`);

        $("#challengeSummaryReport").parent().removeClass("hidden");
        $("#challengeDetailsReport").parent().addClass("hidden");
        $("#challengeUserWiseReport").parent().addClass("hidden");

        $(".challengeSummaryReport-export").show();
        $(".challengeDetailsReport-export").hide();
        $(".challengeUserWiseReport-export").hide();

        $('#userrecordsearch').val("");
        $('#usersearch').addClass("hidden");
    }else if(currentTab == 'detail'){
        $("#tab").val('detail');
        var table = "challengeDetailsReport";
        $('.nav-link').removeClass('active');
        $("#tab-details").addClass('active');
        $(`#${table} tbody`).html(`<tr><td class="text-center no-data-table" colspan="10">`+message.select_challenge_view_report+`</td></tr>`);

        $("#challengeDetailsReport").parent().removeClass("hidden");
        $("#challengeSummaryReport").parent().addClass("hidden");
        $("#challengeUserWiseReport").parent().addClass("hidden");

        $(".challengeSummaryReport-export").hide();
        $(".challengeDetailsReport-export").show();
        $(".challengeUserWiseReport-export").hide();

        $('#usersearch').removeClass("hidden");
    }else if(currentTab == 'userwise'){
        $("#tab").val('userwise');
        var table = "challengeUserWiseReport";
        $('.nav-link').removeClass('active');
        $("#tab-userwise").addClass('active');
        $(`#${table} tbody`).html(`<tr><td class="text-center no-data-table" colspan="11">`+message.select_challenge_view_report+`</td></tr>`);

        $("#challengeUserWiseReport").parent().removeClass("hidden");
        $("#challengeSummaryReport").parent().addClass("hidden");
        $("#challengeDetailsReport").parent().addClass("hidden");

        $(".challengeSummaryReport-export").hide();
        $(".challengeDetailsReport-export").hide();
        $(".challengeUserWiseReport-export").show();

        $('#usersearch').removeClass("hidden");
    }

    $(document).on('change', '#challenge', function(e) {

        if($('#challenge').val() != '') {
            getChallengeParticipants($('#challenge').val());
            /*$.get(url.getChallengeParticipant, { challenge: $('#challenge').val()}, function (data, textStatus, jqXHR) {
                var cmpId = '';
                if(jqXHR.status == 200 && data && data.status == 1) {
                    var options = '';
                    var teamoptions = '';
                    teamList = data.data.teamList;

                    $.each(data.data.companyList, function(index, element) {
                        options += `<option value="${index}">${element}</option>`;
                        cmpId = index;
                    });
                    $('#company').html(options).val('').prop('disabled', false);
                    $('#company').select2('destroy').select2();

                    if($('#challengeType').val() != "inter_company") {
                        $('#company').val(cmpId).trigger('change');
                    }
                } else {
                    toastr.clear()
                    toastr.error(message.failed_load_challenge)
                }
            });*/
        }
    });

    $(document).on('change', '#company', function(e) {
        if($('#company').val() != "" && $('#company').val() != null) {
            var teamoptions = '';
            $.each(teamList[$('#company').val()], function(index, element) {
                teamoptions += `<option value="${index}">${element}</option>`;
            });
            $('#team').html(teamoptions).val('').prop('disabled', false);
            $('#team').select2('destroy').select2();
        }
    });


    $('#challengeSummaryReport').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.getChallengeSummaryData,
            data: {
                tab:$("#tab").val(),
                type: $('#myTab .nav-link.active').data('for'),
                challengeStatus: $('#challengeStatus').val() ?? null,
                challenge: $('#challenge').val() ?? null,
                company: $('#company').val(),
                team: $('#team').val() ?? null,
                userrecordsearch: $('#userrecordsearch').val() ?? null,
                getQueryString: window.location.search
            },
        },
        columns: [
            {
                data: 'userName',
                name: 'userName',
                visible: ($('#challengeType').val() == "individual")
            },{
                data: 'email',
                name: 'email',
                visible: ($('#challengeType').val() == "individual")
            },{
                data: 'company',
                name: 'company',
                visible: ($('#challengeType').val() == "inter_company")
            }, {
                data: 'team',
                name: 'team'
            }, {
                data: 'type',
                name: 'type'
            }, {
                data: 'valueCount',
                name: 'valueCount'
            }, {
                data: 'points',
                name: 'points'
            }
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [[25, 50, 100, 1000, 5000], [25, 50, 100, 1000, 5000]],
        searching: false,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        order: [],
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            lengthMenu: "Entries per page _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        drawCallback: function( fileName ) {
            var api = this.api();
            if (api.rows().count()==0) {
                $(".challengeSummaryReport-export").hide();
            }
        }
    });

    $('#challengeUserWiseReport').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: url.getChallengeDailySummaryData,
            data: {
                tab:$("#tab").val(),
                type: $('#myTab .nav-link.active').data('for'),
                challengeStatus: $('#challengeStatus').val() ?? null,
                challenge: $('#challenge').val() ?? null,
                company: $('#company').val(),
                team: $('#team').val() ?? null,
                userrecordsearch: $('#userrecordsearch').val() ?? null,
                getQueryString: window.location.search
            },
        },
        columns: [
            {
                data: 'userName',
                name: 'userName'
            },{
                data: 'email',
                name: 'email'
            }, {
                data: 'company',
                name: 'company'
            }, {
                data: 'team',
                name: 'team'
            }, {
                data: 'tracker',
                name: 'tracker'
            },{
                data: 'trackerchange',
                name: 'trackerchange',
                sortable: false
            }, {
                data: 'type',
                name: 'type'
            }, {
                data: 'userVal',
                name: 'userVal'
            }, {
                data: 'points',
                name: 'points'
            }, {
                data: 'log_date',
                name: 'log_date'
            }, {
                data: 'actions',
                name: 'actions',
                searchable: false, 
                sortable: false,
                className: 'no-sort'
            }
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [[25, 50, 100, 1000, 5000], [25, 50, 100, 1000, 5000]],
        searching: false,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        order: [],
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            lengthMenu: "Entries per page _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        drawCallback: function( fileName ) {
            var api = this.api();
            if (api.rows().count()==0) {
                $(".challengeDetailsReport-export").hide();
            }
        }
    });

    $('#challengeDetailsReport').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            type: "POST",
            url: url.getChallengeDailySummaryData,
            data: {
                tab:$("#tab").val(),
                type: $('#myTab .nav-link.active').data('for'),
                challengeStatus: $('#challengeStatus').val() ?? null,
                challenge: $('#challenge').val() ?? null,
                company: $('#company').val(),
                team: $('#team').val() ?? null,
                userrecordsearch: $('#userrecordsearch').val() ?? null,
                getQueryString: window.location.search
            },
        },
        columns: [
            {
                data: 'userName',
                name: 'userName'
            },{
                data: 'email',
                name: 'email'
            }, {
                data: 'company',
                name: 'company'
            }, {
                data: 'team',
                name: 'team'
            }, {
                data: 'tracker',
                name: 'tracker'
            }, {
                data: 'type',
                name: 'type'
            }, {
                data: 'userVal',
                name: 'userVal'
            }, {
                data: 'points',
                name: 'points'
            }, {
                data: 'created_at',
                name: 'created_at'
            }, {
                data: 'log_date',
                name: 'log_date'
            }
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [[25, 50, 100, 1000, 5000], [25, 50, 100, 1000, 5000]],
        searching: false,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        order: [],
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            lengthMenu: "Entries per page _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        drawCallback: function( fileName ) {
            var api = this.api();
            if (api.rows().count()==0) {
                $(".challengeUserWiseReport-export").hide();
            }
        }
    });

    if(window.location.hash) {
        var hsahq = window.location.hash;
        if($('.nav-tabs a[href="' + hsahq + '"]').length > 0) {
            $('.nav-tabs a[href="' + hsahq + '"]').click();
        }
    }

    $(document).on('change','.getChallengeList', function(){
        getChallengeList();
    });

    $('.daterangesFromExportModel').datepicker({
        format: "yyyy-mm-dd",
        todayHighlight: false,
        autoclose: true,
        endDate: new Date(),
        clearBtn: true,
    });

    $(document).on('click', '#challengeActivityReport', function(t) {
        var challengeActivityReportSearchForm = $( "#challengeActivityReportSearch" ).serialize();
        $('#queryString').val(JSON.stringify(get_query()));
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        var tab = $(this).attr('data-tab');
        $('#email').val(loginemail).removeClass('error');
        $("#tab").val(tab);
        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $("#exportNpsReport").attr('action', url.challengeActivityReportExportUrl);
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

  function getChallengeParticipants(challengeId){
    $.get(url.getChallengeParticipant, { challenge: challengeId}, function (data, textStatus, jqXHR) {
        var cmpId = '';
        console.log(jqXHR.status);
        if(jqXHR.status == 200 && data && data.status == 1) {
            var options = '';
            var teamoptions = '';
            teamList = data.data.teamList;

            $.each(data.data.companyList, function(index, element) {
                options += `<option value="${index}">${element}</option>`;
                cmpId = index;
            });
            $('#company').html(options).val('').prop('disabled', false);
            $('#company').select2('destroy').select2();

            if($('#challengeType').val() != "inter_company") {
                $('#company').val(cmpId).trigger('change');
            }
        } else {
            toastr.clear()
            toastr.error(message.failed_load_challenge)
        }
    });
  }

  function getChallenges(challengeStatus, challengeType){
    $.get(url.getChallenges, { challengeStatus: challengeStatus , challengeType : challengeType }, function (data, textStatus, jqXHR) {
        if(jqXHR.status == 200 && data && data.status == 1) {
            var options = '';
            $.each(data.data, function(index, element) {
                options += `<option value="${index}">${element}</option>`;
            });
            $('#challenge').html(options).val('').prop('disabled', false);
            $('#challenge').select2('destroy').select2();
            $('#company').empty().prop('disabled', true);
            $('#company').select2('destroy').select2();
            $('#team').empty().prop('disabled', true);
            $('#team').select2('destroy').select2();
        } else {
            toastr.clear()
            toastr.error(message.failed_load_challenge)
        }
    });
  }

  function get_query(){
    $('.daterangesFromExportModel').show();
    var url = document.location.href;
    var qs = url.substring(url.indexOf('?') + 1).split('&');
    for(var i = 0, result = {}; i < qs.length; i++){
        qs[i] = qs[i].split('=');
        if(qs[i][1] != undefined ){
            $('.daterangesFromExportModel').hide();
        }
        result[qs[i][0]] = decodeURIComponent(qs[i][1]);
    }
    return result;
}
function loadDetailedTabData() {
        $('#detailedReportManagement').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                type: 'POST',
                url: url.detailedReport,
                data: {
                    name: $("#dtName").val(),
                    company: $("#dtCompany").val(),
                    presenter: $("#dtPresenter").val(),
                    fromdate: $("#dtFromdate").val(),
                    todate: $("#dtTodate").val(),
                    status: $("#dtStatus").val(),
                    complementary: $('#dtComplementary').val(),
                    category: $('#dtCategory').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'event_name',
                name: 'event_name',
            }, {
                data: 'presenter',
                name: 'presenter',
            }, {
                data: 'subcategory_name',
                name: 'subcategory_name',
            }, {
                data: 'date_time',
                name: 'date_time',
                render: function (data, type, row) {
                    return moment.utc(data).tz(timezone).format("MMM DD, YYYY")  + '<br />' + moment.utc(data).tz(timezone).format("hh:mm A") + " - " + moment.utc(row.end_time).tz(timezone).format("hh:mm A");
                }
            }, {
                data: 'created_by',
                name: 'created_by',
                visible: (data.roleType == 'zsa') ? false : true
            }, {
                data: 'company_name',
                name: 'company_name',
            }, {
                data: 'location_type',
                name: 'location_type',
            }, {
                data: 'fees',
                name: 'fees',
                render: function (data, type, row) {
                    var iData = $.fn.dataTable.render.number(',').display(data);
                    return '€ ' + iData;
                },
                visible: ((data.roleType == 'rca') ? false : true)
            }, {
                data: 'is_complementary',
                name: 'is_complementary',
                visible: ((data.roleType == 'rca') ? false : true)
            }, {
                data: 'status',
                name: 'status',
            }, {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            }, {
                data: 'cancelled_by_name',
                name: 'cancelled_by_name',
                visible: false
            }, {
                data: 'cancelled_on',
                name: 'cancelled_on',
                visible: false
            }, {
                data: 'cancel_reason',
                name: 'cancel_reason',
                visible: false
            }],
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
            },
            // dom: (data.roleType == 'zsa' || data.roleType == 'rsa') ? 'lBfrtip' : 'rtip',
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            buttons: [],
            drawCallback: function(settings) {
                $("#detailed-tab-result-block").show();
                $("#detailed-tab-process-block").hide();
                var api = this.api();
                if(api.rows().count()==0){
                    $("#detailed-tab-result-block .exportBookingDetailReport").hide();
                }else {
                    $("#detailed-tab-result-block .exportBookingDetailReport").show();
                }
            }
        });
    }

    function loadSummaryTabData() {
        var fileName = "event_summary_report";
        $('#summaryReportManagement').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                type: 'POST',
                url: url.summaryReport,
                data: {
                    company: $('#stCompany').val(),
                    status: $('#stStatus').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'company_name',
                name: 'company_name',
            }, {
                data: 'total_events',
                name: 'total_events',
            }, {
                data: 'booked_events',
                name: 'booked_events',
            }, {
                data: 'cancelled_events',
                name: 'cancelled_events',
            }, {
                data: 'billable',
                name: 'billable',
                render: function (data, type, row) {
                    var iData = $.fn.dataTable.render.number(',').display(data);
                    return '€ ' + iData;
                }
            }, {
                data: 'status',
                name: 'status',
            }, {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false
            }],
            paging: true,
            pageLength: parseInt(pagination.value),
            lengthChange: true,
            lengthMenu: [[25, 50, 100], [25, 50, 100]],
            searching: false,
            ordering: true,
            order: [[0, 'asc']],
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
            },
            //dom: (data.roleType == 'zsa' || data.roleType == 'rsa') ? 'lBfrtip' : 'rtip',
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            buttons: [],
            drawCallback: function(settings) {
                $("#summary-tab-result-block").show();
                $("#summary-tab-process-block").hide();
                var api = this.api();
                if(api.rows().count()==0){
                    $("#summary-tab-result-block .exportBookingSummaryReport").hide();
                }else {
                    $("#summary-tab-result-block .exportBookingSummaryReport").show();
                }
            }
        });
    }

    function setSearchBlock(target) {
        if(target == "#summary-view-tab") {
            $("[data-control-for='service-tab']").hide();
            $("[data-control-for='bookings-tab']").show();
        } else {
            $("[data-control-for='service-tab']").show();
            $("[data-control-for='bookings-tab']").hide();
        }
    }

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        if(data.roleType != 'rca' || data.roleType != 'zca') {
            if (window.location.hash) {
                var hash = window.location.hash,
                    hash = (($('.nav-tabs a[href="' + hash + '"]').length > 0) ? hash : '#detailed-tab');
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
                if(hash == "#summary-view-tab") {
                    setSearchBlock(hash);
                    loadSummaryTabData();
                } else {
                    setSearchBlock('#detailed-tab');
                    loadDetailedTabData();
                }
            } else {
                setSearchBlock('#detailed-tab');
                loadDetailedTabData();
            }

            $(document).on('show.bs.tab', '#reportTab.nav-tabs a', function(e) {
                var target = $(e.target).attr("href");
                if (target) {
                    var scroll = $(window).scrollTop(); 
                    window.location.hash = target;
                    $(window).scrollTop(scroll);
                    if(target == "#summary-view-tab") {
                        setSearchBlock(target);
                        loadSummaryTabData();
                    } else {
                        setSearchBlock('#detailed-tab');
                        loadDetailedTabData();
                    }
                }
            });
        } else {
            setSearchBlock('#detailed-tab');
            loadDetailedTabData();
        }

        $(document).on('click', '.resetSearchBtn', function(e) {
            var activeTab = ($("ul#reportTab li a.active").attr('href') || "#detailed-tab");
            $("#isFiltered").val(0);
            $('#queryString').val('');
            if(activeTab == "#summary-view-tab") {
                $('#stCompany, #stStatus').val('').trigger('change');
                $("#summary-tab-result-block").hide();
                $("#summary-tab-process-block").show();
                loadSummaryTabData();
            } else {
                $('#dtName, #dtCompany, #dtPresenter, #dtFromdate, #dtTodate, #dtStatus, #dtComplementary, #dtCategory').val('').trigger('change');
                $("#detailed-tab-result-block").hide();
                $("#detailed-tab-process-block").show();
                loadDetailedTabData();
            }
        });

        $(document).on('submit', '#detailedTabSearch, #summaryTabSearch', function(e) {
            e.preventDefault();
            var activeTab = ($("ul#reportTab li a.active").attr('href') || "#detailed-tab");
            $("#isFiltered").val(1);
            if(activeTab == "#summary-view-tab") {
                $("#summary-tab-result-block").hide();
                $("#summary-tab-process-block").show();
                setSearchBlock(activeTab);
                loadSummaryTabData();
            } else {
                $("#detailed-tab-result-block").hide();
                $("#detailed-tab-process-block").show();
                setSearchBlock('#detailed-tab');
                loadDetailedTabData();
            }
        });

        // show cancelled evetn details on view cancel details button click
        $(document).on('click', '.view-cancel-event-details', function(e) {
            $(".page-loader-wrapper").fadeIn();
            var bid = $(this).data('bid');
            $.ajax({
                type: 'POST',
                url: url.cancelEventDetails.replace(":bid", bid),
                dataType: 'json',
            }).done(function(data) {
                if (data?.status) {
                    $('#cancelled_by').html(data?.cancelled_by);
                    $('#cancelled_at').html(data?.cancelled_at);
                    $('#cancelation_reason').html(data?.cancelation_reason);
                    $('#cancel-event-details-model-box').modal('show');
                } else {
                    toastr.error((data.message || message.failed_to_load));
                }
            }).fail(function(error) {
                toastr.error((error?.responseJSON?.message || message.failed_to_load));
            }).always(function() {
                $(".page-loader-wrapper").fadeOut();
            });
        });
        $('.dateranges').datepicker({
            format: "mm/dd/yyyy",
            todayHighlight: false,
            autoclose: true,
        });

        $('.daterangesFromExportModel').datepicker({
            format: "yyyy-mm-dd",
            todayHighlight: false,
            autoclose: true,
            endDate: new Date(),
            clearBtn: true,
        });

        $(document).on('click', '#exportBookingReport', function(t) {
            var exportConfirmModalBox = '#export-model-box';
            var __startDate = $(this).attr('data-start');
            var __endDate = $(this).attr('data-end');
            var tab = $(this).attr('data-tab');
            if(tab == "booking-details"){
                var tabSearch = $( "#detailedTabSearch" ).serialize();
            }else{
                var tabSearch = $( "#summaryTabSearch" ).serialize();
            }
            
            if($("#isFiltered").val() == '1'){
                $('#queryString').val(JSON.stringify(queryStringToObject('?'+tabSearch)));
            }else{
                $('#queryString').val('');
                $('.daterangesFromExportModel').show();
            }
            $('#email').val(loginemail).removeClass('error');
            $("#tab").val(tab);
            $('.error').remove();
            $("#model-title").html($(this).data('title'));
            $("#exportNpsReport").attr('action', url.bookingReportExportUrl);
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
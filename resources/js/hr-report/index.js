function getScoreColor($score = 0) {
    var _colorCode;
    if ($score <= 0) {
        _colorCode = companyScoreColorCode.red;
    } else if ($score >= 60 && $score < 80) {
        _colorCode = companyScoreColorCode.yellow;
    } else if ($score >= 80 && $score <= 100) {
        _colorCode = companyScoreColorCode.green;
    } else {
        _colorCode = companyScoreColorCode.red;
    }
    return _colorCode;
}
$(window).resize(function() {
    if ($('#subCategoryWiseDepartmentGraph .score-status').length > 0) {
        var iv;
        if (iv !== null) {
            window.clearTimeout(iv);
        }
        iv = setTimeout(function() {
            equalizeHeights($('#subCategoryWiseDepartmentGraph .score-status'));
        }, 120);
    }
});

function equalizeHeights(selector) {
    var heights = new Array();
    $(selector).each(function() {
        $(this).css('min-height', '0');
        $(this).css('max-height', 'none');
        $(this).css('height', 'auto');
        heights.push($(this).height());
    });
    var max = Math.max.apply(Math, heights);
    $(selector).each(function() {
        $(this).css('height', max + 'px');
    });
}

function loadDetails(company, department, category) {
    var url = urls.detailsUrl.replace(':companyId', company).replace(':departmentId', department).replace(':categoryId', category),
        now = moment(),
        fromDate = moment($('#detailsFromMonth').datepicker("getDate")),
        endDate = moment($('#detailsToMonth').datepicker("getDate")).endOf('month'),
        data = {};
    if (endDate > now) {
        endDate = now;
    }
    data.from = fromDate.format('YYYY-MM-DD 00:00:00');
    data.to = endDate.format('YYYY-MM-DD 23:59:59');
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        data: data,
    }).done(function(data) {
        if (data.status != undefined && data.status == 0) {
            toastr.error((data.data || 'Failed to load graph.'));
            $('#report-details-block').hide();
            return;
        }
        $('#details_no_data').hide();
        $('#chart-area').show();
        if (data && data.performance) {
            var _lineChartColorCode = getScoreColor($("#hrReportManagement .report-details.selected").data('score'));
            if (typeof charts.departmentScoreLineGraph.object != "object") {
                charts.departmentScoreLineGraph.object = new Chart(charts.departmentScoreLineGraph.selector, charts.departmentScoreLineGraph.options);
            }
            charts.departmentScoreLineGraph.options.data.labels = data.performance.labels;
            charts.departmentScoreLineGraph.options.data.datasets[0].data = data.performance.data;
            charts.departmentScoreLineGraph.options.data.datasets[0].borderColor = _lineChartColorCode;
            charts.departmentScoreLineGraph.options.data.datasets[0].pointBackgroundColor = _lineChartColorCode;
            charts.departmentScoreLineGraph.object.update();
        }
        if (data && data.subcategories && data.subcategories.length > 0) {
            $('#no_data_subcategory').hide();
            var subcategoriesHTML = "",
                subcategoriesTemplate = $('#subCategoryTabTemplate').text().trim();
            charts.subCategoryWiseDepartmentGaugeCharts = [];
            $(data.subcategories).each(function(index, subcategory) {
                subcategoriesHTML += subcategoriesTemplate.replace('#id#', subcategory.sub_category_id).replace('#sub_category_name#', subcategory.subcategory_name).replace('#sub_category_percentage#', subcategory.percentage).replace(/#background-color#/g, getScoreColor(subcategory.percentage));
            });
            $('#subCategoryWiseDepartmentGraph').html(subcategoriesHTML).show();
            equalizeHeights($('#subCategoryWiseDepartmentGraph .score-status'));
            $(data.subcategories).each(function(index, subcategory) {
                var _selector = $(`[data-subcategorywisedepartmentscoregaugechart-${subcategory.sub_category_id}]`)[0],
                    _color = getScoreColor(subcategory.percentage);
                charts.subCategoryWiseDepartmentGaugeCharts[index] = new Gauge(_selector).setOptions(charts.gaugeChartOptions);
                charts.subCategoryWiseDepartmentGaugeCharts[index].setMinValue(0);
                charts.subCategoryWiseDepartmentGaugeCharts[index].maxValue = 100;
                charts.subCategoryWiseDepartmentGaugeCharts[index].options.colorStart = _color;
                charts.subCategoryWiseDepartmentGaugeCharts[index].set(subcategory.percentage);
            });
        } else {
            $('#subCategoryWiseDepartmentGraph').empty().hide();
            $('#no_data_subcategory').show();
        }
    }).fail(function(error) {
        $('#details_no_data').show();
        $('#subCategoryWiseDepartmentGraph').html('').hide();
        $('#no_data_subcategory').hide();
        toastr.error('Failed to load graph.');
    }).always(function() {
        $('#details_loader').hide();
    });
}
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#monthranges').datepicker({
        format: "M, yyyy",
        startView: 1,
        minViewMode: 1,
        maxViewMode: 2,
        clearBtn: true,
        autoclose: true,
        endDate: ((moment().isSame(moment().endOf('month'), 'date')) ? moment().endOf('month').add(1, 'd').toDate() : moment().endOf('month').toDate())
    });
    $('#detailsmonthranges').datepicker({
        format: "M, yyyy",
        startView: 1,
        minViewMode: 1,
        maxViewMode: 2,
        clearBtn: false,
        autoclose: true,
        endDate: ((moment().isSame(moment().endOf('month'), 'date')) ? moment().endOf('month').add(1, 'd').toDate() : moment().endOf('month').toDate())
    });
    $('#globalFromMonth').datepicker("setDate", ((requestParams.from != undefined) ? moment(requestParams.from).toDate() : ""));
    $('#globalToMonth').datepicker("setDate", ((requestParams.to != undefined) ? moment(requestParams.to).toDate() : ""));
    if (requestParams.from && requestParams.to) {
        dtPayload.from = requestParams.from;
        dtPayload.to = requestParams.to;
    }
    categories = $.parseJSON(categories);
    $(categories).each(function(index, category) {
        columns.push({
            data: category.name,
            name: category.name,
            className: 'text-center hr-scor-link score-cell',
            render: function(data, type, row, a) {
                var _selector = a.settings.aoColumns[a.col],
                    _categoryName = _selector.mData,
                    _categoryId = (_selector.catId || 0);
                return `<a href='javascript:void(0);' class="d-block text-white report-details" data-score="${data}" data-company="${row.company_id}" data-category="${_categoryId}" data-department="${row.department_id}" data-company-name="${row.company_name}" data-category-name="${_categoryName}" data-department-name="${row.department_name}"><span class='score-list'>${data}</span></a>`;
            }
        });
    });
    columns.push({
        data: 'score',
        name: 'score',
        className: 'text-center',
        render: function(data, type, row) {
            return `<div class="round-score" style="background-color: ${getScoreColor(data)};">${data}%</div>`;
        }
    });
    $('#hrReportManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: "POST",
            url: urls.datatable,
            data: dtPayload,
        },
        columns: columns,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        order: [],
        autoWidth: false,
        columnDefs: [{
            targets: 'score-cell',
            createdCell: function(td, cellData, rowData, row, col) {
                $(td).css('background-color', getScoreColor(($('a.report-details', td).data('score') || cellData)));
            }
        }, {
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        drawCallback: function(row, data, start, end, display) {
            var api = this.api(),
                data;
            $(api.columns().footer()).removeClass('hr-scor-link score-cell');
            if (api.rows().count() == 0) {
                $('#hrReportManagement tfoot').hide();
            } else {
                $('#hrReportManagement tfoot').show();
            }
        },
        fnDrawCallback: function(oSettings) {
            if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                $(oSettings.nTableWrapper).find('.dataTables_paginate, .dataTables_info').hide();
            } else {
                $(oSettings.nTableWrapper).find('.dataTables_paginate, .dataTables_info').show();
            }
        },
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
    });
    $('#detailsmonthranges').on('changeDate', function(e) {
        let datepickerData = $(this).data('datepicker'),
            isManualUpdating = ($(this).data('isManualUpdating') || false);
        if (!datepickerData.updating && isManualUpdating == false) {
            let now = moment(),
                fromDate = moment(datepickerData.pickers[0].getDate()),
                toDate = moment(datepickerData.pickers[1].getDate()).endOf('month'),
                options = new Object();
            if (toDate > now) {
                toDate = now;
            }
            var _selector = $("#hrReportManagement .report-details.selected");
            if (_selector.length > 0) {
                loadDetails($(_selector).data('company'), $(_selector).data('department'), $(_selector).data('category'));
            }
        }
    });
    $(document).on('click', '.report-details', function(e) {
        var _company = ($(this).data('company') || 0),
            _department = ($(this).data('department') || 0),
            _category = ($(this).data('category') || 0),
            _now = moment(),
            _startDate = moment($('#globalFromMonth').datepicker("getDate")),
            _endDate = moment($('#globalToMonth').datepicker("getDate")).endOf('month');
        if (_category > 0 && _department > 0) {
            if ($('#globalFromMonth').val() != "" && $('#globalToMonth').val() != "") {
                if (_endDate > _now) {
                    _endDate = _now;
                }
            } else {
                _startDate = moment().subtract(5, 'months');
                _endDate = moment();
            }
            $('#detailsmonthranges').data('isManualUpdating', true);
            $('#detailsFromMonth').datepicker("setDate", _startDate.toDate());
            $('#detailsToMonth').datepicker("setDate", _endDate.toDate());
            $('#detailsmonthranges').data('isManualUpdating', false);
            $('#card-title').html(`${$(this).data('category-name')} - ${$(this).data('department-name')}(${$(this).data('company-name')})`);
            $("#hrReportManagement .report-details").removeClass('selected');
            $(this).addClass('selected');
            $('#report-details-block, #details_loader').show();
            $('#details_no_data, #chart-area').hide();
            $('html, body').animate({
                scrollTop: $("#report-details-block").offset().top - 65
            }, 1000);
            loadDetails(_company, _department, _category);
        }
    });
    $(document).on('click', '.back-to-report', function(e) {
        $("#hrReportManagement .report-details").removeClass('selected');
        $('html, body').animate({
            scrollTop: 0
        }, 1000);
    });
    $(document).on('click', '#hrReportSearchSubmitFrm', function(e) {
        e.preventDefault();
        var action = $('#hrReportSearch').attr('action'),
            now = moment(),
            fromDate = moment($('#globalFromMonth').datepicker("getDate")),
            endDate = moment($('#globalToMonth').datepicker("getDate")).endOf('month'),
            company = ($('#company').val() || 0),
            data = {};
        if ($('#globalFromMonth').val() != "" && $('#globalToMonth').val() != "") {
            if (endDate > now) {
                endDate = now;
            }
            data.from = fromDate.format('YYYY-MM-DD 00:00:00');
            data.to = endDate.format('YYYY-MM-DD 23:59:59');
        } else {
            $('#globalFromMonth, #globalToMonth').val('');
        }
        if (company > 0) {
            data.company = company;
        }
        var qString = $.param(data);
        window.location.href = action + ((qString != '') ? '?' + qString : '');
    });
});
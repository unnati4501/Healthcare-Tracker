/*
 * Chart declarations
 */
var subCategoryTabsCarousel = $('#audit_category_wise_company_score_tab').owlCarousel({
        navText: ["<i class='fal fa-chevron-circle-left'></i>", "<i class='fal fa-chevron-circle-right'></i>"],
        loop: false,
        margin: 0,
        nav: true,
        dots: false,
        // autoWidth: true,
        pullDrag: false,
        mouseDrag: false,
        responsive: {
            0: {
                items: 2
            },
            500: {
                items: 3
            },
            767: {
                items: 3
            },
            1000: {
                items: 5
            },
            1700: {
                items: 8
            }
        }
    }),
    auditCharts = {
        gaugeChartOptions: {
            animationSpeed: 32,
            angle: 0.0,
            lineWidth: 0.25,
            pointer: {
                length: 0.6,
                strokeWidth: 0.025,
                color: '#000000'
            },
            strokeColor: '#f2f4f4',
            highDpiSupport: true
        },
        companyScoreGaugeChart: {
            selector: $('[data-companyscoregaugechart]')[0],
            object: undefined
        },
        companyScoreLineChart: {
            selector: $('[data-companyscorelinechart]')[0],
            object: undefined,
            options: {
                type: "line",
                plugins: [{
                    afterLayout: chart => {
                        var ctx = chart.chart.ctx;
                        var xAxis = chart.scales['x-axis-0'];
                        var gradientStroke = ctx.createLinearGradient(xAxis.left, 0, xAxis.right, 0);
                        var dataset = chart.data.datasets[0];
                        if (dataset.colors.length > 0) {
                            dataset.colors.forEach((c, i) => {
                                var stop = ((dataset.colors.length > 1) ? (1 / (dataset.colors.length - 1) * i) : 0);
                                gradientStroke.addColorStop(stop, dataset.colors[i]);
                            });
                            // dataset.backgroundColor = gradientStroke;
                            dataset.borderColor = gradientStroke;
                            dataset.pointBorderColor = gradientStroke;
                            dataset.pointBackgroundColor = gradientStroke;
                            dataset.pointHoverBorderColor = gradientStroke;
                            dataset.pointHoverBackgroundColor = gradientStroke;
                        }
                    }
                }],
                gridLines: {
                    display: true,
                    drawBorder: true,
                    drawOnChartArea: false,
                },
                data: {
                    labels: [],
                    datasets: [{
                        label: "",
                        data: [],
                        colors: [],
                        fill: true,
                        lineTension: 0.5,
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (`${tooltipItem.yLabel}%`);
                            },
                            labelColor: function(tooltipItem, data) {
                                var dataset = data.data.datasets[tooltipItem.datasetIndex],
                                    color = dataset.colors[tooltipItem.index];
                                return {
                                    borderColor: color,
                                    backgroundColor: color
                                };
                            },
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: 100.00,
                            },
                            scaleLabel: {
                                display: true,
                                fontSize: 14,
                                labelString: "Percentage",
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    baseLine: []
                }
            }
        },
        categoryWiseCompanyScorePieChart: {
            selector: $('[data-categorywisecompanydoughnutchart]')[0],
            object: undefined,
            data: {
                labels: {
                    render: 'percentage',
                    precision: 1,
                    fontColor: '#000',
                    fontSize: 16,
                    position: 'outside',
                    arc: true,
                },
                empty: {
                    labels: [],
                    datasets: [{
                        data: [100],
                        backgroundColor: [companyScoreColorCode.grey],
                        hoverBackgroundColor: [companyScoreColorCode.grey]
                    }]
                },
                filled: {
                    labels: ["Low", "Moderate", "High"],
                    datasets: [{
                        data: [],
                        backgroundColor: [companyScoreColorCode.red, companyScoreColorCode.yellow, companyScoreColorCode.green],
                        hoverBackgroundColor: [companyScoreColorCode.red, companyScoreColorCode.yellow, companyScoreColorCode.green]
                    }]
                }
            },
            options: {
                type: 'doughnut',
                data: {},
                options: {
                    responsive: true,
                    cutoutPercentage: 75,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        enabled: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[tooltipItem.datasetIndex]
                                return (`${data.labels[tooltipItem.index]}: ${dataset.data[tooltipItem.index]}%`);
                            }
                        }
                    },
                    plugins: {
                        labels: []
                    }
                }
            }
        },
        companyCategoryScoreGaugeChart: {
            selector: $('[data-companycategoryscoregaugechart]')[0],
            object: undefined
        },
        categoryWiseCompanyScoreLineChart: {
            selector: $('[data-categorywisecompanylinechart]')[0],
            object: undefined,
            options: {
                type: "line",
                plugins: [{
                    afterLayout: chart => {
                        var ctx = chart.chart.ctx;
                        var xAxis = chart.scales['x-axis-0'];
                        var gradientStroke = ctx.createLinearGradient(xAxis.left, 0, xAxis.right, 0);
                        var dataset = chart.data.datasets[0];
                        if (dataset.colors.length > 0) {
                            dataset.colors.forEach((c, i) => {
                                var stop = ((dataset.colors.length > 1) ? (1 / (dataset.colors.length - 1) * i) : 0);
                                gradientStroke.addColorStop(stop, dataset.colors[i]);
                            });
                        }
                        // dataset.backgroundColor = gradientStroke;
                        dataset.borderColor = gradientStroke;
                        dataset.pointBorderColor = gradientStroke;
                        dataset.pointBackgroundColor = gradientStroke;
                        dataset.pointHoverBorderColor = gradientStroke;
                        dataset.pointHoverBackgroundColor = gradientStroke;
                    }
                }],
                gridLines: {
                    display: true,
                    drawBorder: true,
                    drawOnChartArea: false,
                },
                data: {
                    labels: [],
                    datasets: [{
                        label: "",
                        data: [],
                        colors: [],
                        fill: true,
                        lineTension: 0.5,
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (`${tooltipItem.yLabel}%`);
                            },
                            labelColor: function(tooltipItem, data) {
                                var dataset = data.data.datasets[tooltipItem.datasetIndex],
                                    color = dataset.colors[tooltipItem.index];
                                return {
                                    borderColor: color,
                                    backgroundColor: color
                                };
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: 100.00,
                            },
                            scaleLabel: {
                                display: true,
                                fontSize: 14,
                                labelString: "Percentage",
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    baseLine: [],
                }
            }
        },
        subCategoryWiseCompanyScoreGaugeCharts: [],
        subCategoryWiseCompanyScoreLineChart: {
            selector: $('[data-subcategorywiselinechart]')[0],
            object: undefined,
            options: {
                type: "line",
                plugins: [{
                    afterLayout: chart => {
                        var ctx = chart.chart.ctx;
                        var xAxis = chart.scales['x-axis-0'];
                        var gradientStroke = ctx.createLinearGradient(xAxis.left, 0, xAxis.right, 0);
                        var dataset = chart.data.datasets[0];
                        if (dataset.colors.length > 0) {
                            dataset.colors.forEach((c, i) => {
                                var stop = ((dataset.colors.length > 1) ? (1 / (dataset.colors.length - 1) * i) : 0);
                                gradientStroke.addColorStop(stop, dataset.colors[i]);
                            });
                            // dataset.backgroundColor = gradientStroke;
                            dataset.borderColor = gradientStroke;
                            dataset.pointBorderColor = gradientStroke;
                            dataset.pointBackgroundColor = gradientStroke;
                            dataset.pointHoverBorderColor = gradientStroke;
                            dataset.pointHoverBackgroundColor = gradientStroke;
                        }
                    }
                }],
                gridLines: {
                    display: true,
                    drawBorder: true,
                    drawOnChartArea: false,
                },
                data: {
                    labels: [],
                    datasets: [{
                        label: "",
                        data: [],
                        colors: [],
                        fill: true,
                        lineTension: 0.5,
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (`${tooltipItem.yLabel}%`);
                            },
                            labelColor: function(tooltipItem, data) {
                                var dataset = data.data.datasets[tooltipItem.datasetIndex],
                                    color = dataset.colors[tooltipItem.index];
                                return {
                                    borderColor: color,
                                    backgroundColor: color
                                };
                            },
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: 100.00,
                            },
                            scaleLabel: {
                                display: true,
                                fontSize: 14,
                                labelString: "Percentage",
                            }
                        }]
                    },
                    legend: {
                        display: false
                    },
                    baseLine: []
                }
            }
        },
    };
/*
 * Initilize empty graph of audit tab
 */
function initEmptyGraph() {
    if ($('ul.dashboard-tabs li#audit').length > 0) {
        // company score gauge chart
        auditCharts.companyScoreGaugeChart.object = new Gauge(auditCharts.companyScoreGaugeChart.selector).setOptions(auditCharts.gaugeChartOptions);
        auditCharts.companyScoreGaugeChart.object.setMinValue(0);
        auditCharts.companyScoreGaugeChart.object.maxValue = 100;
        auditCharts.companyScoreGaugeChart.object.options.colorStart = '#f44436';
        auditCharts.companyScoreGaugeChart.object.set(0);
        // company score line chart
        auditCharts.companyScoreLineChart.object = new Chart(auditCharts.companyScoreLineChart.selector, auditCharts.companyScoreLineChart.options);
        // company category score gauge chart
        auditCharts.companyCategoryScoreGaugeChart.object = new Gauge(auditCharts.companyCategoryScoreGaugeChart.selector).setOptions(auditCharts.gaugeChartOptions);
        auditCharts.companyCategoryScoreGaugeChart.object.setMinValue(0);
        auditCharts.companyCategoryScoreGaugeChart.object.maxValue = 100;
        auditCharts.companyCategoryScoreGaugeChart.object.options.colorStart = '#f44436';
        auditCharts.companyCategoryScoreGaugeChart.object.set(0);
    }
}
/*
 * function to convert hex to rgba
 */
function hexToRGB(hex, alpha) {
    var r = parseInt(hex.slice(1, 3), 16),
        g = parseInt(hex.slice(3, 5), 16),
        b = parseInt(hex.slice(5, 7), 16);
    if (alpha) {
        return "rgba(" + r + ", " + g + ", " + b + ", " + alpha + ")";
    } else {
        return "rgb(" + r + ", " + g + ", " + b + ")";
    }
}
/*
 * Initialize easy responsive tabs
 */
function InitializeCategoryCompanyScoreTabs() {
    $('#audit_category_wise_company_score_tab').easyResponsiveTabs({
        type: 'default',
        width: 'auto',
        fit: true,
        tabidentify: 'tab_identifier_child'
    });
}
/*
 * Audit tab common AJAX call
 */
function auditTabAjaxCall(tier, options = null) {
    var age = $('.age').val();
    age = ((age) ? age.split('_') : age);
    if (tier == 2) {
        $('#audit_category_wise_company_score_no_data, #audit_category_wise_company_score_tab_wrapper').hide();
        $('#audit_category_wise_company_score_loader').show();
    }
    if ($('#roleType').val() == 1) {
        if ($('#industry_id').val() != '' && $('#company_id').val() != '') {
            var companyIds = $('#company_id').val();
        } else if ($('#company_id').val() != '') {
            var companyIds = $('#company_id').val();
        } else {
            var companyIds = $('#companiesId').val();
        }
    } else {
        var companyIds = ($('#company_id').val() != '') ? $('#company_id').val() : null;
    }
    $.ajax({
        url: urls.audit,
        type: 'POST',
        dataType: 'json',
        data: {
            tier: tier,
            companyId: companyIds,
            departmentId: $('#department_id').val(),
            age1: ((age) ? age[0] : null),
            age2: ((age) ? age[1] : null),
            options: options
        }
    }).done(function(data) {
        loadAuditTabData(data, tier);
    }).fail(function(error) {
        if (tier == 2) {
            $('#audit_category_wise_company_score_loader, #audit_category_wise_company_score_tab_wrapper').hide();
            $('#audit_category_wise_company_score_no_data').show();
        }
        toastr.error('Failed to load audit tab data.');
    })
}
/*
 * Load Audit Tab Data Tier by Tier
 */
function loadAuditTabData(data, tier) {
    switch (tier) {
        case 1:
            // Update company score gauge chart data
            auditCharts.companyScoreGaugeChart.object.options.colorStart = data.companyScoreGaugeChart.colorCode;
            auditCharts.companyScoreGaugeChart.object.set(data.companyScoreGaugeChart.score);
            $('[data-companyscoregaugechart-value]').css('color', data.companyScoreGaugeChart.colorCode).html(`${data.companyScoreGaugeChart.score}%`);
            // Update company score line chart data
            auditCharts.companyScoreLineChart.options.data.labels = data.companyScoreLineChart.labels;
            auditCharts.companyScoreLineChart.options.data.datasets[0].lineTension = 0.5;
            auditCharts.companyScoreLineChart.options.data.datasets[0].data = data.companyScoreLineChart.data;
            auditCharts.companyScoreLineChart.options.data.datasets[0].colors = data.companyScoreLineChart.colors;
            auditCharts.companyScoreLineChart.options.options.baseLine = [];
            if (data.companyScoreLineChart.data.length > 0) {
                auditCharts.companyScoreLineChart.options.options.baseLine = [{
                    y: data.companyScoreLineChart.baseline,
                    text: `Baseline (${data.companyScoreLineChart.baseline}%)`
                }];
            }
            auditCharts.companyScoreLineChart.object.update();
            break;
        case 2:
            if (data.tabs && data.tabs.length > 0) {
                $('#audit_category_wise_company_score_loader, #audit_subcategory_wise_company_score_no_data').hide();
                $('#audit_category_wise_company_score_tab_wrapper').show();
                var tabsHtml = '',
                    tabsTemplate = $('#auditTabCategoryTabTemplate').text().trim(),
                    now = moment(),
                    fromDate = moment($('#categoryWiseCompanyScoreFromMonth').datepicker("getDate")),
                    endDate = moment($('#categoryWiseCompanyScoreToMonth').datepicker("getDate")).endOf('month'),
                    _options = {};
                if (endDate > now) {
                    endDate = now;
                }
                _options.fromDateCategoryCompanyScore = fromDate.format('YYYY-MM-DD 00:00:00');
                _options.endDateCategoryCompanyScore = endDate.format('YYYY-MM-DD 23:59:59');
                if (data.tabs.length > 0) {
                    $.each(data.tabs, function(index, category) {
                        if (index == 0) {
                            _options.category_id = category.category_id;
                        }
                        tabsHtml += tabsTemplate.replace('#category_name#', category.display_name).replace('#active_class#', ((index == 0) ? 'active' : '')).replace('#aria-selected#', ((index == 0) ? 'true' : 'false')).replace('#percentage#', category.category_percentage).replace('#background-color#', getScoreColor(category.category_percentage)).replace(/#id#/g, category.category_id);
                    });
                    // $('#audit_category_wise_company_score_tab').empty().html(tabsHtml);
                    subCategoryTabsCarousel.trigger('replace.owl.carousel', tabsHtml).trigger('refresh.owl.carousel');
                    $('#audit_category_wise_company_score_tab .owl-item .item.active').parent().addClass('selected');
                    $('#audit_category_wise_company_score_tab .owl-item .item.active').removeClass('active');
                    auditTabAjaxCall(3, _options);
                }
            } else {
                $('#audit_category_wise_company_score_loader, #audit_category_wise_company_score_tab_wrapper').hide();
                $('#audit_category_wise_company_score_no_data').show();
            }
            break;
        case 3:
            var _lineChartColorCode = getScoreColor(data.score.category_percentage),
                _pieChartData = [],
                now = moment(),
                fromDate = moment($('#categoryWiseCompanyScoreFromMonth').datepicker("getDate")),
                endDate = moment($('#categoryWiseCompanyScoreToMonth').datepicker("getDate")).endOf('month'),
                _options = {
                    category_id: (data.score.category_id || $("#audit_category_wise_company_score_tab .owl-item.selected .item").data('id')),
                };
            if (endDate > now) {
                endDate = now;
            }
            _options.fromDateCategoryCompanyScore = fromDate.format('YYYY-MM-DD 00:00:00');
            _options.endDateCategoryCompanyScore = endDate.format('YYYY-MM-DD 23:59:59');
            // load category wise company score gauge chart
            $('#category_score_percentage').html(`Detailed Category Score`);
            $('.go-to-question-report').attr('href', (data.questionReportURL || '#'));
            if (typeof auditCharts.categoryWiseCompanyScorePieChart.object != "object") {
                auditCharts.categoryWiseCompanyScorePieChart.object = new Chart(auditCharts.categoryWiseCompanyScorePieChart.selector, auditCharts.categoryWiseCompanyScorePieChart.options);
            }
            // Update company category score gauge chart data
            auditCharts.companyCategoryScoreGaugeChart.object.options.colorStart = _lineChartColorCode;
            auditCharts.companyCategoryScoreGaugeChart.object.set((data.score.category_percentage || 0));
            $('[data-companycategoryscoregaugechart-value]').css('color', _lineChartColorCode).html(`${(data.score.category_percentage || 0)}%`);
            if (data.score.low != undefined && data.score.moderate != undefined && data.score.high != undefined) {
                let chData = auditCharts.categoryWiseCompanyScorePieChart.data.filled;
                _pieChartData = [
                    data.score.low,
                    data.score.moderate,
                    data.score.high,
                ];
                chData.datasets[0].data = _pieChartData;
                auditCharts.categoryWiseCompanyScorePieChart.options.data = chData;
                auditCharts.categoryWiseCompanyScorePieChart.options.options.plugins.labels[0] = auditCharts.categoryWiseCompanyScorePieChart.data.labels;
                // auditCharts.categoryWiseCompanyScorePieChart.options.options.tooltips.enabled = true;
            } else {
                // auditCharts.categoryWiseCompanyScorePieChart.options.options.tooltips.enabled = false;
                auditCharts.categoryWiseCompanyScorePieChart.options.options.plugins.labels = [];
                auditCharts.categoryWiseCompanyScorePieChart.options.data = auditCharts.categoryWiseCompanyScorePieChart.data.empty;
            }
            auditCharts.categoryWiseCompanyScorePieChart.object.update();
            // load category wise company score line chart
            if (typeof auditCharts.categoryWiseCompanyScoreLineChart.object != "object") {
                auditCharts.categoryWiseCompanyScoreLineChart.object = new Chart(auditCharts.categoryWiseCompanyScoreLineChart.selector, auditCharts.categoryWiseCompanyScoreLineChart.options);
            }
            auditCharts.categoryWiseCompanyScoreLineChart.options.data.labels = data.performance.labels;
            auditCharts.categoryWiseCompanyScoreLineChart.options.data.datasets[0].lineTension = 0.5;
            auditCharts.categoryWiseCompanyScoreLineChart.options.data.datasets[0].data = data.performance.data;
            auditCharts.categoryWiseCompanyScoreLineChart.options.data.datasets[0].colors = data.performance.colors;
            auditCharts.categoryWiseCompanyScoreLineChart.options.options.baseLine = [];
            if (data.performance.data.length > 0) {
                auditCharts.categoryWiseCompanyScoreLineChart.options.options.baseLine = [{
                    y: data.performance.baseline,
                    text: `Baseline (${data.performance.baseline}%)`
                }];
            }
            auditCharts.categoryWiseCompanyScoreLineChart.object.update();
            $('#audit_subcategory_wise_company_score_loader').show();
            $('#audit_subcategory_wise_company_score_no_data').hide();
            // prepare subcategories
            $('#audit-subcategory').html(data.subcategories).select2('destroy').select2();
            _options.sub_category_id = $('#audit-subcategory').val();
            auditTabAjaxCall(4, _options);
            break;
        case 4:
            if (data.subcategories.length > 0) {
                // var subcategoriesHtml = '',
                //     subcategoriesTemplate = $('#auditTabSubCategoryTabTemplate').text().trim();
                // auditCharts.subCategoryWiseCompanyScoreGaugeCharts = [];
                // $('#subCategoryWiseCompanyScoreGraph').html('');
                // $(data.subcategories).each(function(index, subcategory) {
                //     subcategoriesHtml += subcategoriesTemplate.replace('#id#', subcategory.sub_category_id).replace('#sub_category_name#', subcategory.subcategory_name).replace('#sub_category_percentage#', subcategory.percentage).replace(/#background-color#/g, getScoreColor(subcategory.percentage));
                // });
                // $('#subCategoryWiseCompanyScoreGraph').html(subcategoriesHtml).show();
                // $(data.subcategories).each(function(index, subcategory) {
                //     var _selector = $(`[data-subcategorywisecompanyscoregaugechart-${subcategory.sub_category_id}]`)[0],
                //         _color = getScoreColor(subcategory.percentage);
                //     auditCharts.subCategoryWiseCompanyScoreGaugeCharts[index] = new Gauge(_selector).setOptions(auditCharts.gaugeChartOptions);
                //     auditCharts.subCategoryWiseCompanyScoreGaugeCharts[index].setMinValue(0);
                //     auditCharts.subCategoryWiseCompanyScoreGaugeCharts[index].maxValue = 100;
                //     auditCharts.subCategoryWiseCompanyScoreGaugeCharts[index].options.colorStart = _color;
                //     auditCharts.subCategoryWiseCompanyScoreGaugeCharts[index].set(subcategory.percentage);
                // });
                // if ($('#subCategoryWiseCompanyScoreGraph .score-status').length > 0) {
                //     equalizeHeights($('#subCategoryWiseCompanyScoreGraph .score-status'));
                // }
                // load subcategory wise line chart
                $('#audit_subcategory_wise_company_score_loader, #audit_subcategory_wise_company_score_no_data').hide();
                $('#subCategoryWiseCompanyScoreGraph').show();
                if (typeof auditCharts.subCategoryWiseCompanyScoreLineChart.object != "object") {
                    auditCharts.subCategoryWiseCompanyScoreLineChart.object = new Chart(auditCharts.subCategoryWiseCompanyScoreLineChart.selector, auditCharts.subCategoryWiseCompanyScoreLineChart.options);
                }
                auditCharts.subCategoryWiseCompanyScoreLineChart.options.data.labels = data.performance.labels;
                auditCharts.subCategoryWiseCompanyScoreLineChart.options.data.datasets[0].lineTension = 0.5;
                auditCharts.subCategoryWiseCompanyScoreLineChart.options.data.datasets[0].data = data.performance.data;
                auditCharts.subCategoryWiseCompanyScoreLineChart.options.data.datasets[0].colors = data.performance.colors;
                auditCharts.subCategoryWiseCompanyScoreLineChart.options.options.baseLine = [];
                if (data.performance.data.length > 0) {
                    auditCharts.subCategoryWiseCompanyScoreLineChart.options.options.baseLine = [{
                        y: data.performance.baseline,
                        text: `Baseline (${data.performance.baseline}%)`
                    }];
                }
                auditCharts.subCategoryWiseCompanyScoreLineChart.object.update();
            } else {
                $('#subCategoryWiseCompanyScoreGraph, #audit_subcategory_wise_company_score_loader').hide();
                $('#audit_subcategory_wise_company_score_no_data').show();
            }
            break;
        default:
            toastr.error('Something went wrong!');
            break;
    }
}
/*
 * Load empty charts of audit tab
 */
initEmptyGraph();
/*
 * Load audit tab's category wise graphs on change of tab
 */
$(document).on('click', "#audit_category_wise_company_score_tab .owl-item .item", function() {
    $("#audit_category_wise_company_score_tab .owl-item").removeClass('selected');
    $('#subCategoryWiseCompanyScoreGraph').hide();
    $(this).parent().addClass("selected");
    var _id = $(this).data('id'),
        now = moment(),
        fromDate = moment($('#categoryWiseCompanyScoreFromMonth').datepicker("getDate")),
        endDate = moment($('#categoryWiseCompanyScoreToMonth').datepicker("getDate")).endOf('month'),
        _options = {
            category_id: _id,
        };
    if (endDate > now) {
        endDate = now;
    }
    _options.fromDateCategoryCompanyScore = fromDate.format('YYYY-MM-DD 00:00:00');
    _options.endDateCategoryCompanyScore = endDate.format('YYYY-MM-DD 23:59:59');
    auditTabAjaxCall(3, _options);
});
$(document).on('change', '#audit-subcategory', function(e) {
    var _id = $(this).val(),
        now = moment(),
        fromDate = moment($('#categoryWiseCompanyScoreFromMonth').datepicker("getDate")),
        endDate = moment($('#categoryWiseCompanyScoreToMonth').datepicker("getDate")).endOf('month'),
        _options = {
            category_id: $("#audit_category_wise_company_score_tab .owl-item.selected .item").data('id'),
            sub_category_id: _id
        };
    if (endDate > now) {
        endDate = now;
    }
    _options.fromDateCategoryCompanyScore = fromDate.format('YYYY-MM-DD 00:00:00');
    _options.endDateCategoryCompanyScore = endDate.format('YYYY-MM-DD 23:59:59');
    console.log(_options);
    auditTabAjaxCall(4, _options);
});
var owlCarouselOptions = {
        navText: ["<i class='far fa-long-arrow-left'></i>", "<i class='far fa-long-arrow-right'></i>"],
        loop: false,
        margin: 0,
        nav: true,
        dots: false,
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
    },
    questionReportCategoryTabs = $('#question-report-category-tabs'),
    questionReportSubcategoryTabs = $('#question-report-subcategory-tabs'),
    charts = {
        categoryScoreGaugeChart: {
            selector: $('[data-categorygaugechart]')[0],
            object: undefined,
            options: {
                type: "doughnut",
                data: {
                    datasets: [{
                        data: [],
                        backgroundColor: [],
                        hoverBackgroundColor: [],
                        label: [],
                    }],
                    labels: []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutoutPercentage: 90,
                    elements: {
                        arc: {
                            borderWidth: 0,
                        }
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: "rgba(0, 0, 0,0.7)",
                        borderWidth: "0",
                        borderColor: "rgba(0, 0, 0,0.7)",
                        yPadding: 9,
                        bodyFontSize: 14,
                        filter: function(item, data) {
                            var label = data.labels[item.index];
                            if (label) return item;
                        }
                    }
                }
            }
        },
    },
    dtObj = undefined,
    dtSelector = $('#subcategory_questions_tbl'),
    dtOptions = {
        processing: true,
        serverSide: true,
        ajax: {
            type: 'post',
            url: urls.reportDataUrl,
        },
        columns: [{
            name: 'id',
            data: 'DT_RowIndex',
            className: 'text-center',
            orderable: false,
            searchable: false,
        }, {
            data: 'question_type',
            name: 'question_type',
        }, {
            data: 'question',
            name: 'question',
        }, {
            data: 'responses',
            name: 'responses',
            class: 'text-center',
        }, {
            data: 'percentage',
            name: 'percentage',
            class: 'text-center'
        }],
        pageLength: parseInt(pagination.value),
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        lengthChange: false,
        searching: false,
        order: [
            [2, 'asc']
        ],
        autoWidth: false,
        destroy: true,
        drawCallback: function(settings) {
            $('#subcategory_question_block').show();
            $('#subcategory_progress').hide();
        }
    };
/**
 * This function will return the color code for the specific score.
 *
 */
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
/*
 * Initialize monthrange pickers
 */
function monthRangeInit() {
    $('.monthranges').datepicker({
        format: "M, yyyy",
        startView: 1,
        minViewMode: 1,
        maxViewMode: 2,
        clearBtn: false,
        autoclose: true,
        endDate: ((moment().isSame(moment().endOf('month'), 'date')) ? moment().endOf('month').add(1, 'd').toDate() : moment().endOf('month').toDate())
    });
    $('#globalFromMonth').datepicker("setDate", ((requestParams.from != undefined) ? moment(requestParams.from).toDate() : moment().subtract(5, 'months').toDate()));
    $('#globalToMonth').datepicker("setDate", ((requestParams.to != undefined) ? moment(requestParams.to).toDate() : moment().toDate()));
    $('.monthranges').on('changeDate', function(e) {
        let datepickerData = $(this).data('datepicker');
        if (!datepickerData.updating) {
            questionReportAjaxCall(1);
        }
    });
}
/*
 * Initilize empty graph of audit tab
 */
function initEmptyGraph() {
    // category score gauge chart
    charts.categoryScoreGaugeChart.object = new Chart(charts.categoryScoreGaugeChart.selector, charts.categoryScoreGaugeChart.options);
}
/*
 * Common AJAX call
 */
function questionReportAjaxCall(tier, options = null) {
    var now = moment(),
        categoryScoreOptions = {},
        categoryfromDate = moment($('#globalFromMonth').datepicker("getDate")),
        categoryendDate = moment($('#globalToMonth').datepicker("getDate")).endOf('month'),
        categoryId = ($('#question-report-category-tabs .owl-item.selected .item').data('id') || 0);
    if (categoryendDate > now) {
        categoryendDate = now;
    }
    if (tier == 1) {
        // $('#report_data_process').show();
        $('#report_wrapper, #report_no_data').hide()
    } else if (tier == 2) {
        $('#subcategory_progressbars_loader').show();
        $('[data-subcategory-progressbars], #subcategory_progressbars_no_data').hide();
    }
    var companyIds = ($('#roleType').val() == 1 && $('#company_id').val() == '') ? $('#companiesId').val() : $('#company_id').val();
    $.ajax({
        url: urls.reportDataUrl,
        type: 'POST',
        dataType: 'json',
        data: {
            tier: tier,
            categoryId: categoryId,
            companyId: companyIds,
            locationId: $('#location_id').val(),
            departmentId: $('#department_id').val(),
            fromDate: categoryfromDate.format('YYYY-MM-DD 00:00:00'),
            endDate: categoryendDate.format('YYYY-MM-DD 23:59:59'),
            options: options
        }
    }).done(function(data) {
        loadQuestionReportData(data, tier);
    }).fail(function(error) {
        toastr.error('Failed to load question report data.');
    })
}
/*
 * question report common ajax callback
 */
function loadQuestionReportData(data, tier) {
    switch (tier) {
        case 1:
            var categoryTabsTemplate = $('#category_tabs_template').html().trim(),
                categoryTabsHtml = '',
                tier2Options = {};
            if (data.tabs.length > 0) {
                $.each(data.tabs, function(index, category) {
                    categoryTabsHtml += categoryTabsTemplate.replace('#name#', category.display_name).replace('#active_class#', ((index == 0) ? 'active' : '')).replace('#id#', category.category_id).replace('#image#', category.image);
                });
                // , #report_data_process
                $('#report_no_data').hide()
                $('.tabs-wraper').show();
                $('#report_wrapper').show();
                // load category tabs
                $('#question-report-category-tabs').show();
                questionReportCategoryTabs.trigger('replace.owl.carousel', categoryTabsHtml).trigger('refresh.owl.carousel');
                $('#question-report-category-tabs .owl-item .item.active').addClass('selected').parent().addClass('selected');
                $('#question-report-category-tabs .owl-item .item.active').removeClass('active');
                // load first selected subcategory data
                questionReportAjaxCall(2);
            } else {
                categoryTabsHtml = categoryTabsTemplate.replace('#name#', "").replace('#active_class#', "").replace('#id#', 0).replace('#image#', '');
                questionReportCategoryTabs.trigger('replace.owl.carousel', categoryTabsHtml).trigger('refresh.owl.carousel');
                charts.categoryScoreGaugeChart.options.data.labels = [];
                charts.categoryScoreGaugeChart.options.data.datasets[0].data = [100];
                charts.categoryScoreGaugeChart.options.data.datasets[0].backgroundColor = [companyScoreColorCode.red];
                charts.categoryScoreGaugeChart.options.data.datasets[0].hoverBackgroundColor = [companyScoreColorCode.red];
                charts.categoryScoreGaugeChart.options.data.datasets[0].label = [];
                charts.categoryScoreGaugeChart.object.update();
                $('[data-categorygaugechart-value]').css('color', companyScoreColorCode.red).html(`${0}%`);
                // #report_data_process,
                $('#subcategory_wrapper').hide()
                $('[data-subcategory-progressbars]').html('');
                $("#subcategory_progressbars_no_data").show();
                $('#question-report-category-tabs').hide();
                $('.tabs-wraper').hide();
                $('#report_wrapper').show();
            }
            break;
        case 2:
            $('#subcategory_progressbars_loader').hide();
            var subcategoryProgressbarTemplate = $('#subcategory_progressbars_template').html().trim(),
                subcategoryTabsTemplate = $('#subcategory_tabs_template').html().trim(),
                subcategoryProgressbarHtml = '',
                subcategoryTabsHtml = '',
                tier2Options = {};
            // Update company score gauge chart data
            if (data.score.category_percentage != undefined && data.score.category_percentage > 0) {
                charts.categoryScoreGaugeChart.options.data.labels = data.categoryScoreGaugeChart.labels;
                charts.categoryScoreGaugeChart.options.data.datasets[0].data = data.categoryScoreGaugeChart.data;
                charts.categoryScoreGaugeChart.options.data.datasets[0].backgroundColor = data.categoryScoreGaugeChart.colors;
                charts.categoryScoreGaugeChart.options.data.datasets[0].hoverBackgroundColor = data.categoryScoreGaugeChart.colors;
                charts.categoryScoreGaugeChart.options.data.datasets[0].label = data.categoryScoreGaugeChart.labels;
                $('[data-categorygaugechart-value]').css('color', data.score.color_code).html(`${data.score.category_percentage}%`);
            } else {
                charts.categoryScoreGaugeChart.options.data.labels = [];
                charts.categoryScoreGaugeChart.options.data.datasets[0].data = [100];
                charts.categoryScoreGaugeChart.options.data.datasets[0].backgroundColor = [companyScoreColorCode.red];
                charts.categoryScoreGaugeChart.options.data.datasets[0].hoverBackgroundColor = [companyScoreColorCode.red];
                charts.categoryScoreGaugeChart.options.data.datasets[0].label = [];
                $('[data-categorygaugechart-value]').css('color', companyScoreColorCode.red).html(`${0}%`);
            }
            charts.categoryScoreGaugeChart.object.update();
            if (data.subcategories != undefined && data.subcategories.length > 0) {
                $(data.subcategories).each(function(index, subcategory) {
                    if (index == 0) {
                        tier2Options.subcategory_id = subcategory.sub_category_id;
                    }
                    // preparing subcategory progressbars html
                    subcategoryProgressbarHtml += subcategoryProgressbarTemplate.replace('#name#', subcategory.subcategory_name).replace(/#percetage#/g, subcategory.percentage).replace(/#color_code#/g, getScoreColor(subcategory.percentage));
                    // preparing subcategory tabs html
                    subcategoryTabsHtml += subcategoryTabsTemplate.replace('#name#', subcategory.subcategory_name).replace('#active_class#', ((index == 0) ? 'active' : '')).replace('#id#', subcategory.sub_category_id);
                });
                // load subcategory progressbars
                $('[data-subcategory-progressbars]').html(subcategoryProgressbarHtml).show();
                // load subcategory tabs
                questionReportSubcategoryTabs.trigger('replace.owl.carousel', subcategoryTabsHtml).trigger('refresh.owl.carousel');
                $('#question-report-subcategory-tabs .owl-item .item.active').addClass('selected');
                $('#question-report-subcategory-tabs .owl-item .item.active').parent().addClass('selected');
                $('#question-report-subcategory-tabs .owl-item .item.active').removeClass('active');
                $('#subcategory_wrapper').show();
                loadDT(tier2Options);
            } else {
                $("#subcategory_progressbars_no_data").show();
                $('#subcategory_wrapper').hide();
            }
            break;
        default:
            toastr.error('Something went wrong!');
            break;
    }
}

function loadDT(options) {
    var cDtOptions = dtOptions,
        now = moment(),
        categoryScoreOptions = {},
        categoryfromDate = moment($('#globalFromMonth').datepicker("getDate")),
        categoryendDate = moment($('#globalToMonth').datepicker("getDate")).endOf('month'),
        categoryId = ($('#question-report-category-tabs .owl-item.selected .item').data('id') || 0);
    if (categoryendDate > now) {
        categoryendDate = now;
    }
    cDtOptions.ajax.data = {
        tier: 3,
        categoryId: categoryId,
        companyId: $('#company_id').val(),
        locationId: $('#location_id').val(),
        departmentId: $('#department_id').val(),
        fromDate: categoryfromDate.format('YYYY-MM-DD 00:00:00'),
        endDate: categoryendDate.format('YYYY-MM-DD 23:59:59'),
        options: options
    };
    dtSelector.DataTable(cDtOptions);
}
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    /*--------------- month Range ---------------*/
    monthRangeInit();
    /*--------------- init Empty Graph ---------------*/
    initEmptyGraph();
    /*--------------- question report category tabs ---------------*/
    questionReportCategoryTabs.on('initialized.owl.carousel', function(event) {
        var selector = $(`#question-report-category-tabs .item[data-id="${(requestParams.category || 0)}"]`).parent();
        $(selector).addClass('selected');
        $(selector).find('.item').addClass('selected');
        setTimeout(function() {
            $('#question-report-category-tabs').trigger('to.owl.carousel', $(selector).index());
        }, 1);
    });
    questionReportCategoryTabs.owlCarousel(owlCarouselOptions);
    questionReportSubcategoryTabs.owlCarousel(owlCarouselOptions);
    questionReportAjaxCall(2);
    /*
     * Load category wise graph, subcategories and their questions by changing comapany
     */
    $(document).on('change', '#company_id', function() {
        if (typeof options == 'undefined') {
            options = new Object();
        }
        options.change = $(this).attr("id");
        var select = $(this).attr("id");
        var value = $(this).val();
        var deptDependent = $(this).attr('target-data');
        var locDependent = $(this).attr('target-location-data');
        var departmentLength = 0;
        if ($('#company_id').val() != '' && $('#company_id').val() != null) {
            var _token = $('input[name="_token"]').val();
            url = urls.getDept.replace(':id', value);
            $.ajax({
                url: url,
                method: 'get',
                data: {
                    _token: _token
                },
                success: function(result) {
                    $('#' + deptDependent).empty();
                    $('#' + deptDependent).select2('destroy').select2();
                    $('#' + deptDependent).attr('disabled', false);
                    $('#' + locDependent).empty();
                    $('#' + locDependent).select2('destroy').select2();
                    $('#' + locDependent).attr('disabled', false);
                    $('#' + deptDependent).val('').append('<option value="">Select</option>');
                    $.each(result.result, function(key, value) {
                        $('#' + deptDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    departmentLength = Object.keys(result.result).length;
                    if (Object.keys(result.result).length == 1) {
                        $.each(result.result, function(key, value) {
                            $('#' + deptDependent).select2('val', value.id);
                        });
                    }
                    questionReportAjaxCall(1, options);
                }
            })
        } else {
            $('#' + deptDependent).empty();
            $('#' + deptDependent).select2('destroy').select2();
            $('#' + deptDependent).attr('disabled', true);
            $('#' + locDependent).empty();
            $('#' + locDependent).select2('destroy').select2();
            $('#' + locDependent).attr('disabled', true);
            questionReportAjaxCall(1, options);
        }

        // progress visibility of question block
        $('#subcategory_question_block').hide();
        $('#subcategory_progress').show();
    });
    $('#department_id').change(function() {
        if (typeof options == 'undefined') {
            options = new Object();
        }
        options.change = $(this).attr("id");
        var select = $(this).attr("id");
        var value = $(this).val();
        var locDependent = $('#company_id').attr('target-location-data');
        var _token = $('input[name="_token"]').val();
        url = urls.getLoc.replace(':id', value);
        var locationlength = 0;
        if ($('#department_id').val() != '' && $('#department_id').val() != null) {
            $.ajax({
                url: url,
                method: 'get',
                data: {
                    _token: _token
                },
                success: function(result) {
                    $('#' + locDependent).empty();
                    $('#' + locDependent).select2('destroy').select2();
                    $('#' + locDependent).attr('disabled', false);
                    $('#' + locDependent).val('').append('<option value="">Select</option>');
                    $.each(result.result, function(key, value) {
                        $('#' + locDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    locationlength = Object.keys(result.result).length;
                    if (Object.keys(result.result).length == 1) {
                        $.each(result.result, function(key, value) {
                            $('#' + locDependent).select2('val', value.id);
                        });
                    }
                    questionReportAjaxCall(1, options);
                }
            })
        } else {
            $('#location_id').empty();
            $('#location_id').select2('destroy').select2();
            $('#location_id').attr('disabled', true);
            questionReportAjaxCall(1, options);
        }
    });
    $('#location_id').change(function() {
        if (typeof options == 'undefined') {
            options = new Object();
        }
        options.change = $(this).attr("id");
        questionReportAjaxCall(1, options)
    });
    /*
     * Load category wise graph, subcategories and their questions by taping on category tab
     */
    $(document).on('click', "#question-report-category-tabs .owl-item .item", function() {
        $("#question-report-category-tabs .owl-item, #question-report-category-tabs .owl-item .item").removeClass('selected');
        $(this).addClass("selected").parent().addClass("selected");
        questionReportAjaxCall(2);
        // progress visibility of question block
        $('#subcategory_question_block').hide();
        $('#subcategory_progress').show();
    });
    /*
     * Load subcategory wise questions by taping on sucategory tab
     */
    $(document).on('click', "#question-report-subcategory-tabs .owl-item .item", function() {
        $("#question-report-subcategory-tabs .owl-item, #question-report-subcategory-tabs .owl-item .item").removeClass('selected');
        $(this).addClass("selected").parent().addClass("selected");
        var _options = {
            subcategory_id: $(this).data('id'),
        };
        $('#subcategory_question_block').hide();
        $('#subcategory_progress').show();
        loadDT(_options);
    });
});
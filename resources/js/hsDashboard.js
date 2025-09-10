var baselinePlugin = {
        afterDraw: function(chartInstance) {
            var yScale = chartInstance.scales["y-axis-0"];
            var canvas = chartInstance.chart;
            var ctx = canvas.ctx;
            var index;
            var line;
            var style;
            if (chartInstance.options.baseLine) {
                for (index = 0; index < chartInstance.options.baseLine.length; index++) {
                    // debugger;
                    line = chartInstance.options.baseLine[index];
                    if (!line.style) {
                        style = "rgba(112, 112, 112, 1)";
                    } else {
                        style = line.style;
                    }
                    if (line.y != undefined) {
                        yValue = yScale.getPixelForValue(line.y);
                    } else {
                        yValue = 0;
                    }
                    ctx.lineWidth = 2;
                    if (yValue) {
                        ctx.beginPath();
                        ctx.moveTo(60, yValue);
                        ctx.lineTo((canvas.width - 28), yValue);
                        ctx.strokeStyle = style;
                        ctx.stroke();
                    }
                    if (line.text) {
                        ctx.fillStyle = style;
                        ctx.fillText(line.text, canvas.width - 125, (yValue + ctx.lineWidth + 15));
                    }
                }
                return;
            };
        }
    },
    charts = {
        healthScoreSurveyChart: {
            object: '',
            config: {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    maintainAspectRatio: true,
                    responsive: true,
                    stacked: false,
                    legend: {
                        display: false,
                        position: 'bottom',
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (`${tooltipItem.yLabel}%`);
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: 100.00
                            },
                            scaleLabel: {
                                display: true,
                                fontSize: 14,
                                labelString: "Percentage",
                            }
                        }]
                    },
                    baseLine: [],
                }
            }
        },
        physicalScoreChart: {
            object: '',
            config: {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    maintainAspectRatio: true,
                    responsive: true,
                    hoverMode: 'index',
                    stacked: false,
                    legend: {
                        display: false,
                        position: 'bottom',
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (`${tooltipItem.yLabel}%`);
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: 100.00
                            },
                            scaleLabel: {
                                display: true,
                                fontSize: 14,
                                labelString: "Percentage",
                            }
                        }]
                    },
                    baseLine: [],
                }
            }
        },
        psychologicalScoreChart: {
            object: '',
            config: {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    maintainAspectRatio: true,
                    responsive: true,
                    stacked: false,
                    legend: {
                        display: false,
                        position: 'bottom',
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (`${tooltipItem.yLabel}%`);
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: 100.00
                            },
                            scaleLabel: {
                                display: true,
                                fontSize: 14,
                                labelString: "Percentage",
                            }
                        }]
                    },
                    baseLine: [],
                }
            }
        },
    };
Chart.pluginService.register(baselinePlugin);
Chart.defaults.global.pointHitDetectionRadius = 1;

function knobInit() {
    $('.knob-chart').knob({
        min: 0,
        max: 100,
        format: function(value) {
            return value + '%';
        }
    });
}

function num_format_short(n) {
    if (n < 1e3) return n;
    if (n >= 1e3 && n < 1e6) return +(n / 1e3).toFixed(1) + "K";
    if (n >= 1e6 && n < 1e9) return +(n / 1e6).toFixed(1) + "M";
    if (n >= 1e9 && n < 1e12) return +(n / 1e9).toFixed(1) + "B";
    if (n >= 1e12) return +(n / 1e12).toFixed(1) + "T";
}

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

function loadChart(chartType, extraData) {
    var pAge = $('#age').val(),
        data = {};
    pAge = ((pAge) ? pAge.split('_') : pAge);
    data = {
        healthScoreCharts: true,
        chartType: chartType,
        comapnyId: $('#company_id').val(),
        departmentId: $('#department_id').val(),
        age1: ((pAge) ? pAge[0] : null),
        age2: ((pAge) ? pAge[1] : null)
    };
    if (extraData != undefined) {
        data = $.extend(data, extraData);
    }
    if (chartType == 'healthScoreSurvey') {
        $('[data-physical-survey-knob], [data-psychological-survey-knob]').css('display', 'none');
    }
    $.ajax({
        url: urls.chartData,
        type: 'POST',
        dataType: 'json',
        data: data
    }).done(function(data) {
        switch (chartType) {
            case 'healthScoreSurvey':
                // load data of healthScoreSurvey chart
                charts.healthScoreSurveyChart.config.data.labels = data.labels;
                charts.healthScoreSurveyChart.config.data.datasets = [];
                charts.healthScoreSurveyChart.config.options.baseLine = [];
                var color = ($('#hsCategoryList li.active').data('hex') || dynamicColors());
                charts.healthScoreSurveyChart.config.data.datasets.push({
                    data: data.data,
                    fill: true,
                    borderWidth: 1,
                    borderColor: color,
                    backgroundColor: hexToRGB(color, 0.3),
                    hoverBackgroundColor: color,
                    lineTension: 0.5
                });
                if (data.data.length > 0) {
                    charts.healthScoreSurveyChart.config.options.baseLine = [{
                        y: data.baseline,
                        text: `Baseline (${data.baseline}%)`
                    }];
                }
                charts.healthScoreSurveyChart.object.update();
                // survey user counts
                $('[data-totaluser]').html(num_format_short(data.counts.appuser));
                $('[data-completerallsurveys]').html(num_format_short(data.counts.allsurvey));
                $('[data-notattempt]').html(num_format_short(data.counts.notattempt));
                $('[data-physical-survey-knob]').hide();
                $('[data-psychological-survey-knob]').hide();
                $('[data-physical-survey-knob], [data-psychological-survey-knob]').css('display', 'block');
                $('#physical_survey_knob').attr('data-original-title', `No. of Users(Completed surveys): ${data.counts.physicalsurvey}<br/>No. of Users(Not attempted): ${(data.counts.allcounts - data.counts.physicalsurvey)}`).val(((data.counts.physicalsurvey * 100) / data.counts.allcounts)).trigger('change');
                $('#psychological_survey_knob').attr('data-original-title', `No. of Users(Completed surveys): ${data.counts.physcologicalsurvey}<br/>No. of Users(Not attempted): ${(data.counts.allcounts - data.counts.physcologicalsurvey)}`).val(((data.counts.physcologicalsurvey * 100) / data.counts.allcounts)).trigger('change');
                // $('[data-completedphysicalsurvey]').html(num_format_short(data.counts.physicalsurvey));
                // $('[data-completedpsychologicalsurvey]').html(num_format_short(data.counts.physcologicalsurvey));
                break;
            case 'healthScorePhysicalCatWise':
                // load data of physical chart
                charts.physicalScoreChart.config.data.labels = data.labels;
                charts.physicalScoreChart.config.data.datasets = [];
                charts.physicalScoreChart.config.options.baseLine = [];
                var color = ($('#physicalCategoryList li.active').data('hex') || dynamicColors());
                charts.physicalScoreChart.config.data.datasets.push({
                    data: data.data,
                    fill: true,
                    borderWidth: 1,
                    borderColor: color,
                    backgroundColor: hexToRGB(color, 0.3),
                    hoverBackgroundColor: color,
                    lineTension: 0.5
                });
                if (data.data.length > 0) {
                    charts.physicalScoreChart.config.options.baseLine = [{
                        y: data.baseline,
                        text: `Baseline (${data.baseline}%)`
                    }];
                }
                charts.physicalScoreChart.object.update();
                break;
            case 'healthScorePsychologicalCatWise':
                // load data of psychological chart
                charts.psychologicalScoreChart.config.data.labels = data.labels;
                charts.psychologicalScoreChart.config.data.datasets = [];
                charts.psychologicalScoreChart.config.options.baseLine = [];
                var color = ($('#psychologicalCategoryList li.active').data('hex') || dynamicColors());
                charts.psychologicalScoreChart.config.data.datasets.push({
                    data: data.data,
                    fill: true,
                    borderWidth: 1,
                    borderColor: color,
                    backgroundColor: hexToRGB(color, 0.3),
                    hoverBackgroundColor: color,
                    lineTension: 0.5
                });
                if (data.data.length > 0) {
                    charts.psychologicalScoreChart.config.options.baseLine = [{
                        y: data.baseline,
                        text: `Baseline (${data.baseline}%)`
                    }];
                }
                charts.psychologicalScoreChart.object.update();
                break;
        }
    }).fail(function(error) {
        alert('Failed to load chart.');
        console.log('Failed to load chart Check below for more about error');
        console.log(error);
    });
}

function loadDepartments() {
    var company_id = $('#company_id').val(),
        url = urls.getDept.replace(':id', company_id),
        options = '';
    $.get(url, {
        _token: _token
    }, function(data) {
        if (data && data.code == 200) {
            $.each(data.result, function(index, dept) {
                options += `<option value='${dept.id}'>${dept.name}</option>`;
            });
            $('#department_id').empty().append(options).val('');
        } else {
            $('#department_id').empty();
        }
    }, 'json');
}

function loadAllCharts() {
    loadChart('healthScoreSurvey', {
        category_id: ($('#hsCategoryList li.active').data('id') || 0)
    });
    loadChart('healthScorePhysicalCatWise', {
        sub_category_id: ($('#physicalCategoryList li.active').data('id') || 0)
    });
    loadChart('healthScorePsychologicalCatWise', {
        sub_category_id: ($('#psychologicalCategoryList li.active').data('id') || 0)
    });
}
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: 60000
    });
    knobInit();
    charts.healthScoreSurveyChart.object = new Chart($('#healthScoreSurveyChart'), charts.healthScoreSurveyChart.config);
    charts.physicalScoreChart.object = new Chart($('#physicalScoreChart'), charts.physicalScoreChart.config);
    charts.psychologicalScoreChart.object = new Chart($('#psychologicalScoreChart'), charts.psychologicalScoreChart.config);
    loadAllCharts();
    $(document).on('change.select2', '#company_id', function(e) {
        // $('#department_id').empty();
        // $('#age').val('');
        // $('#department_id, #age').select2('destroy').select2();
        // loadDepartments();
        loadAllCharts();
    });
    $(document).on('change.select2', '#department_id, #age', function(e) {
        loadAllCharts();
    });
    $(document).on('click', '.side-tabbing-list li', function(e) {
        var id = ($(this).data('id') || 0),
            type = ($(this).data('type') || ''),
            extraOptions = {};
        if (id > 0 && type != '') {
            $(this).parent().find('li').removeClass('active');
            $(this).addClass('active');
            if (type == "healthScoreSurvey") {
                extraOptions.category_id = id;
            } else {
                extraOptions.sub_category_id = id;
            }
            loadChart(type, extraOptions);
        }
    });
});
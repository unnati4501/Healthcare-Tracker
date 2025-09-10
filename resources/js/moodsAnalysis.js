/*
 * Code for displaying departments on company change
 */
$('#company').change(function() {
    var select = $(this).attr("id");
    var value = $(this).val();
    var deptDependent = $(this).attr('target-data');
    if ($('#company').val() != '' && $('#company').val() != null) {
        var _token = $('input[name="_token"]').val();
        deptUrl = url.deptData.replace(':id', value);
        $.ajax({
            url: deptUrl,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(result) {
                $('#' + deptDependent).empty();
                $('#' + deptDependent).select2('destroy').select2({
                    allowClear: true
                });
                $('#' + deptDependent).val('').append('<option value="">Select</option>');
                $('#' + deptDependent).attr('disabled', false);
                $.each(result.result, function(key, value) {
                    $('#' + deptDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                });
                if (Object.keys(result.result).length == 1) {
                    $.each(result.result, function(key, value) {
                        $('#' + deptDependent).select2('val', value.id);
                    });
                }
            }
        })
    } else {
        $('#' + deptDependent).empty();
        $('#' + deptDependent).select2('destroy').select2({
            allowClear: true
        });
        $('#' + deptDependent).attr('disabled', true);
    }
});
/*
 *Code for week/month/year filter
 */
$(document).on('click', '#duration li', function(e) {
    var _this = $(this);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    params = [];
    params['duration'] = _this.data('duration');
    usersData(params);
    moodsData(params);
    tagsData(params);
});
/*
 *Comon AJAX Call
 */
function ajaxCall(params) {
    var successCallBackFunction = params['successCallBackFunction'];
    var errorCallBackFunction = params['errorCallBackFunction'];
    var duration = $('#duration .active');
    params['duration'] = duration.data('duration');
    $.ajax({
        url: params['url'],
        crossDomain: true,
        type: 'GET',
        dataType: 'json',
        data: {
            company: $('#company').val(),
            department: $('#department').val(),
            duration: params['duration'] || 7,
            mood: params['mood'] || null,
        },
        async: true,
        cache: false,
        success: function(data) {
            if (typeof successCallBackFunction === "function") {
                successCallBackFunction(data);
            }
        },
        error: function(error) {
            if (typeof errorCallBackFunction === "function") {
                errorCallBackFunction(error);
            }
        }
    });
}
/*
 *Ajax call for usersData
 */
function usersData(params = []) {
    params['url'] = url.usersData;
    params['successCallBackFunction'] = function successCallBack(data) {
        loadUsersChart(data);
        $('#totalUsers').html(data['totalUsers']);
        $('#activeUsers').html(data['activeUsers']);
        $('#passiveUsers').html(data['passiveUsers']);
    }
    params['errorCallBackFunction'] = function errorCallBack(error) {
        toastr.error("Failed to retrieve users data");
        $('#totalUsers').html('-');
        $('#activeUsers').html('-');
        $('#passiveUsers').html('-');
    }
    ajaxCall(params);
}
/*
 *Ajax call for moodsData
 */
function moodsData(params = []) {
    params['url'] = url.moodsData;
    params['successCallBackFunction'] = function successCallBack(data) {
        loadMoodsChart(data);
    }
    params['errorCallBackFunction'] = function errorCallBack(error) {
        toastr.error("Failed to retrieve moods data");
    }
    ajaxCall(params);
}
/*
 *Ajax call for tagsData
 */
function tagsData(params = []) {
    params['url'] = url.tagsData;
    params['successCallBackFunction'] = function successCallBack(data) {
        loadTagsChart(data);
    }
    params['errorCallBackFunction'] = function errorCallBack(error) {
        toastr.error("Failed to retrieve tags data");
    }
    ajaxCall(params);
}
/*
 *Ajax calls on document ready
 */
$(document).ready(function() {
    usersData();
    moodsData();
    tagsData();
});
/*
 *Ajax calls on company/department change
 */
$('#company,#department').change(function() {
    usersData();
    moodsData();
    tagsData();
});

function loadUsersChart(data) {
    $('#numberOfUsers').remove();
    $('#appendNumberOfUsersChart').append('<canvas class="canvas" id="numberOfUsers"></canvas>');
    var numberOfUsers = document.getElementById("numberOfUsers");
    var numberOfUsersChart = new Chart(numberOfUsers, {
        type: "doughnut",
        data: {
            datasets: [{
                data: [data.activeUsers, data.passiveUsers],
                backgroundColor: ["#FFD600", "#50C9B5"],
                label: ["Active", "Passive"],
            }],
            labels: ["Active", "Passive"]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 60,
            rotation: 180,
            elements: {
                arc: {
                    borderWidth: 0
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
                bodyFontSize: 14
            }
        }
    });
}
/*
 *Load moods chart data
 */
function loadMoodsChart(data) {
    $('#chartMoodsAnalysis').remove();
    $('#appendMoodsChart').append('<canvas height="250" id="chartMoodsAnalysis" width="300"></canvas>');
    backgroundColor = [];
    for (var i = 0; i < data['data'].length; i++) {
        backgroundColor[i] = "rgb(82, 97, 172, 0.3)";
    }
    var ctx = document.getElementById('chartMoodsAnalysis');
    var myBarChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: data['labels'],
            datasets: [{
                label: "Percentage",
                data: data['data'],
                fill: false,
                backgroundColor: backgroundColor,
                hoverBackgroundColor: "rgb(82, 97, 172)",
                borderColor: "rgb(82, 97, 172)",
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        beginAtZero: true,
                        autoSkip: false
                    },
                    barThickness: 20,
                    maxBarThickness: 30
                }],
                yAxes: [{
                    gridLines: {
                        display: true
                    },
                    ticks: {
                        beginAtZero: true,
                        max: 100
                    },
                }]
            },
        }
    }, 1000);
    ctx.onclick = function(evt) {
        var element = myBarChart.getElementAtEvent(evt);
        for (var i = 0; i < backgroundColor.length; i++) {
            backgroundColor[i] = "rgb(82, 97, 172, 0.3)";
        }
        if (element.length == 0) {
            myBarChart.update();
            tagsData();
            return;
        };
        backgroundColor[element[0]._index] = "rgb(82, 97, 172)";
        myBarChart.update();
        params = [];
        params['mood'] = element[0]._model.label;
        tagsData(params);
    };
}
/*
 *Load tags chart data
 */
function loadTagsChart(data) {
    $('#chartTagAnalysis').remove();
    $('#appendTagsChart').append('<canvas height="200" id="chartTagAnalysis" width="600"></canvas>');
    var ctx = document.getElementById('chartTagAnalysis');
    var myBarChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: data['labels'],
            datasets: [{
                label: "Percentage",
                data: data['data'],
                fill: false,
                backgroundColor: "rgb(82, 97, 172, 0.3)",
                borderColor: "rgb(82, 97, 172)",
                borderWidth: 1
            }, {
                label: "Percentage",
                data: data['stackedData'],
                fill: false,
                backgroundColor: "rgb(82, 97, 172)",
                borderColor: "rgb(82, 97, 172)",
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false
                    },
                    ticks: {
                        beginAtZero: true,
                        autoSkip: false
                    },
                    barThickness: 30,
                    maxBarThickness: 50,
                    stacked: true
                }],
                yAxes: [{
                    gridLines: {
                        display: true
                    },
                    ticks: {
                        beginAtZero: true,
                        max: 100
                    },
                }]
            }
        }
    }, 1000);
}
var eapActivity = {
    appointmentTrendChart: {
        object: '',
        config: {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: "#5261AC"
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        categoryPercentage: 0.2,
                        gridLines: {
                            display: false
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Days",
                            fontFamily: "Montserrat, sans-serif",
                            fontStyle: "500",
                            fontColor: '#675C53',
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 2
                        }
                    }],
                    yAxes: [{
                        categoryPercentage: 0.2,
                        // gridLines: {
                        //     display: false
                        // },
                        scaleLabel: {
                            display: true,
                            labelString: "Appointments",
                            fontFamily: "Montserrat, sans-serif",
                            fontStyle: "500",
                            fontColor: '#675C53',
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 2
                        }
                    }]
                },
                maintainAspectRatio: false,
                legend: {
                    display: false
                }
            }
        }
    },
    skillTrendChartChart: {
        object: '',
        config: {
            type: "horizontalBar",
            data: {
                labels: [],
                datasets: [{
                    label: "Completed Appointments",
                    backgroundColor: ["#8393E2", "#5261AC", "#6174D2", "#475497", "#A3B2F8"],
                    // hoverBackgroundColor: blue1_gradient,
                    data: []
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        // categoryPercentage: 0.3,
                        barThickness: 13,
                        maxBarThickness: 15,
                        // gridLines: {
                        //     display: false
                        // },
                        scaleLabel: {
                            display: true,
                            labelString: "SkillTrendChart",
                            fontFamily: "Montserrat, sans-serif",
                            fontStyle: "500",
                            fontColor: '#675C53',
                            fontSize: 14
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 4
                        }
                    }],
                    yAxes: [{
                        // categoryPercentage: 0.3,
                        barThickness: 13,
                        maxBarThickness: 15,
                        gridLines: {
                            display: false
                        },
                        scaleLabel: {
                            display: false
                        },
                        ticks: {
                            beginAtZero: true,
                            stepSize: 2
                        }
                    }]
                },
                maintainAspectRatio: false,
                legend: {
                    display: false
                }
            }
        }
    },
    utilisationChart1Chart: {
        object: '',
        config: {
            type: "doughnut",
            data: {
                datasets: [{
                    data: [],
                    backgroundColor: ["#5261AC", "#ffab00"],
                    label: ["Use Service", "Don't Use Service"],
                }],
                labels: ["Use Service", "Don't Use Service"]
            },
            options: {
                // tooltips: {
                //     enabled: false
                // },
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 60,
                rotation: 180,
                elements: {
                    arc: {
                        borderWidth: 0
                    }
                },
                legendCallback: function(chart) {
                    var text = [];
                    text.push('<ul class="' + chart.id + '-legend chart-legend" style="list-style:none">');
                    for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                        text.push('<li><div class="legend-color" style="border-color:' + chart.data.datasets[0].backgroundColor[i] + '" ></div>');
                        if (chart.data.datasets[0].label[i]) {
                            text.push("<span class='legend-text'>" + chart.data.datasets[0].label[i] + "</span>");
                        }
                        text.push("</li>");
                    }
                    text.push("</ul>");
                    return text.join("");
                },
                legend: {
                    // position:'right'
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
        }
    },
    utilisationChart2Chart: {
        object: '',
        config: {
            type: "doughnut",
            data: {
                datasets: [{
                    data: [],
                    backgroundColor: ["#5261AC", "#ffab00"],
                    label: ["Use Service", "Don't Use Service"],
                }],
                labels: ["Use Service", "Don't Use Service"]
            },
            options: {
                // tooltips: {
                //     enabled: false
                // },
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 60,
                rotation: 180,
                elements: {
                    arc: {
                        borderWidth: 0
                    }
                },
                legendCallback: function(chart) {
                    var text = [];
                    text.push('<ul class="' + chart.id + '-legend chart-legend" style="list-style:none">');
                    for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                        text.push('<li><div class="legend-color" style="border-color:' + chart.data.datasets[0].backgroundColor[i] + '" ></div>');
                        if (chart.data.datasets[0].label[i]) {
                            text.push("<span class='legend-text'>" + chart.data.datasets[0].label[i] + "</span>");
                        }
                        text.push("</li>");
                    }
                    text.push("</ul>");
                    return text.join("");
                },
                legend: {
                    // position:'right'
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
        }
    }
};
/*
 * Audit tab common AJAX call
 */
function eapActivityTabAjaxCall(tier, options = null) {
    var age = $('.age').val();
    age = ((age) ? age.split('_') : age);
    var companyIds = $('#company_id').val();
    var age1 = ((age) ? age[0] : null);
    var age2 = ((age) ? age[1] : null);
    $.ajax({
        url: urls.eapActivity,
        type: 'POST',
        dataType: 'json',
        data: {
            tier: tier,
            companyId: ($.isNumeric(companyIds) ? companyIds : null),
            age1: ($.isNumeric(age1) ? age1 : null),
            age2: ($.isNumeric(age2) ? age2 : null),
            options: options
        }
    }).done(function(data) {
        loadEapActivityTabData(data, tier);
    }).fail(function(error) {
        toastr.error('Failed to load audit tab data.');
    })
}
/*
 * Load EAP Activity Tab Data Tier by Tier
 */
function loadEapActivityTabData(data, tier) {
    switch (tier) {
        case 1:
            $('#today-sessions').html(data.todaySession);
            $('#upcoming-sessions').html(data.upcomingSession);
            $('#completed-sessions').html(data.completedSession);
            $('#cancelled-sessions').html(data.cancelledSession);
            break;
        case 2:
            // initialize Skill Trend chart with blank data
            if (typeof eapActivity.appointmentTrendChart.object != "object") {
                eapActivity.appointmentTrendChart.object = new Chart($('#appointmentTrend'), eapActivity.appointmentTrendChart.config);
            }
            eapActivity.appointmentTrendChart.config.data.labels = data.appointmentTrend ? data.appointmentTrend.day : [];
            eapActivity.appointmentTrendChart.config.data.datasets[0].data = data.appointmentTrend ? data.appointmentTrend.count : [];
            eapActivity.appointmentTrendChart.config.data.datasets[0].backgroundColor = data.appointmentTrend ? poolColors(data.appointmentTrend.day.length) : [];
            eapActivity.appointmentTrendChart.object.update();
            break;
        case 3:
            // initialize Skill Trend chart with blank data
            if (typeof eapActivity.skillTrendChartChart.object != "object") {
                eapActivity.skillTrendChartChart.object = new Chart($('#SkillTrendChart'), eapActivity.skillTrendChartChart.config);
            }
            eapActivity.skillTrendChartChart.config.data.labels = data.skillTrend ? data.skillTrend.categoriesSkill : [];
            eapActivity.skillTrendChartChart.config.data.datasets[0].data = data.skillTrend ? data.skillTrend.totalAssignUser : [];
            eapActivity.skillTrendChartChart.config.data.datasets[0].backgroundColor = data.skillTrend ? poolColors(data.skillTrend.categoriesSkill.length) : [];
            eapActivity.skillTrendChartChart.object.update();
            break;
        case 4:
            $('#total-counsellors').html(data.totalCounsellors);
            $('#active-counsellors').html(data.activeCounsellors);

            // intilize Utilization chart with blank data
            if (typeof eapActivity.utilisationChart1Chart.object != "object") {
                eapActivity.utilisationChart1Chart.object = new Chart($('#UtilisationChart1'), eapActivity.utilisationChart1Chart.config);
            }

            // Update Utilization chart data
            eapActivity.utilisationChart1Chart.config.data.datasets[0].data = data.utilization ? data.utilization : [];
            eapActivity.utilisationChart1Chart.object.update();
            document.getElementById("UtilisationChart1").innerHTML = eapActivity.utilisationChart1Chart.object.generateLegend();


            // intilize Referral Rate chart with blank data
            if (typeof eapActivity.utilisationChart2Chart.object != "object") {
                eapActivity.utilisationChart2Chart.object = new Chart($('#UtilisationChart2'), eapActivity.utilisationChart2Chart.config);
            }

            // Update Referral Rate chart data
            eapActivity.utilisationChart2Chart.config.data.datasets[0].data = data.referrerRate ? data.referrerRate : [];
            eapActivity.utilisationChart2Chart.object.update();
            document.getElementById("UtilisationChart2").innerHTML = eapActivity.utilisationChart2Chart.object.generateLegend();
            break;
        default:
            toastr.error('Something went wrong.!');
            break;
    }
}
var digitalTherapy = {
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
                            labelString: "Sessions",
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

function digitalTherapyTabAjaxCall(tier, options = null) {
    var age = $('.age').val();
    age = ((age) ? age.split('_') : age);
    var roleSlug = $("#roleSlug").val();
    var pattern = /^[0-9,]*$/g;

    if($('#roleType').val() == 1) {
        if($('#industry_id').val() != '' && $('#dtcompany_id').val() != '') {
            var companyIds = $('#dtcompany_id').val();
        } else if($('#dtcompany_id').val() != '') {
             var companyIds = $('#dtcompany_id').val();
        } else {
            var companyIds = $('#dtCompaniesId').val();
        }
    } else {
        var companyIds = ($('#dtcompany_id').val() != '') ? $('#dtcompany_id').val() : null;
    }

    if(roleSlug!= 'super_admin' && roleSlug!= 'wellbeing_specialist' && roleSlug!= 'wellbeing_team_lead' && roleSlug!= 'counsellor'){
        companyIds = companyIds.match(pattern) ? companyIds : null ;
    }else{
        companyIds = $.isNumeric(companyIds) ? companyIds : null;
    }

    if($('#service_id').val() != 'all') {
        var serviceIds = $('#service_id').val();
    } else {
        var serviceIds = $('#serviceIds').val();
    }
    var departmentId = $('#department_id').val();
    var locationId = $('#location_id').val();
    var age1 = ((age) ? age[0] : null);
    var age2 = ((age) ? age[1] : null);
    $.ajax({
        url: urls.digitalTherapy,
        type: 'POST',
        dataType: 'json',
        data: {
            tier: tier,
            companyId: companyIds,
            departmentId: ($.isNumeric(departmentId) ? departmentId : null),
            locationId: ($.isNumeric(locationId) ? locationId : null),
            serviceId: serviceIds ,
            age1: ($.isNumeric(age1) ? age1 : null),
            age2: ($.isNumeric(age2) ? age2 : null),
            options: options
        }
    }).done(function(data) {
        loadDigitalTherapyTabData(data, tier);
    }).fail(function(error) {
        toastr.error('Failed to load audit tab data.');
    })
}
/*
 * Load EAP Activity Tab Data Tier by Tier
 */
function loadDigitalTherapyTabData(data, tier) {
    switch (tier) {
        case 1:
            $('#dt-today-sessions').html(data.todaySession);
            $('#dt-upcoming-sessions').html(data.upcomingSession);
            $('#dt-completed-sessions').html(data.completedSession);
            $('#dt-cancelled-sessions').html(data.cancelledSession);
            break;
        case 2:
            // initialize Skill Trend chart with blank data
            if (typeof digitalTherapy.appointmentTrendChart.object != "object") {
                if (data.appointmentTrend != undefined) {
                    let maximumAppointmentTrendCount = Math.max.apply(Math,data.appointmentTrend.count);
                    if (maximumAppointmentTrendCount >= 530) {
                        digitalTherapy.appointmentTrendChart.config.options.scales.yAxes[0].ticks.stepSize = 50;
                    } else if (maximumAppointmentTrendCount >= 430) {
                        digitalTherapy.appointmentTrendChart.config.options.scales.yAxes[0].ticks.stepSize = 40;
                    } else if (maximumAppointmentTrendCount >= 330) {
                        digitalTherapy.appointmentTrendChart.config.options.scales.yAxes[0].ticks.stepSize = 30;
                    } else if (maximumAppointmentTrendCount >= 230) {
                        digitalTherapy.appointmentTrendChart.config.options.scales.yAxes[0].ticks.stepSize = 20;
                    } else if (maximumAppointmentTrendCount >= 130) {
                        digitalTherapy.appointmentTrendChart.config.options.scales.yAxes[0].ticks.stepSize = 10;
                    } else if (maximumAppointmentTrendCount >= 100) {
                        digitalTherapy.appointmentTrendChart.config.options.scales.yAxes[0].ticks.stepSize = 8;
                    } else if (maximumAppointmentTrendCount >= 70) {
                        digitalTherapy.appointmentTrendChart.config.options.scales.yAxes[0].ticks.stepSize = 6;
                    } else if (maximumAppointmentTrendCount >= 40) {
                        digitalTherapy.appointmentTrendChart.config.options.scales.yAxes[0].ticks.stepSize = 4;
                    }
                }
                digitalTherapy.appointmentTrendChart.object = new Chart($('#dtAppointmentTrend'), digitalTherapy.appointmentTrendChart.config);
            }
            digitalTherapy.appointmentTrendChart.config.data.labels = data.appointmentTrend ? data.appointmentTrend.day : [];
            digitalTherapy.appointmentTrendChart.config.data.datasets[0].data = data.appointmentTrend ? data.appointmentTrend.count : [];
            digitalTherapy.appointmentTrendChart.config.data.datasets[0].backgroundColor = data.appointmentTrend ? poolColors(data.appointmentTrend.day.length) : [];
            digitalTherapy.appointmentTrendChart.object.update();
            break;
        case 3:
            // initialize Skill Trend chart with blank data
            if (typeof digitalTherapy.skillTrendChartChart.object != "object") {
                digitalTherapy.skillTrendChartChart.object = new Chart($('#dtSkillTrendChart'), digitalTherapy.skillTrendChartChart.config);
            }
            digitalTherapy.skillTrendChartChart.config.data.labels = data.skillTrend ? data.skillTrend.categoriesSkill : [];
            digitalTherapy.skillTrendChartChart.config.data.datasets[0].data = data.skillTrend ? data.skillTrend.totalAssignUser : [];
            digitalTherapy.skillTrendChartChart.config.data.datasets[0].backgroundColor = data.skillTrend ? poolColors(data.skillTrend.categoriesSkill.length) : [];
            digitalTherapy.skillTrendChartChart.object.update();
            break;
        case 4:
            $('#total-wellbeingspecialists').html(data.totalCounsellors);
            $('#active-wellbeingspecialists').html(data.activeCounsellors);

            // intilize Utilization chart with blank data
            // if (typeof digitalTherapy.utilisationChart1Chart.object != "object") {
            //     digitalTherapy.utilisationChart1Chart.object = new Chart($('#dtUtilisationChart1'), digitalTherapy.utilisationChart1Chart.config);
            // }

            // // Update Utilization chart data
            // digitalTherapy.utilisationChart1Chart.config.data.datasets[0].data = data.utilization ? data.utilization : [];
            // digitalTherapy.utilisationChart1Chart.object.update();
            // document.getElementById("dtUtilisationChart1").innerHTML = digitalTherapy.utilisationChart1Chart.object.generateLegend();


            // intilize Referral Rate chart with blank data
            // if (typeof digitalTherapy.utilisationChart2Chart.object != "object") {
            //     digitalTherapy.utilisationChart2Chart.object = new Chart($('#dtUtilisationChart2'), digitalTherapy.utilisationChart2Chart.config);
            // }

            // // Update Referral Rate chart data
            // digitalTherapy.utilisationChart2Chart.config.data.datasets[0].data = data.referrerRate ? data.referrerRate : [];
            // digitalTherapy.utilisationChart2Chart.object.update();
            // document.getElementById("dtUtilisationChart2").innerHTML = digitalTherapy.utilisationChart2Chart.object.generateLegend();
            break;
        default:
            toastr.error('Something went wrong.!');
            break;
    }
}
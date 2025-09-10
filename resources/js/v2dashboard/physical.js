/*
 * Chart declarations
 */
var physicalCharts = {
    physicalHsDoughnut: {
        object: '',
        config: {
            type: 'doughnut',
            data: {
                labels: ["Low", "Moderate", "High"],
                datasets: [{
                    data: [],
                    backgroundColor: ["#E80707", "#FFA304", "#45D25D"],
                    hoverBackgroundColor: ["#E80707", "#FFA304", "#45D25D"]
                }]
            },
            options: {
                cutoutPercentage: 80,
                elements: {
                    center: {
                        text: '',
                        color: '#000',
                        fontStyle: 'Arial',
                        sidePadding: 200
                    }
                },
                legend: {
                    display: false
                },
                plugins: {
                    labels: [{
                        render: 'percentage',
                        precision: 1,
                        fontColor: '#000',
                        fontSize: 16,
                        position: 'outside',
                        arc: true,
                    }]
                },
                tooltips: {
                    enabled: false
                }
            }
        }
    },
    exerciseRangeDoughnut: {
        object: '',
        config: {
            type: 'doughnut',
            data: {
                labels: ["Low", "Moderate", "High", "Very High"],
                datasets: [{
                    data: [],
                    backgroundColor: ["#E80707", "#FFA304", "#45D25D", "#00a7d2"],
                    hoverBackgroundColor: ["#E80707", "#FFA304", "#45D25D", "#00a7d2"]
                }]
            },
            options: {
                cutoutPercentage: 75,
                legend: {
                    display: false
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex]
                            return (`${data.labels[tooltipItem.index]}: ${dataset.data[tooltipItem.index]}%`);
                        }
                    }
                }
            }
        }
    },
    stepsRangeDoughnut: {
        object: '',
        config: {
            type: 'doughnut',
            data: {
                labels: ["Low", "Moderate", "High"],
                datasets: [{
                    data: [],
                    backgroundColor: ["#E80707", "#FFA304", "#45D25D"],
                    hoverBackgroundColor: ["#E80707", "#FFA304", "#45D25D"]
                }]
            },
            options: {
                cutoutPercentage: 75,
                legend: {
                    display: false
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex]
                            return (`${data.labels[tooltipItem.index]}: ${dataset.data[tooltipItem.index]}%`);
                        }
                    }
                }
            }
        }
    },
    bmiDoughnut: {
        object: '',
        config: {
            type: 'doughnut',
            data: {
                labels: ["Underweight", "Normal", "Overweight", "Obese"],
                datasets: [{
                    data: [],
                    backgroundColor: ["#00a7d2", "#45D25D", "#FFA304", "#E80707"],
                    hoverBackgroundColor: ["#00a7d2", "#45D25D", "#FFA304", "#E80707"]
                }]
            },
            options: {
                cutoutPercentage: 75,
                legend: {
                    display: false
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex]
                            return (`${data.labels[tooltipItem.index]}: ${dataset.data[tooltipItem.index]}%`);
                        }
                    }
                }
            }
        }
    }
};
/*
 * Chart declarations
 */
var appUsagecharts = {
    stepsPeriod: {
        object: '',
        config: {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    label: "Percentage",
                    data: [],
                    fill: true,
                    backgroundColor: "rgb(0, 165, 209, 0.2)",
                    hoverBackgroundColor: "rgb(36, 145, 240)",
                    borderColor: "rgb(0, 165, 209)",
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
                            callback: function(label, index, labels) {
                                if (label == '20000') {
                                    return num_format_short(label) + '+';
                                } else {
                                    return num_format_short(label);
                                }
                            }
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Average daily steps",
                        },
                        barThickness: 10,
                        maxBarThickness: 15
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            beginAtZero: true,
                            max: 100,
                            callback: function(label, index, labels) {
                                return label;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "% of users",
                        },
                    }]
                }
            }
        }
    },
    caloriesPeriod: {
        object: '',
        config: {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    label: "Percentage",
                    data: [],
                    fill: true,
                    backgroundColor: "rgb(0, 165, 209, 0.2)",
                    hoverBackgroundColor: "rgb(36, 145, 240)",
                    borderColor: "rgb(0, 165, 209)",
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
                            callback: function(label, index, labels) {
                                if (label == '20000') {
                                    return num_format_short(label) + ' (KCal) +';
                                } else {
                                    return num_format_short(label) + ' (KCal)';
                                }
                            }
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Average daily calories",
                        },
                        barThickness: 10,
                        maxBarThickness: 15
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            beginAtZero: true,
                            max: 100,
                            callback: function(label, index, labels) {
                                return label;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "% of users",
                        },
                    }]
                }
            }
        }
    },
    popularFeedCategories: {
        object: '',
        config: {
            type: "horizontalBar",
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    fill: true,
                    backgroundColor: [],
                    borderColor: "#9e9e9e",
                    borderWidth: 0,
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
                            callback: function(label, index, labels) {
                                return num_format_short(label);
                            }
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Number of views",
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        barThickness: 8,
                        maxBarThickness: 8
                    }]
                }
            }
        }
    },
    meditationHoursChart: {
        object: '',
        config: {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    label: "Hrs",
                    data: [],
                    fill: true,
                    backgroundColor: "rgb(0, 165, 209, 0.2)",
                    hoverBackgroundColor: "rgb(36, 145, 240)",
                    borderColor: "rgb(0, 165, 209)",
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
                            beginAtZero: true
                        },
                        barThickness: 10,
                        maxBarThickness: 15
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            beginAtZero: true
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Hours",
                        }
                    }]
                }
            }
        }
    },
    popularMeditationCategories: {
        object: '',
        config: {
            type: "horizontalBar",
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    fill: true,
                    backgroundColor: [],
                    borderColor: "#9e9e9e",
                    borderWidth: 0,
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
                            callback: function(label, index, labels) {
                                return num_format_short(label);
                            }
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Number of views",
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        barThickness: 8,
                        maxBarThickness: 8
                    }]
                }
            }
        }
    },
    topMeditationTracks: {
        object: '',
        config: {
            type: "horizontalBar",
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    fill: true,
                    backgroundColor: [],
                    borderColor: "#9e9e9e",
                    borderWidth: 0,
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
                            callback: function(label, index, labels) {
                                return num_format_short(label);
                            }
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Track play count",
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        barThickness: 8,
                        maxBarThickness: 8
                    }]
                }
            }
        }
    }
};
/*
 * Chart declarations
 */
var psychologicalCharts = {
    psychologicalHsDoughnut: {
        object: '',
        config: {
            type: 'doughnut',
            data: {
                labels: ["Low", "Moderate", "High"],
                datasets: [{
                    data: [],
                    backgroundColor: ["#E80707", "#FFA304", "#45D25D"],
                    hoverBackgroundColor: ["#E80707", "#FFA304", "#45D25D"]
                }]
            },
            options: {
                cutoutPercentage: 80,
                elements: {
                    center: {
                        text: '',
                        color: '#000',
                        fontStyle: 'Arial',
                        sidePadding: 200
                    }
                },
                legend: {
                    display: false
                },
                plugins: {
                    labels: [{
                        render: 'percentage',
                        precision: 1,
                        fontColor: '#000',
                        fontSize: 16,
                        position: 'outside',
                        arc: true,
                    }]
                },
                tooltips: {
                    enabled: false
                }
            }
        }
    },
    meditationHoursChart: {
        object: '',
        config: {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    label: "Hrs",
                    data: [],
                    fill: true,
                    backgroundColor: "rgb(0, 165, 209, 0.2)",
                    hoverBackgroundColor: "rgb(36, 145, 240)",
                    borderColor: "rgb(0, 165, 209)",
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
                            beginAtZero: true
                        },
                        barThickness: 10,
                        maxBarThickness: 15
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            beginAtZero: true
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Hours",
                        }
                    }]
                }
            }
        }
    },
    popularMeditationCategories: {
        object: '',
        config: {
            type: "horizontalBar",
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    fill: true,
                    backgroundColor: [],
                    borderColor: "#9e9e9e",
                    borderWidth: 0,
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
                            callback: function(label, index, labels) {
                                return num_format_short(label);
                            }
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Number of views",
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        barThickness: 8,
                        maxBarThickness: 8
                    }]
                }
            }
        }
    },
    topMeditationTracks: {
        object: '',
        config: {
            type: "horizontalBar",
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    fill: true,
                    backgroundColor: [],
                    borderColor: "#9e9e9e",
                    borderWidth: 0,
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
                            callback: function(label, index, labels) {
                                return num_format_short(label);
                            }
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Track play count",
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
                        barThickness: 8,
                        maxBarThickness: 8
                    }]
                }
            }
        }
    },
    moodAnalysis: {
        object: '',
        config: {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    label: "Percentage",
                    data: [],
                    fill: true,
                    backgroundColor: "rgb(0, 165, 209, 0.2)",
                    hoverBackgroundColor: "rgb(36, 145, 240)",
                    borderColor: "rgb(0, 165, 209)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Types of mood",
                        },
                        barThickness: 10,
                        maxBarThickness: 15
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false
                        },
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
                }
            }
        }
    }
};
/*
 * Physical tab common AJAX call
 */
function physicalTabAjaxCall(tier, options = null) {
    var age = $('.age').val();
    age = ((age) ? age.split('_') : age);
    if($('#roleType').val() == 1) {
        if($('#industry_id').val() != '' && $('#company_id').val() != '') {
            var companyIds = $('#company_id').val();
        } else if($('#company_id').val() != '') {
             var companyIds = $('#company_id').val();
        } else {
            var companyIds = $('#companiesId').val();
        }
    } else {
        var companyIds = ($('#company_id').val() != '') ? $('#company_id').val() : null;
    }
    $.ajax({
        url: urls.behaviour,
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
        loadPhysicalTabData(data, tier);
    }).fail(function(error) {
        toastr.error('Failed to load Behaviour tab data.');
    })
}
/*
 * Load Physical Tab Data Tier by Tier
 */
function loadPhysicalTabData(data, tier) {
    switch (tier) {
        case 1:
            break;
            // intilize physical hs chart with blank data
            if (typeof physicalCharts.physicalHsDoughnut.object != "object") {
                physicalCharts.physicalHsDoughnut.object = new Chart($('#doughnutPhysicalScore'), physicalCharts.physicalHsDoughnut.config);
            }
            // Update physical hs chart category data
            physicalCharts.physicalHsDoughnut.config.data.datasets[0].data = data.hsCategoryData ? data.hsCategoryData : [];
            physicalCharts.physicalHsDoughnut.object.update();
            // Update physical hs sub categories chart data
            $('[data-sub-category-block]').empty();
            if (data.hsSubCategoryData) {
                data.hsSubCategoryData.forEach(function(item, key) {
                    $('[data-sub-category-block]').append('<div class="col-sm-4"> <div class="total-recipes-chart mt-auto mb-2"> <input class="knob-chart knob-chart-font-18" data-fgcolor="' + item.color + '" data-height="110" data-linecap="round" data-readonly="true" data-thickness=".15" data-width="110" readonly="readonly" type="text" value="' + item.percent + '"/> </div> <h6 class="text-center">' + item.sub_category + '</h6> </div>');
                });
                knobInit();
            }
            // Update physical hs attempted by data
            if (data.attemptedBy) {
                physicalCharts.physicalHsDoughnut.config.options.elements.center.text = 'Completed ' + data.attemptedBy.attemptedPercent + ' %';
                $('[data-attempted-by]').html(data.attemptedBy.attemptedPercent + ' %');
            } else {
                physicalCharts.physicalHsDoughnut.config.options.elements.center.text = 'Completed 0 %';
                $('[data-attempted-by]').html('0 %');
            }

            // teams block
            if (data.teamsData) {
                $('[data-team-block] [data-new-teams]').html(num_format_short(data.teamsData.newTeams));
                $('[data-team-block] [data-total-teams]').html(num_format_short(data.teamsData.totalTeams));
            }
            // challenges block
            if (data.challengesData) {
                $('[data-challenge-block] [data-ongoing-challenge]').html(num_format_short(data.challengesData.totalOngoingChallenges));
                $('[data-challenge-block] [data-upcoming-challenge]').html(num_format_short(data.challengesData.totalUpComingChallenges));
                $('[data-challenge-block] [data-completed-challenge]').html(num_format_short(data.challengesData.totalCompletedChallenges));
            }

            break;
        case 2:
            // Update exercise range chart data on date range filter
            if (data.change == 'daterangeExerciseRanges') {
                physicalCharts.exerciseRangeDoughnut.config.data.datasets[0].data = data.exercisesData || [];
                physicalCharts.exerciseRangeDoughnut.object.update();
                return;
            }
            // Update steps range chart data on date range filter
            if (data.change == 'daterangeStepRanges') {
                physicalCharts.stepsRangeDoughnut.config.data.datasets[0].data = data.stepsData || [];
                physicalCharts.stepsRangeDoughnut.object.update();
                return;
            }
            // intilize exercise range chart with blank data
            if (typeof physicalCharts.exerciseRangeDoughnut.object != "object") {
                physicalCharts.exerciseRangeDoughnut.object = new Chart($('#doughnutExerciseRanges'), physicalCharts.exerciseRangeDoughnut.config);
            }
            // Update exercise range chart data
            physicalCharts.exerciseRangeDoughnut.config.data.datasets[0].data = data.exercisesData ? data.exercisesData : [];
            physicalCharts.exerciseRangeDoughnut.object.update();
            // intilize steps range chart with blank data
            if (typeof physicalCharts.stepsRangeDoughnut.object != "object") {
                physicalCharts.stepsRangeDoughnut.object = new Chart($('#doughnutStepsRanges'), physicalCharts.stepsRangeDoughnut.config);
            }
            // Update steps range chart data
            physicalCharts.stepsRangeDoughnut.config.data.datasets[0].data = data.stepsData ? data.stepsData : [];
            physicalCharts.stepsRangeDoughnut.object.update();
            break;

            // intilize psychological hs chart with blank data
            if (typeof psychologicalCharts.psychologicalHsDoughnut.object != "object") {
                psychologicalCharts.psychologicalHsDoughnut.object = new Chart($('#doughnutPsychologicalScore'), psychologicalCharts.psychologicalHsDoughnut.config);
            }
            // Update psychological hs category chart data
            psychologicalCharts.psychologicalHsDoughnut.config.data.datasets[0].data = data.hsCategoryData ? data.hsCategoryData : [];
            psychologicalCharts.psychologicalHsDoughnut.object.update();
            // Update psychological hs sub categories chart data

            $('[data-pws-sub-category-block]').empty();
            if (data.hsSubCategoryData) {
                data.hsSubCategoryData.forEach(function(item, key) {
                    $('[data-pws-sub-category-block]').append('<div class="col-sm-4"> <div class="total-recipes-chart mt-auto mb-2"> <input class="knob-chart knob-chart-font-18" data-fgcolor="' + item.color + '" data-height="110" data-linecap="round" data-readonly="true" data-thickness=".15" data-width="110" readonly="readonly" type="text" value="' + item.percent + '"/> </div> <h6 class="text-center">' + item.sub_category + '</h6> </div>');
                });
                knobInit();
            }
            // Update psychological hs attempted by data
            if (data.attemptedBy) {
                psychologicalCharts.psychologicalHsDoughnut.config.options.elements.center.text = 'Completed ' + data.attemptedBy.attemptedPercent + ' %';
                $('[data-attempted-by]').html(data.attemptedBy.attemptedPercent + ' %');
            } else {
                psychologicalCharts.psychologicalHsDoughnut.config.options.elements.center.text = 'Completed 0 %';
                $('[data-attempted-by]').html('0 %');
            }
            break;
        case 3:
            // Update steps period chart data on date range filter
            if (data.change == 'daterangeStepsPeriod') {
                appUsagecharts.stepsPeriod.config.data.labels = data.stepsData ? data.stepsData.averageSteps : [];
                appUsagecharts.stepsPeriod.config.data.datasets[0].data = data.stepsData ? data.stepsData.userPercent : [];
                appUsagecharts.stepsPeriod.object.update();
                return;
            }
            // Update calories period chart data on date range filter
            if (data.change == 'daterangeCaloriesPeriod') {
                appUsagecharts.caloriesPeriod.config.data.labels = data.caloriesData ? data.caloriesData.averageCalories : [];
                appUsagecharts.caloriesPeriod.config.data.datasets[0].data = data.caloriesData ? data.caloriesData.userPercent : [];
                appUsagecharts.caloriesPeriod.object.update();
                return;
            }
            // intilize steps period chart with blank data
            if (typeof appUsagecharts.stepsPeriod.object != "object") {
                appUsagecharts.stepsPeriod.object = new Chart($('#chartStepsPeriod'), appUsagecharts.stepsPeriod.config, 1000);
            }
            // Update steps period chart data
            appUsagecharts.stepsPeriod.config.data.labels = data.stepsData ? data.stepsData.averageSteps : [];
            appUsagecharts.stepsPeriod.config.data.datasets[0].data = data.stepsData ? data.stepsData.userPercent : [];
            appUsagecharts.stepsPeriod.object.update();
            // intilize calories period chart with blank data
            if (typeof appUsagecharts.caloriesPeriod.object != "object") {
                appUsagecharts.caloriesPeriod.object = new Chart($('#chartCaloriesPeriods'), appUsagecharts.caloriesPeriod.config, 1000);
            }
            // Update calories period chart data
            appUsagecharts.caloriesPeriod.config.data.labels = data.caloriesData ? data.caloriesData.averageCalories : [];
            appUsagecharts.caloriesPeriod.config.data.datasets[0].data = data.caloriesData ? data.caloriesData.userPercent : [];
            appUsagecharts.caloriesPeriod.object.update();

            // intilize popular exercises chart with blank data
            // if (typeof physicalCharts.popularExercises.object != "object") {
            //     physicalCharts.popularExercises.object = new Chart($('#chartPopularExercises'), physicalCharts.popularExercises.config, 1000);
            // }
            // // Update popular exercises chart data
            // physicalCharts.popularExercises.config.data.labels = data.exercise ? data.exercise : [];
            // physicalCharts.popularExercises.config.data.datasets[0].data = data.percent ? data.percent : [];
            // physicalCharts.popularExercises.config.data.datasets[0].backgroundColor = data.exercise ? poolColors(data.exercise.length) : [];
            // physicalCharts.popularExercises.object.update();

            // sync details block
            if (data.syncDetails) {
                $('[data-sync-details] [data-sync-percent]').html(data.syncDetails.syncPercent + ' %');
                $('[data-sync-details] [data-notsync-percent]').html(data.syncDetails.notSyncPercent + ' %');
                $('[data-sync-details] [data-knob]').empty();
                $('[data-sync-details] [data-knob]').append('<input class="knob-chart knob-chart-font-18" data-fgcolor="#ffb35e" data-height="180" data-linecap="round" data-readonly="true" data-thickness=".1" data-width="180" readonly="readonly" type="text" value="' + data.syncDetails.syncPercent + ' %"/>');
                knobInit();
            }
            break;
        case 4:
            // Update BMI chart data on date range filter
            if (data.change == 'genderFilter') {
                physicalCharts.bmiDoughnut.config.data.datasets[0].data = data.bmiData ? data.bmiData : [];
                physicalCharts.bmiDoughnut.object.update();
                if (data.totalUsers && data.avgWeight) {
                    $('[data-bmi-block] [data-bmi-users]').html(num_format_short(data.totalUsers));
                    $('[data-bmi-block] [data-bmi-weight]').html(data.avgWeight + ' KG');
                } else {
                    $('[data-bmi-block] [data-bmi-users]').html(0);
                    $('[data-bmi-block] [data-bmi-weight]').html(0);
                }
                return;
            }
            // Recipe views block
            if (data.popularRecipesData) {
                $('[data-recipe-block] [data-recipe-logo] img').attr('src', data.popularRecipesData[0].logo);
                $('[data-recipe-block] [data-recipe-name]').html(data.popularRecipesData[0].name);
                $('[data-recipe-block] [data-recipe-views]').html(num_format_short(data.popularRecipesData[0].totalViews));
                $('[data-recipe-block] [data-recipe-cook]').html('Cooked by :' + data.popularRecipesData[0].chef);
                $('[data-recipe-block] [data-text]').html('Most views');
                $('[data-recipe-block] .sx-list').empty();
                data.popularRecipesData.forEach(function(item, key) {
                    if (key == 0) {
                        return;
                    }
                    $('[data-recipe-block] .sx-list').append('<li> <img alt="icon" class="" src="' + item.logo + '"/> <div class="sx-list-name-area"> <h6 class="sx-list-name">' + item.name + '</h6> <small data-recipe-cook=""> Cooked By : ' + item.chef + ' </small> </div> <div class="sx-list-hr"> ' + num_format_short(item.totalViews) + ' </div> </li>');
                });
            }
            if (!data.popularRecipesData) {
                $('[data-recipe-block] .sx-list').empty();
                $('[data-recipe-block] [data-recipe-logo] img').attr('src', defaultImage);
                $('[data-recipe-block] [data-recipe-name]').html(null);
                $('[data-recipe-block] [data-recipe-views]').html(null);
                $('[data-recipe-block] [data-recipe-cook]').html(null);
                $('[data-recipe-block] [data-text]').html('No Recipes Views Found.!');
            }
            // intilize BMI chart with blank data
            if (typeof physicalCharts.bmiDoughnut.object != "object") {
                physicalCharts.bmiDoughnut.object = new Chart($('#doughnutBMI'), physicalCharts.bmiDoughnut.config);
            }
            // Update BMI chart data
            physicalCharts.bmiDoughnut.config.data.datasets[0].data = data.bmiData ? data.bmiData : [];
            physicalCharts.bmiDoughnut.object.update();
            // BMI User and Avg Weight block
            if (data.totalUsers && data.avgWeight) {
                $('[data-bmi-block] [data-bmi-users]').html(num_format_short(data.totalUsers));
                $('[data-bmi-block] [data-bmi-weight]').html(data.avgWeight + ' KG');
            } else {
                $('[data-bmi-block] [data-bmi-users]').html(0);
                $('[data-bmi-block] [data-bmi-weight]').html(0);
            }

            // intilize moods analysis chart with blank data
            if (typeof psychologicalCharts.moodAnalysis.object != "object") {
                psychologicalCharts.moodAnalysis.object = new Chart($('#chartMoodsAnalysis'), psychologicalCharts.moodAnalysis.config);
            }
            // Update moods analysis chart data
            psychologicalCharts.moodAnalysis.config.data.labels = data.moodAnalysis ? data.moodAnalysis.title : [];
            psychologicalCharts.moodAnalysis.config.data.datasets[0].data = data.moodAnalysis ? data.moodAnalysis.percent : [];
            psychologicalCharts.moodAnalysis.object.update();

            // Active team block
            if (data.activeTeamData) {
                $('[data-active-team] [data-first-team-logo] img').attr('src', data.activeTeamData[0].logo);
                $('[data-active-team] [data-first-team-name]').html(data.activeTeamData[0].name);
                $('[data-active-team] [data-first-team-company]').html(null);
                if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                    $('[data-active-team] [data-first-team-company]').html('Company : ' + data.activeTeamData[0].company);
                }
                $('[data-active-team] [data-first-team-hours]').html(data.activeTeamData[0].averageHours + ' hrs');
                $('[data-active-team] [data-text]').html('Average Exercise Hours');
                $('[data-active-team] .sx-list').empty();
                data.activeTeamData.forEach(function(item, key) {
                    if (key == 0) {
                        return;
                    }
                    if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                        $('[data-active-team] .sx-list').append('<li> <img alt="icon" class="" src="' + item.logo + '"/> <div class="sx-list-name-area"> <h6 class="sx-list-name">' + item.name + '</h6> <small> Company : ' + item.company + ' </small> </div> <div class="sx-list-hr"> ' + item.averageHours + ' hrs </div> </li>');
                    } else {
                        $('[data-active-team] .sx-list').append('<li> <img alt="icon" class="" src="' + item.logo + '"/> <div class="sx-list-name-area"> <h6 class="sx-list-name">' + item.name + '</h6> </div> <div class="sx-list-hr"> ' + item.averageHours + ' hrs </div> </li>');
                    }
                });
            }
            // Active individual block
            if (data.activeIndividualData) {
                $('[data-active-individual] [data-first-user-logo] img').attr('src', data.activeIndividualData[0].logo);
                $('[data-active-individual] [data-first-user-name]').html(data.activeIndividualData[0].name);
                $('[data-active-individual] [data-first-user-company]').html(null);
                if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                    $('[data-active-individual] [data-first-user-company]').html('Company : ' + data.activeIndividualData[0].company);
                }
                $('[data-active-individual] [data-first-user-hours]').html(data.activeIndividualData[0].totalHours + ' hrs');
                $('[data-active-individual] [data-text]').html('Total Exercise Hours');
                $('[data-active-individual] .sx-list').empty();
                data.activeIndividualData.forEach(function(item, key) {
                    if (key == 0) {
                        return;
                    }
                    if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                        $('[data-active-individual] .sx-list').append('<li> <img alt="icon" class="" src="' + item.logo + '"/> <div class="sx-list-name-area"> <h6 class="sx-list-name">' + item.name + '</h6> <small> Company : ' + item.company + ' </small> </div> <div class="sx-list-hr"> ' + item.totalHours + ' hrs </div> </li>');
                    } else {
                        $('[data-active-individual] .sx-list').append('<li> <img alt="icon" class="" src="' + item.logo + '"/> <div class="sx-list-name-area"> <h6 class="sx-list-name">' + item.name + '</h6> </div> <div class="sx-list-hr"> ' + item.totalHours + ' hrs </div> </li>');
                    }
                });
            }
            // Badges Earned block
            if (data.badgesEarnedData) {
                $('[data-badges-earned] [data-first-user-logo] img').attr('src', data.badgesEarnedData[0].logo);
                $('[data-badges-earned] [data-first-user-name]').html(data.badgesEarnedData[0].name);
                $('[data-badges-earned] [data-first-user-company]').html(null);
                if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                    $('[data-badges-earned] [data-first-user-company]').html('Company : ' + data.badgesEarnedData[0].company);
                }
                $('[data-badges-earned] [data-first-user-badges]').html(num_format_short(data.badgesEarnedData[0].mostBadges));
                $('[data-badges-earned] [data-text]').html('Total Badges Earned');
                $('[data-badges-earned] .sx-list').empty();
                data.badgesEarnedData.forEach(function(item, key) {
                    if (key == 0) {
                        return;
                    }
                    if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                        $('[data-badges-earned] .sx-list').append('<li> <img alt="icon" class="" src="' + item.logo + '"/> <div class="sx-list-name-area"> <h6 class="sx-list-name">' + item.name + '</h6> <small> Company : ' + item.company + ' </small> </div> <div class="sx-list-hr"> ' + num_format_short(item.mostBadges) + ' </div> </li>');
                    } else {
                        $('[data-badges-earned] .sx-list').append('<li> <img alt="icon" class="" src="' + item.logo + '"/> <div class="sx-list-name-area"> <h6 class="sx-list-name">' + item.name + '</h6> </div> <div class="sx-list-hr"> ' + num_format_short(item.mostBadges) + ' </div> </li>');
                    }
                });
            }
            if (!data.activeTeamData) {
                $('[data-active-team] .sx-list').empty();
                $('[data-active-team] [data-first-team-logo] img').attr('src', defaultImage);
                $('[data-active-team] [data-first-team-name]').html(null);
                $('[data-active-team] [data-first-team-hours]').html(null);
                $('[data-active-team] [data-first-team-company]').html(null);
                $('[data-active-team] [data-text]').html('Get Moving.!');
            }
            if (!data.activeIndividualData) {
                $('[data-active-individual] .sx-list').empty();
                $('[data-active-individual] [data-first-user-logo] img').attr('src', defaultImage);
                $('[data-active-individual] [data-first-user-name]').html(null);
                $('[data-active-individual] [data-first-user-hours]').html(null);
                $('[data-active-individual] [data-first-user-company]').html(null);
                $('[data-active-individual] [data-text]').html('Get Moving.!');
            }
            if (!data.badgesEarnedData) {
                $('[data-badges-earned] .sx-list').empty();
                $('[data-badges-earned] [data-first-user-logo] img').attr('src', defaultImage);
                $('[data-badges-earned] [data-first-user-name]').html(null);
                $('[data-badges-earned] [data-first-user-badges]').html(null);
                $('[data-badges-earned] [data-first-user-company]').html(null);
                $('[data-badges-earned] [data-text]').html('Get Moving.!');
            }
            break;
        default:
            toastr.error('Something went wrong.!');
            break;
    }
}
/*
 * Code for week/month/year filter in popular exercises chart
 */
$(document).on('click', '[data-popular-exercises-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('popular-exercises-duration') || null);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDatePopularExercises = duration;
    physicalTabAjaxCall(3, options);
});
/*
 * Code for male/female gender filter in BMI chart
 */
$(document).on("click", '[data-gender-filter] li', function(e) {
    var _this = $(this);
    var gender = (_this.data('gender') || null);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.change = 'genderFilter';
    options.gender = gender;
    physicalTabAjaxCall(4, options);
});

/*
 * Code for week/month/year filter in moods analysis chart
 */
$(document).on('click', '[data-mood-analysis-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('mood-analysis-duration') || null);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDateMoodsAnalysis = duration;
    physicalTabAjaxCall(4, options);
});
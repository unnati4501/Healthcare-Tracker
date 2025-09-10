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
var appUsagechartssecond = {
    popularWebinarCategories: {
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
    popularMasterclassCategories: {
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
                            labelString: "Number of enrollments",
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
    topWebinarTracks: {
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
    topMasterclassTracks: {
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
                            labelString: "Number of enrollments",
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
 * App usage tab common AJAX call
 */
function appUsageTabAjaxCall(tier, options = null) {
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
        url: urls.usage,
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
        loadAppUsageTabData(data, tier);
    }).fail(function(error) {
        toastr.error('Failed to load Usage tab data.');
    })
}
/*
 * Psychological tab common AJAX call
 */
function psychologicalTabAjaxCall(tier, options = null) {
    var age = $('#age').val();
    age = ((age) ? age.split('_') : age);
    $.ajax({
        url: urls.psychological,
        type: 'POST',
        dataType: 'json',
        data: {
            tier: tier,
            companyId: $('#company_id').val(),
            departmentId: $('#department_id').val(),
            age1: ((age) ? age[0] : null),
            age2: ((age) ? age[1] : null),
            options: options
        }
    }).done(function(data) {
        loadPsychologicalTabData(data, tier);
    }).fail(function(error) {
        toastr.error('Failed to load Psychological tab data.');
    })
}
/*
 * Load App Usage Tab Data Tier by Tier
 */
function loadAppUsageTabData(data, tier) {
    switch (tier) {
        case 1:
            // users block
            if (data.usersData) {
                $('[data-user-block] [data-active-users]').html(num_format_short(data.usersData.activeUsers));
                $('[data-user-block] [data-active-total-users]').html(num_format_short(data.usersData.totalActiveUsers));
                $('[data-user-block] [data-total-users]').html(num_format_short(data.usersData.totalUsers));
                $('[data-user-block] [data-active-percent]').html(data.usersData.activeUsersPercent + '%');
                $('[data-user-block] [data-active-percent]').parent().css("width", data.usersData.activeUsersPercent + '%');
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
            var meditationHoursLength = $('#meditationhours').length;
            if(meditationHoursLength >= 1){
                // intilize meditation hours chart with blank data
                if (typeof psychologicalCharts.meditationHoursChart.object != "object") {
                    psychologicalCharts.meditationHoursChart.object = new Chart($('#chartMeditationHours'), psychologicalCharts.meditationHoursChart.config);
                }
                // Update meditation hours chart data
                psychologicalCharts.meditationHoursChart.config.data.labels = data.labels ? data.labels : [];
                psychologicalCharts.meditationHoursChart.config.data.datasets[0].data = data.data ? data.data : [];
                psychologicalCharts.meditationHoursChart.object.update();
            }
            // Update psychological hs attempted by data
            if (data.totalUsers && data.avgMeditationTime) {
                $('[data-meditiation-hours-block] [data-meditation-hours-total-users]').html(data.totalUsers);
                $('[data-meditiation-hours-block] [data-meditation-hours-avg-hours]').html(data.avgMeditationTime);
            } else {
                $('[data-meditiation-hours-block] [data-meditation-hours-total-users]').html(0);
                $('[data-meditiation-hours-block] [data-meditation-hours-avg-hours]').html(0);
            }
            break;
        case 3:

            // intilize popular feed categories chart with blank data
            if (typeof appUsagecharts.popularFeedCategories.object != "object") {
                appUsagecharts.popularFeedCategories.object = new Chart($('#popularFeedCategories'), appUsagecharts.popularFeedCategories.config, 1000);
            }
            // Update popular feed categories chart data
            appUsagecharts.popularFeedCategories.config.data.labels = data.popularFeedCategoriesData ? data.popularFeedCategoriesData.feedCategory : [];
            appUsagecharts.popularFeedCategories.config.data.datasets[0].data = data.popularFeedCategoriesData ? data.popularFeedCategoriesData.totalViews : [];
            appUsagecharts.popularFeedCategories.config.data.datasets[0].backgroundColor = data.popularFeedCategoriesData ? poolColors(data.popularFeedCategoriesData.feedCategory.length) : [];
            appUsagecharts.popularFeedCategories.object.update();
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
            // intilize popular meditation categories chart with blank data
            if (typeof psychologicalCharts.popularMeditationCategories.object != "object") {
                psychologicalCharts.popularMeditationCategories.object = new Chart($('#chartPopularMeditationCategory'), psychologicalCharts.popularMeditationCategories.config);
            }
            // Update popular meditation categories chart data
            psychologicalCharts.popularMeditationCategories.config.data.labels = data.popularMeditationCategoriesData ? data.popularMeditationCategoriesData.meditationCategory : [];
            psychologicalCharts.popularMeditationCategories.config.data.datasets[0].data = data.popularMeditationCategoriesData ? data.popularMeditationCategoriesData.totalViews : [];
            psychologicalCharts.popularMeditationCategories.config.data.datasets[0].backgroundColor = data.popularMeditationCategoriesData ? poolColors(data.popularMeditationCategoriesData.meditationCategory.length) : [];
            psychologicalCharts.popularMeditationCategories.object.update();
            break;
        case 4:
            // Update top meditation tracks chart data on date range filter
            if (data.change == 'daterangeTopMeditationTracks') {
                psychologicalCharts.topMeditationTracks.config.data.labels = data.topMeditationTracksData ? data.topMeditationTracksData.meditationTitle : [];
                psychologicalCharts.topMeditationTracks.config.data.datasets[0].data = data.topMeditationTracksData ? data.topMeditationTracksData.totalViews : [];
                psychologicalCharts.topMeditationTracks.config.data.datasets[0].backgroundColor = data.topMeditationTracksData ? poolColors(data.topMeditationTracksData.meditationTitle.length) : [];
                psychologicalCharts.topMeditationTracks.object.update();
                return;
            }
            // intilize top meditation tracks chart with blank data
            if (typeof psychologicalCharts.topMeditationTracks.object != "object") {
                psychologicalCharts.topMeditationTracks.object = new Chart($('#chartTopTrack'), psychologicalCharts.topMeditationTracks.config);
            }
            // Update top meditation tracks chart data
            psychologicalCharts.topMeditationTracks.config.data.labels = data.topMeditationTracksData ? data.topMeditationTracksData.meditationTitle : [];
            psychologicalCharts.topMeditationTracks.config.data.datasets[0].data = data.topMeditationTracksData ? data.topMeditationTracksData.totalViews : [];
            psychologicalCharts.topMeditationTracks.config.data.datasets[0].backgroundColor = data.topMeditationTracksData ? poolColors(data.topMeditationTracksData.meditationTitle.length) : [];
            psychologicalCharts.topMeditationTracks.object.update();

            // intilize popular webinar categories chart with blank data
            if (typeof appUsagechartssecond.popularWebinarCategories.object != "object") {
                appUsagechartssecond.popularWebinarCategories.object = new Chart($('#popularWebinarCategories'), appUsagechartssecond.popularWebinarCategories.config, 1000);
            }
            // Update popular webinar categories chart data
            appUsagechartssecond.popularWebinarCategories.config.data.labels = data.popularWebinarCategoriesData ? data.popularWebinarCategoriesData.webinarCategory : [];
            appUsagechartssecond.popularWebinarCategories.config.data.datasets[0].data = data.popularWebinarCategoriesData ? data.popularWebinarCategoriesData.totalViews : [];
            appUsagechartssecond.popularWebinarCategories.config.data.datasets[0].backgroundColor = data.popularWebinarCategoriesData ? poolColors(data.popularWebinarCategoriesData.webinarCategory.length) : [];
            appUsagechartssecond.popularWebinarCategories.object.update();

            // intilize popular masterclass categories chart with blank data
            if (typeof appUsagechartssecond.popularMasterclassCategories.object != "object") {
                appUsagechartssecond.popularMasterclassCategories.object = new Chart($('#popularMasterclassCategories'), appUsagechartssecond.popularMasterclassCategories.config, 1000);
            }
            // Update popular webinar categories chart data
            appUsagechartssecond.popularMasterclassCategories.config.data.labels = data.popularMasterclassCategoriesData ? data.popularMasterclassCategoriesData.masterclassCategory : [];
            appUsagechartssecond.popularMasterclassCategories.config.data.datasets[0].data = data.popularMasterclassCategoriesData ? data.popularMasterclassCategoriesData.totalEnrollments : [];
            appUsagechartssecond.popularMasterclassCategories.config.data.datasets[0].backgroundColor = data.popularMasterclassCategoriesData ? poolColors(data.popularMasterclassCategoriesData.masterclassCategory.length) : [];
            appUsagechartssecond.popularMasterclassCategories.object.update();


            // Update top 10 Webinar chart data on date range filter
            if (data.change == 'daterangeTopWebinarTracks') {
                appUsagechartssecond.topWebinarTracks.config.data.labels = data.topWebinarsData ? data.topWebinarsData.webinarTitle : [];
                appUsagechartssecond.topWebinarTracks.config.data.datasets[0].data = data.topWebinarsData ? data.topWebinarsData.totalViews : [];
                appUsagechartssecond.topWebinarTracks.config.data.datasets[0].backgroundColor = data.topWebinarsData ? poolColors(data.topWebinarsData.webinarTitle.length) : [];
                appUsagechartssecond.topWebinarTracks.object.update();
                return;
            }
            // intilize top 10 Webinar chart with blank data
            if (typeof appUsagechartssecond.topWebinarTracks.object != "object") {
                appUsagechartssecond.topWebinarTracks.object = new Chart($('#chartTopWebinar'), appUsagechartssecond.topWebinarTracks.config);
            }
            // Update top 10 webinar chart data
            appUsagechartssecond.topWebinarTracks.config.data.labels = data.topWebinarsData ? data.topWebinarsData.webinarTitle : [];
            appUsagechartssecond.topWebinarTracks.config.data.datasets[0].data = data.topWebinarsData ? data.topWebinarsData.totalViews : [];
            appUsagechartssecond.topWebinarTracks.config.data.datasets[0].backgroundColor = data.topWebinarsData ? poolColors(data.topWebinarsData.webinarTitle.length) : [];
            appUsagechartssecond.topWebinarTracks.object.update();

            // Update top 10 Masterclass chart data on date range filter
            if (data.change == 'daterangeTopWebinarTracks') {
                appUsagechartssecond.topMasterclassTracks.config.data.labels = data.topMasterclassData ? data.topMasterclassData.masterclassTitle : [];
                appUsagechartssecond.topMasterclassTracks.config.data.datasets[0].data = data.topMasterclassData ? data.topMasterclassData.totalEnrollment : [];
                appUsagechartssecond.topMasterclassTracks.config.data.datasets[0].backgroundColor = data.topMasterclassData ? poolColors(data.topMasterclassData.masterclassTitle.length) : [];
                appUsagechartssecond.topMasterclassTracks.object.update();
                return;
            }
            // intilize top 10 Masterclass chart with blank data
            if (typeof appUsagechartssecond.topMasterclassTracks.object != "object") {
                appUsagechartssecond.topMasterclassTracks.object = new Chart($('#chartTopMasterclass'), appUsagechartssecond.topMasterclassTracks.config);
            }
            // Update top 10 Masterclass chart data
            appUsagechartssecond.topMasterclassTracks.config.data.labels = data.topMasterclassData ? data.topMasterclassData.masterclassTitle : [];
            appUsagechartssecond.topMasterclassTracks.config.data.datasets[0].data = data.topMasterclassData ? data.topMasterclassData.totalEnrollment : [];
            appUsagechartssecond.topMasterclassTracks.config.data.datasets[0].backgroundColor = data.topMasterclassData ? poolColors(data.topMasterclassData.masterclassTitle.length) : [];
            appUsagechartssecond.topMasterclassTracks.object.update();
            break;
        default:
            toastr.error('Something went wrong.!');
            break;
    }
}
/*
 * Code for week/month/year filter in popular feed categories chart
 */
$(document).on('click', '[data-popular-feeds-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('popular-feeds-duration') || null);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDatePopularFeeds = duration;
    appUsageTabAjaxCall(3, options);
});
/*
 * Code for week/month/year filter in meditation hours chart
 */
$(document).on('click', '[data-meditiation-hours-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('meditiation-hours-duration') || null);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDateMeditationHours = duration;
    psychologicalTabAjaxCall(2, options);
});
/*
 * Code for week/month/year filter in popular Webinar categories chart
 */
$(document).on('click', '[data-popular-webinar-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('popular-webinar-duration') || null);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    var fromDateTopWebinar = moment($('#daterangeTopWebinarTracksFrom').datepicker("getDate"));
    var endDateTopWebinars = moment($('#daterangeTopWebinarTracksFromTo').datepicker("getDate")).endOf('month');
    var fromDateTopWebinar = fromDateTopWebinar.format('YYYY-MM-DD 00:00:00');
    var endDateTopWebinars = endDateTopWebinars.format('YYYY-MM-DD 23:59:59');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDatePopularWebinar = duration;
    options.change = 'daterangeTopWebinarTracks';
    options.fromDateTopWebinar = fromDateTopWebinar;
    options.endDateTopWebinars = endDateTopWebinars;
    appUsageTabAjaxCall(4, options);
});
/*
 * Code for week/month/year filter in popular Masterclass categories chart
 */
$(document).on('click', '[data-popular-masterclass-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('popular-masterclass-duration') || null);
    var parent = _this.parent();
    var fromDateTopWebinar = moment($('#daterangeTopWebinarTracksFrom').datepicker("getDate"));
    var endDateTopWebinars = moment($('#daterangeTopWebinarTracksFromTo').datepicker("getDate")).endOf('month');
    var fromDateTopWebinar = fromDateTopWebinar.format('YYYY-MM-DD 00:00:00');
    var endDateTopWebinars = endDateTopWebinars.format('YYYY-MM-DD 23:59:59');
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDatePopularMasterclass = duration;
    options.change = 'daterangeTopWebinarTracks';
    options.fromDateTopWebinar = fromDateTopWebinar;
    options.endDateTopWebinars = endDateTopWebinars;
    appUsageTabAjaxCall(4, options);
});
/*
 * Code for all/week/month/year filter in top 10 Masterclass categories chart
 */
$(document).on('click', '[data-top-masterclass-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('top-masterclass-duration') || null);
    var fromDateTopWebinar = moment($('#daterangeTopWebinarTracksFrom').datepicker("getDate"));
    var endDateTopWebinars = moment($('#daterangeTopWebinarTracksFromTo').datepicker("getDate")).endOf('month');
    var fromDateTopWebinar = fromDateTopWebinar.format('YYYY-MM-DD 00:00:00');
    var endDateTopWebinars = endDateTopWebinars.format('YYYY-MM-DD 23:59:59');
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDateTopMasterclass = duration;
    options.change = 'daterangeTopWebinarTracks';
    options.fromDateTopWebinar = fromDateTopWebinar;
    options.endDateTopWebinars = endDateTopWebinars;
    appUsageTabAjaxCall(4, options);
});
/*
 * Chart declarations
 */
var appUsageCharts = {
    meditationHoursChart: {
        object: '',
        config: {
            type: "line",
            data: {
                labels: [],
                datasets: [{
                    label: "Minutes",
                    backgroundColor: "#00A5D1",
                    borderColor: "#00A5D1",
                    borderWidth: 2,
                    fill: false,
                    data: []
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
                            display: false,
                            // labelString: "Customer Names",
                            // fontFamily: "Rubik, sans-serif"
                        },
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#797979',
                        }
                    }],
                    yAxes: [{
                        categoryPercentage: 0.2,
                        scaleLabel: {
                            display: true,
                            labelString: "Minutes",
                            fontFamily: "Montserrat, sans-serif",
                            fontStyle: "500",
                            fontColor: '#675C53',
                            fontSize: 14
                        },
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#797979',
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
                        barThickness: 13,
                        maxBarThickness: 15
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
                        barThickness: 13,
                        maxBarThickness: 15
                    }]
                }
            }
        }
    },
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
                        barThickness: 13,
                        maxBarThickness: 15,
                        ticks: {
                            callback: function(value) {
                                if (value.length > 30){
                                    return value.substr(0, 30) + '...';
                                } else {
                                    return value;
                                }
                            },
                        } 
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
                        barThickness: 13,
                        maxBarThickness: 15,
                        ticks: {
                            callback: function(value) {
                                if (value.length > 30){
                                    return value.substr(0, 30) + '...';
                                } else {
                                    return value;
                                }
                            },
                        } 
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
                        barThickness: 13,
                        maxBarThickness: 15,
                        ticks: {
                            callback: function(value) {
                                if (value.length > 30){
                                    return value.substr(0, 30) + '...';
                                } else {
                                    return value;
                                }
                            },
                        } 
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
                        barThickness: 13,
                        maxBarThickness: 15,
                        ticks: {
                            callback: function(value) {
                                if (value.length > 30){
                                    return value.substr(0, 30) + '...';
                                } else {
                                    return value;
                                }
                            },
                        } 
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
                        barThickness: 13,
                        maxBarThickness: 15,
                        ticks: {
                            callback: function(value) {
                                if (value.length > 30){
                                    return value.substr(0, 30) + '...';
                                } else {
                                    return value;
                                }
                            },
                        }                         
                    }]
                }
            }
        }
    },
    topFeedsChart: {
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
                        barThickness: 13,
                        maxBarThickness: 15,
                        ticks: {
                            callback: function(value) {
                                if (value.length > 30){
                                    return value.substr(0, 30) + '...';
                                } else {
                                    return value;
                                }
                            },
                        }      
                    }]
                }     
            }
        }
    },
};
/*
 * App usage tab common AJAX call
 */
function appUsageTabAjaxCall(tier, options = null) {
    var age = $('.age').val();
    age = ((age) ? age.split('_') : age);
    var roleSlug = $("#roleSlug").val();
    var pattern = /^[0-9,]*$/g;

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

    if(roleSlug!= 'super_admin' && roleSlug!= 'wellbeing_specialist' && roleSlug!= 'wellbeing_team_lead' && roleSlug!= 'counsellor'){
        companyIds = companyIds.match(pattern) ? companyIds : null ;
    }else{
        companyIds = $.isNumeric(companyIds) ? companyIds : null;
    }

    var departmentId = $('#department_id').val();
    var locationId = $('#location_id').val();
    var age1 = ((age) ? age[0] : null);
    var age2 = ((age) ? age[1] : null);
    $.ajax({
        url: urls.usage,
        type: 'POST',
        dataType: 'json',
        data: {
            tier: tier,
            companyId: companyIds,
            departmentId: ($.isNumeric(departmentId) ? departmentId : null),
            locationId: ($.isNumeric(locationId) ? locationId : null),
            age1: ($.isNumeric(age1) ? age1 : null),
            age2: ($.isNumeric(age2) ? age2 : null),
            options: options
        }
    }).done(function(data) {
        loadAppUsageTabData(data, tier);
    }).fail(function(error) {
        toastr.error('Failed to load Usage tab data.');
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
                $('[data-user-block] [data-total-users]').html(num_format_short(data.usersData.totalUsers));
                $('[data-user-block] [data-active-users]').html(num_format_short(data.usersData.activeUsers));
                $('[data-user-block] [data-active-last-seven-days-users]').html(num_format_short(data.usersData.activeUsersForLast7Days));
            }
            var meditationHoursLength = $('#meditationhours').length;
            if (meditationHoursLength >= 1) {
                // initialize meditation hours chart with blank data
                if (typeof appUsageCharts.meditationHoursChart.object != "object") {
                    appUsageCharts.meditationHoursChart.object = new Chart($('#chartMeditationHours'), appUsageCharts.meditationHoursChart.config);
                }
                // Update meditation hours chart data
                appUsageCharts.meditationHoursChart.config.data.labels = data.meditationHoursData.labels ? data.meditationHoursData.labels : [];
                appUsageCharts.meditationHoursChart.config.data.datasets[0].data = data.meditationHoursData.data ? data.meditationHoursData.data : [];
                appUsageCharts.meditationHoursChart.object.update();
            }
            // Update meditation hours chart lenged data
            if (data.meditationHoursData.totalUsers && data.meditationHoursData.avgMeditationTime) {
                $('[data-meditiation-hours-block] [data-meditation-hours-total-users]').html(data.meditationHoursData.totalUsers);
                $('[data-meditiation-hours-block] [data-meditation-hours-avg-hours]').html(data.meditationHoursData.avgMeditationTime);
            } else {
                $('[data-meditiation-hours-block] [data-meditation-hours-total-users]').html(0);
                $('[data-meditiation-hours-block] [data-meditation-hours-avg-hours]').html(0);
            }
            break;
        case 2:
            // Update top meditation tracks chart data on date range filter
            if (data.change == 'popularMeditationDuration') {
                appUsageCharts.popularMeditationCategories.config.data.labels = data.popularMeditationCategoriesData ? data.popularMeditationCategoriesData.meditationCategory : [];
                appUsageCharts.popularMeditationCategories.config.data.datasets[0].data = data.popularMeditationCategoriesData ? data.popularMeditationCategoriesData.totalViews : [];
                appUsageCharts.popularMeditationCategories.config.data.datasets[0].backgroundColor = data.popularMeditationCategoriesData ? poolColors(data.popularMeditationCategoriesData.meditationCategory.length) : [];
                appUsageCharts.popularMeditationCategories.object.update();
                return;
            }
            // Update top meditation tracks chart data on date range filter
            if (data.change == 'daterangeTopMeditationTracks') {
                appUsageCharts.topMeditationTracks.config.data.labels = data.topMeditationTracksData ? data.topMeditationTracksData.meditationTitle : [];
                appUsageCharts.topMeditationTracks.config.data.datasets[0].data = data.topMeditationTracksData ? data.topMeditationTracksData.totalViews : [];
                appUsageCharts.topMeditationTracks.config.data.datasets[0].backgroundColor = data.topMeditationTracksData ? poolColors(data.topMeditationTracksData.meditationTitle.length) : [];
                appUsageCharts.topMeditationTracks.object.update();
                return;
            }
            // initialize popular meditation categories chart with blank data
            if (typeof appUsageCharts.popularMeditationCategories.object != "object") {
                appUsageCharts.popularMeditationCategories.object = new Chart($('#chartPopularMeditationCategory'), appUsageCharts.popularMeditationCategories.config);
            }
            // Update popular meditation categories chart data
            appUsageCharts.popularMeditationCategories.config.data.labels = data.popularMeditationCategoriesData ? data.popularMeditationCategoriesData.meditationCategory : [];
            appUsageCharts.popularMeditationCategories.config.data.datasets[0].data = data.popularMeditationCategoriesData ? data.popularMeditationCategoriesData.totalViews : [];
            appUsageCharts.popularMeditationCategories.config.data.datasets[0].backgroundColor = data.popularMeditationCategoriesData ? poolColors(data.popularMeditationCategoriesData.meditationCategory.length) : [];
            appUsageCharts.popularMeditationCategories.object.update();
            // initialize top meditation tracks chart with blank data
            if (typeof appUsageCharts.topMeditationTracks.object != "object") {
                appUsageCharts.topMeditationTracks.object = new Chart($('#chartTopTrack'), appUsageCharts.topMeditationTracks.config);
            }
            // Update top meditation tracks chart data
            appUsageCharts.topMeditationTracks.config.data.labels = data.topMeditationTracksData ? data.topMeditationTracksData.meditationTitle : [];
            appUsageCharts.topMeditationTracks.config.data.datasets[0].data = data.topMeditationTracksData ? data.topMeditationTracksData.totalViews : [];
            appUsageCharts.topMeditationTracks.config.data.datasets[0].backgroundColor = data.topMeditationTracksData ? poolColors(data.topMeditationTracksData.meditationTitle.length) : [];
            appUsageCharts.topMeditationTracks.object.update();
            break;
        case 3:
            if (data.change == 'popularWebinarDuration') {
                appUsageCharts.popularWebinarCategories.config.data.labels = data.popularWebinarCategoriesData ? data.popularWebinarCategoriesData.webinarCategory : [];
                appUsageCharts.popularWebinarCategories.config.data.datasets[0].data = data.popularWebinarCategoriesData ? data.popularWebinarCategoriesData.totalViews : [];
                appUsageCharts.popularWebinarCategories.config.data.datasets[0].backgroundColor = data.popularWebinarCategoriesData ? poolColors(data.popularWebinarCategoriesData.webinarCategory.length) : [];
                appUsageCharts.popularWebinarCategories.object.update();
                return;
            }
            // Update top 10 Webinar chart data on date range filter
            if (data.change == 'daterangeTopWebinarTracks') {
                appUsageCharts.topWebinarTracks.config.data.labels = data.topWebinarsData ? data.topWebinarsData.webinarTitle : [];
                appUsageCharts.topWebinarTracks.config.data.datasets[0].data = data.topWebinarsData ? data.topWebinarsData.totalViews : [];
                appUsageCharts.topWebinarTracks.config.data.datasets[0].backgroundColor = data.topWebinarsData ? poolColors(data.topWebinarsData.webinarTitle.length) : [];
                appUsageCharts.topWebinarTracks.object.update();
                return;
            }
            // Recipe views block
            if (data.popularRecipesData) {
                $('[data-recipe-block]').empty();
                data.popularRecipesData.forEach(function(item, key) {
                    $('[data-recipe-block]').append('<tr> <td> <div class="d-flex"> <div class="me-3 table-img table-img-l flex-shrink-0"> <img alt="" src="' + item.logo + '"/> </div> <div> <p class="mb-2"> <strong> ' + item.name + '</strong> </p> <span class="gray-500"> By: ' + item.chef + '</span> </div> </div> </td> <td> <span class="gray-500"> ' + num_format_short(item.totalViews) + '</span> </td> </tr>');
                });
            } else {
                $('[data-recipe-block]').empty();
                $('[data-recipe-block]').append('<h5 class="sx-text mt-5">No Recipes Views Found.!</h5>');
            }
            // initialize popular webinar categories chart with blank data
            if (typeof appUsageCharts.popularWebinarCategories.object != "object") {
                appUsageCharts.popularWebinarCategories.object = new Chart($('#popularWebinarCategories'), appUsageCharts.popularWebinarCategories.config, 1000);
            }
            // Update popular webinar categories chart data
            appUsageCharts.popularWebinarCategories.config.data.labels = data.popularWebinarCategoriesData ? data.popularWebinarCategoriesData.webinarCategory : [];
            appUsageCharts.popularWebinarCategories.config.data.datasets[0].data = data.popularWebinarCategoriesData ? data.popularWebinarCategoriesData.totalViews : [];
            appUsageCharts.popularWebinarCategories.config.data.datasets[0].backgroundColor = data.popularWebinarCategoriesData ? poolColors(data.popularWebinarCategoriesData.webinarCategory.length) : [];
            appUsageCharts.popularWebinarCategories.object.update();
            // initialize top 10 Webinar chart with blank data
            if (typeof appUsageCharts.topWebinarTracks.object != "object") {
                appUsageCharts.topWebinarTracks.object = new Chart($('#chartTopWebinar'), appUsageCharts.topWebinarTracks.config);
            }
            // Update top 10 webinar chart data
            appUsageCharts.topWebinarTracks.config.data.labels = data.topWebinarsData ? data.topWebinarsData.webinarTitle : [];
            appUsageCharts.topWebinarTracks.config.data.datasets[0].data = data.topWebinarsData ? data.topWebinarsData.totalViews : [];
            appUsageCharts.topWebinarTracks.config.data.datasets[0].backgroundColor = data.topWebinarsData ? poolColors(data.topWebinarsData.webinarTitle.length) : [];
            appUsageCharts.topWebinarTracks.object.update();
            break;
        case 4:
            // Update Popular Masterclass Categories chart data on duration filter
            if (data.change == 'popularMasterclassDuration') {
                appUsageCharts.popularMasterclassCategories.config.data.labels = data.popularMasterclassCategoriesData ? data.popularMasterclassCategoriesData.masterclassCategory : [];
                appUsageCharts.popularMasterclassCategories.config.data.datasets[0].data = data.popularMasterclassCategoriesData ? data.popularMasterclassCategoriesData.totalEnrollments : [];
                appUsageCharts.popularMasterclassCategories.config.data.datasets[0].backgroundColor = data.popularMasterclassCategoriesData ? poolColors(data.popularMasterclassCategoriesData.masterclassCategory.length) : [];
                appUsageCharts.popularMasterclassCategories.object.update();
                return;
            }
            // Update Popular Feed Categories chart data on duration filter
            if (data.change == 'popularFeedDuration') {
                appUsageCharts.popularFeedCategories.config.data.labels = data.popularFeedCategoriesData ? data.popularFeedCategoriesData.feedCategory : [];
                appUsageCharts.popularFeedCategories.config.data.datasets[0].data = data.popularFeedCategoriesData ? data.popularFeedCategoriesData.totalViews : [];
                appUsageCharts.popularFeedCategories.config.data.datasets[0].backgroundColor = data.popularFeedCategoriesData ? poolColors(data.popularFeedCategoriesData.feedCategory.length) : [];
                appUsageCharts.popularFeedCategories.object.update();
                return;
            }
            // Update top 10 Masterclass chart data on date range filter
            if (data.change == 'daterangeTopMasterclass') {
                appUsageCharts.topMasterclassTracks.config.data.labels = data.topMasterclassData ? data.topMasterclassData.masterclassTitle : [];
                appUsageCharts.topMasterclassTracks.config.data.datasets[0].data = data.topMasterclassData ? data.topMasterclassData.totalEnrollment : [];
                appUsageCharts.topMasterclassTracks.config.data.datasets[0].backgroundColor = data.topMasterclassData ? poolColors(data.topMasterclassData.masterclassTitle.length) : [];
                appUsageCharts.topMasterclassTracks.object.update();
                return;
            }
            // Update top 10 Masterclass chart data on date range filter
            if (data.change == 'daterangeTopFeeds') {
                appUsageCharts.topFeedsChart.config.data.labels = data.topFeedsData ? data.topFeedsData.FeedsTitle : [];
                appUsageCharts.topFeedsChart.config.data.datasets[0].data = data.topFeedsData ? data.topFeedsData.totalViews : [];
                appUsageCharts.topFeedsChart.config.data.datasets[0].backgroundColor = data.topFeedsData ? poolColors(data.topFeedsData.FeedsTitle.length) : [];
                appUsageCharts.topFeedsChart.object.update();
                return;
            }
            // initialize popular masterclass categories chart with blank data
            if (typeof appUsageCharts.popularMasterclassCategories.object != "object") {
                appUsageCharts.popularMasterclassCategories.object = new Chart($('#popularMasterclassCategories'), appUsageCharts.popularMasterclassCategories.config, 1000);
            }
            // Update popular webinar categories chart data
            appUsageCharts.popularMasterclassCategories.config.data.labels = data.popularMasterclassCategoriesData ? data.popularMasterclassCategoriesData.masterclassCategory : [];
            appUsageCharts.popularMasterclassCategories.config.data.datasets[0].data = data.popularMasterclassCategoriesData ? data.popularMasterclassCategoriesData.totalEnrollments : [];
            appUsageCharts.popularMasterclassCategories.config.data.datasets[0].backgroundColor = data.popularMasterclassCategoriesData ? poolColors(data.popularMasterclassCategoriesData.masterclassCategory.length) : [];
            appUsageCharts.popularMasterclassCategories.object.update();
            // initialize top 10 Masterclass chart with blank data
            if (typeof appUsageCharts.topMasterclassTracks.object != "object") {
                appUsageCharts.topMasterclassTracks.object = new Chart($('#chartTopMasterclass'), appUsageCharts.topMasterclassTracks.config);
            }
            // Update top 10 Masterclass chart data
            appUsageCharts.topMasterclassTracks.config.data.labels = data.topMasterclassData ? data.topMasterclassData.masterclassTitle : [];
            appUsageCharts.topMasterclassTracks.config.data.datasets[0].data = data.topMasterclassData ? data.topMasterclassData.totalEnrollment : [];
            appUsageCharts.topMasterclassTracks.config.data.datasets[0].backgroundColor = data.topMasterclassData ? poolColors(data.topMasterclassData.masterclassTitle.length) : [];
            appUsageCharts.topMasterclassTracks.object.update();
            // // initialize popular feed categories chart with blank data
            if (typeof appUsageCharts.popularFeedCategories.object != "object") {
                appUsageCharts.popularFeedCategories.object = new Chart($('#popularFeedCategories'), appUsageCharts.popularFeedCategories.config, 1000);
            }
            // Update popular feed categories chart data
            appUsageCharts.popularFeedCategories.config.data.labels = data.popularFeedCategoriesData ? data.popularFeedCategoriesData.feedCategory : [];
            appUsageCharts.popularFeedCategories.config.data.datasets[0].data = data.popularFeedCategoriesData ? data.popularFeedCategoriesData.totalViews : [];
            appUsageCharts.popularFeedCategories.config.data.datasets[0].backgroundColor = data.popularFeedCategoriesData ? poolColors(data.popularFeedCategoriesData.feedCategory.length) : [];
            appUsageCharts.popularFeedCategories.object.update();
            // initialize top 10 feed chart with blank data
            if (typeof appUsageCharts.topFeedsChart.object != "object") {
                appUsageCharts.topFeedsChart.object = new Chart($('#FeedTop10Chart'), appUsageCharts.topFeedsChart.config);
            }
            // Update top 10 feed chart data
            appUsageCharts.topFeedsChart.config.data.labels = data.topFeedsData ? data.topFeedsData.FeedsTitle : [];
            appUsageCharts.topFeedsChart.config.data.datasets[0].data = data.topFeedsData ? data.topFeedsData.totalViews : [];
            appUsageCharts.topFeedsChart.config.data.datasets[0].backgroundColor = data.topFeedsData ? poolColors(data.topFeedsData.FeedsTitle.length) : [];
            appUsageCharts.topFeedsChart.object.update();
            break;
        default:
            toastr.error('Something went wrong.!');
            break;
    }
}
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
    appUsageTabAjaxCall(1, options);
});
/*
 * Code for week/month/year filter in popular meditation categories chart
 */
$(document).on('click', '[data-popular-meditation-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('popular-meditation-duration') || null);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDatePopularMeditation = duration;
    options.change = 'popularMeditationDuration';
    appUsageTabAjaxCall(2, options);
});
/*
 * Code for week/month/year filter in popular webinar categories chart
 */
$(document).on('click', '[data-popular-webinar-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('popular-webinar-duration') || null);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    // var fromDateTopWebinar = moment($('#daterangeTopWebinarTracksFrom').datepicker("getDate"));
    // var endDateTopWebinars = moment($('#daterangeTopWebinarTracksFromTo').datepicker("getDate")).endOf('month');
    // var fromDateTopWebinar = fromDateTopWebinar.format('YYYY-MM-DD 00:00:00');
    // var endDateTopWebinars = endDateTopWebinars.format('YYYY-MM-DD 23:59:59');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDatePopularWebinar = duration;
    options.change = 'popularWebinarDuration';
    // options.change = 'daterangeTopWebinarTracks';
    // options.fromDateTopWebinar = fromDateTopWebinar;
    // options.endDateTopWebinars = endDateTopWebinars;
    appUsageTabAjaxCall(3, options);
});
/*
 * Code for week/month/year filter in popular Masterclass categories chart
 */
$(document).on('click', '[data-popular-masterclass-from-duration] li', function(e) {
    var _this = $(this);
    var duration = (_this.data('popular-masterclass-duration') || null);
    var parent = _this.parent();
    $(parent).find('li').removeClass('active');
    _this.addClass('active process');
    // var fromDateTopWebinar = moment($('#daterangeTopWebinarTracksFrom').datepicker("getDate"));
    // var endDateTopWebinars = moment($('#daterangeTopWebinarTracksFromTo').datepicker("getDate")).endOf('month');
    // var fromDateTopWebinar = fromDateTopWebinar.format('YYYY-MM-DD 00:00:00');
    // var endDateTopWebinars = endDateTopWebinars.format('YYYY-MM-DD 23:59:59');
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.fromDatePopularMasterclass = duration;
    options.change = 'popularMasterclassDuration';
    // options.fromDateTopWebinar = fromDateTopWebinar;
    // options.endDateTopWebinars = endDateTopWebinars;
    appUsageTabAjaxCall(4, options);
});
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
    options.change = 'popularFeedDuration';
    appUsageTabAjaxCall(4, options);
});
// /*
//  * Code for all/week/month/year filter in top 10 Masterclass categories chart
//  */
// $(document).on('click', '[data-top-masterclass-from-duration] li', function(e) {
//     var _this = $(this);
//     var duration = (_this.data('top-masterclass-duration') || null);
//     var fromDateTopWebinar = moment($('#daterangeTopWebinarTracksFrom').datepicker("getDate"));
//     var endDateTopWebinars = moment($('#daterangeTopWebinarTracksFromTo').datepicker("getDate")).endOf('month');
//     var fromDateTopWebinar = fromDateTopWebinar.format('YYYY-MM-DD 00:00:00');
//     var endDateTopWebinars = endDateTopWebinars.format('YYYY-MM-DD 23:59:59');
//     var parent = _this.parent();
//     $(parent).find('li').removeClass('active');
//     _this.addClass('active process');
//     if (typeof options == 'undefined') {
//         options = new Object();
//     }
//     options.fromDateTopMasterclass = duration;
//     options.change = 'daterangeTopWebinarTracks';
//     options.fromDateTopWebinar = fromDateTopWebinar;
//     options.endDateTopWebinars = endDateTopWebinars;
//     appUsageTabAjaxCall(4, options);
// });
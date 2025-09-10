var tabs = ['move', 'nourish', 'inspire', 'challenge'],
    currActiveTab = ((location.hash) ? location.hash.substr(1) : "move"),
    currActiveTab = (($.inArray(currActiveTab, tabs) !== -1) ? currActiveTab : "move"),
    charts = {
        popularExercisesChart: {
            object: '',
            config: {
                type: "horizontalBar",
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        fill: true,
                        backgroundColor: "rgb(0, 165, 209, 0.2)",
                        borderWidth: 0,
                        // borderColor: "rgb(0, 165, 209)"
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (` ${tooltipItem.xLabel.toFixed(2)}%`);
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                beginAtZero: true
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
        exerciseHoursChart: {
            object: '',
            config: {
                type: "line",
                gridLines: {
                    display: true,
                    drawBorder: true,
                    drawOnChartArea: false
                },
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        fill: false,
                        borderColor: "rgb(0, 165, 209)",
                        lineTension: 0.5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (' ' + tooltipItem.yLabel.toFixed(2) + ' Hour' + ((tooltipItem.yLabel > 1) ? 's' : ''));
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                beginAtZero: true,
                                autoSkip: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                callback: function(label, index, labels) {
                                    return (label + ' hr' + ((parseFloat(label) > 1) ? 's' : ''));
                                }
                            }
                        }]
                    }
                }
            }
        },
        caloriesBurnedChart: {
            object: '',
            config: {
                type: "bar",
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        fill: false,
                        backgroundColor: "#00a7d2",
                        // borderColor: "rgb(0, 165, 209)",
                        borderWidth: 0
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
                            barThickness: 6,
                            maxBarThickness: 8
                        }],
                        yAxes: [{
                            gridLines: {
                                display: true
                            },
                            ticks: {
                                callback: function(label, index, labels) {
                                    return num_format_short(label) + ' (KCal)';
                                }
                            }
                        }]
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (' ' + num_format_short(tooltipItem.yLabel) + ' (KCal)');
                            }
                        }
                    }
                },
            },
        },
        usersBMIChart: {
            object: '',
            config: {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ["#00a7d2", "#45D25D", "#FFA304", "#E80707"],
                        hoverBackgroundColor: ["#00a7d2", "#45D25D", "#FFA304", "#E80707"]
                    }]
                },
                options: {
                    cutoutPercentage: 60,
                    legend: {
                        display: false,
                    },
                    tooltips: {
                        callbacks: {
                            title: function(tooltipItems, data) {
                                return '';
                            },
                            label: function(tooltipItem, data) {
                                var datasetLabel = '';
                                return label = data.labels[tooltipItem.index];
                                return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            },
                        },
                    },
                },
            },
        },
        courseDetailsChart: {
            object: '',
            config: {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: ["#FFB35E", "#00a7d2", "#45d25d"],
                        hoverBackgroundColor: ["#FFB35E", "#00a7d2", "#45d25d"]
                    }]
                },
                options: {
                    cutoutPercentage: 60,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        callbacks: {
                            title: function(tooltipItems, data) {
                                return '';
                            },
                            label: function(tooltipItem, data) {
                                var datasetLabel = '';
                                return label = data.labels[tooltipItem.index];
                                return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            }
                        }
                    }
                }
            }
        },
        meditationHoursChart: {
            object: '',
            config: {
                type: "line",
                gridLines: {
                    display: true,
                    drawBorder: true,
                    drawOnChartArea: false,
                },
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        fill: false,
                        borderColor: "rgb(0, 165, 209)",
                        lineTension: 0.5
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (' ' + tooltipItem.yLabel.toFixed(2) + ' Hour' + ((tooltipItem.yLabel > 1) ? 's' : ''));
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                beginAtZero: true,
                                autoSkip: false
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                callback: function(label, index, labels) {
                                    return (label + ' hr' + ((parseFloat(label) > 1) ? 's' : ''));
                                }
                            }
                        }],
                    },
                },
            },
        },
        courseCompletedChart: {
            object: '',
            config: {
                type: "horizontalBar",
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        fill: false,
                        backgroundColor: "rgb(0, 165, 209, 0.2)",
                        // borderColor: "rgb(0, 165, 209)",
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (' ' + tooltipItem.xLabel.toFixed(2) + '%');
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                beginAtZero: true
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                display: false
                            },
                            barThickness: 8,
                            maxBarThickness: 8
                        }]
                    },
                }
            }
        },
    },
    DTable = {
        moveTeamActivity: {
            object: '',
            selector: '#tblMoveTeamActivity',
            type: 'moveTeamActivity',
            searching: true,
            language: {
                searchPlaceholder: "Team name",
            },
            columns: [{
                data: 'team_name',
                name: 'teams.name',
                searchable: true,
                sortable: true
            }, {
                data: 'no_of_members',
                name: 'no_of_members',
                searchable: false,
                sortable: false
            }, {
                data: 'total_steps',
                name: 'total_steps',
                searchable: false,
                sortable: false
            }, {
                data: 'last_half_hour',
                name: 'last_half_hour',
                searchable: false,
                sortable: false
            }, {
                data: 'last_hour',
                name: 'last_hour',
                searchable: false,
                sortable: false
            }, {
                data: 'last_month',
                name: 'last_month',
                searchable: false,
                sortable: false
            }]
        },
        nourishTeamActivity: {
            object: '',
            selector: '#tblNourishTeamActivity',
            type: 'nourishTeamActivity',
            searching: true,
            language: {
                searchPlaceholder: "Team name",
            },
            columns: [{
                data: 'team_name',
                name: 'teams.name',
                searchable: true,
                sortable: true
            }, {
                data: 'no_of_members',
                name: 'no_of_members',
                searchable: false,
                sortable: false
            }, {
                data: 'total_burned_calories',
                name: 'total_burned_calories',
                searchable: false,
                sortable: false
            }]
        },
        inspireTeamActivity: {
            object: '',
            selector: '#tblInspireTeamActivity',
            type: 'inspireTeamActivity',
            searching: true,
            language: {
                searchPlaceholder: "Team name",
            },
            columns: [{
                data: 'team_name',
                name: 'teams.name',
                searchable: true,
                sortable: true
            }, {
                data: 'no_of_members',
                name: 'no_of_members',
                searchable: false,
                sortable: false
            }, {
                data: 'total_meditation_count',
                name: 'total_meditation_count',
                searchable: false,
                sortable: false
            }]
        },
    },
    carousel = {
        mostActiveIndividualUsers: {
            object: '',
            selector: $('#mostActiveIndividualUsers'),
            config: {
                loop: false,
                margin: 16,
                nav: false,
                autoplay: true,
                autoplayTimeout: 3000,
                autoplayHoverPause: true,
                animateOut: 'fadeOut',
                responsive: {
                    0: {
                        items: 1
                    },
                    600: {
                        items: 3
                    },
                    1000: {
                        items: 3
                    },
                    1400: {
                        items: 4
                    }
                }
            }
        }
    };
$(document).ready(function() {
    $('.select2').select2({
        width: '100%'
    });
    $('#company_id').select2({
        containerCssClass: comapany_visibility
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: 60000
    });
    $('.knob-chart').knob({
        min: 0,
        max: 100,
        format: function(value) {
            return value + '%';
        }
    });
    loadDashboardOverviewData();
    loadActiveTabData();
    // --------------- Dashboard Tabs ---------------
    $('#dashboardTabs').easyResponsiveTabs({
        type: 'default',
        width: 'auto',
        fit: true,
        tabidentify: 'dashboard-tabs',
        activate: function(event) {
            currActiveTab = $(this).attr('id');
            loadActiveTabData();
        }
    });
    $(document).on('change.select2', '#company_id', function(e) {
        $('#department_id').empty();
        $('#age').val('');
        $('#location_id').empty();
        $('#department_id, #age, #location_id').select2('destroy').select2();
        loadDashboardOverviewData();
        loadActiveTabData();
        loadDepartments();
    });
    $(document).on('change.select2', '#department_id, #age', function(e) {
        loadDashboardOverviewData();
        loadActiveTabData();
    });
    $(document).on('click', '[data-loadchart] li', function(e) {
        var _this = $(this),
            extraData = undefined;
        if (!_this.hasClass('active') && !_this.hasClass('process')) {
            var parent = _this.parent(),
                chartType = ($(parent).data('charttype') || "");
            if (chartType != "") {
                $(parent).find('li').removeClass('active');
                _this.addClass('active process');
                loadChart(chartType, _this, extraData);
            }
        }
    });
    $(document).on("click", '[data-userbmigenderfilter] li', function(e) {
        var _this = $(this),
            gender = (_this.data('filtervalue') || null),
            parent = _this.parent();
        $(parent).find('li').removeClass('active');
        _this.addClass('active');
        loadChart("usersBMI", $('[data-charttype="usersBMI"] li.active'), {
            gender: gender
        });
    });
    $(document).on('change.select2', '#challenge_type', function(e) {
        loadChallenges();
        if ($(this).val() == "inter_company") {
            $('#no_of_members').hide();
            $('#no_of_companies').show();
        } else {
            $('#no_of_members').show();
            $('#no_of_companies').hide();
        }
    });
    $(document).on('change.select2', '#challenge_name', function(e) {
        loadChallengeData();
    });
});

function num_format_short(n) {
    if (n < 1e3) return n;
    if (n >= 1e3 && n < 1e6) return +(n / 1e3).toFixed(1) + "K";
    if (n >= 1e6 && n < 1e9) return +(n / 1e6).toFixed(1) + "M";
    if (n >= 1e9 && n < 1e12) return +(n / 1e9).toFixed(1) + "B";
    if (n >= 1e12) return +(n / 1e12).toFixed(1) + "T";
    // return Math.abs(num) > 999 ? Math.sign(num)*((Math.abs(num)/1000).toFixed(1)) + 'k' : Math.sign(num)*Math.abs(num)
}

function loadDashboardOverviewData() {
    var pAge = $('#age').val();
    pAge = ((pAge) ? pAge.split('_') : pAge);
    $.ajax({
        url: urls.getData,
        type: 'POST',
        dataType: 'json',
        crossDomain: true,
        cache: false,
        data: {
            comapnyId: $('#company_id').val(),
            departmentId: $('#department_id').val(),
            age1: ((pAge) ? pAge[0] : null),
            age2: ((pAge) ? pAge[1] : null)
        },
    }).done(function(data) {
        //removed existing up and down classes from all 3 overview blocks
        $('[data-moveblock] [data-movestate], [data-nourishblock] [data-nourishstate], [data-inspireblock] [data-inspirestate]').removeClass('up down');
        // move block
        $('[data-moveblock] [data-week1date]').html(data.move.week1.date);
        $('[data-moveblock] [data-week1steps]').html(data.move.week1.steps);
        $('[data-moveblock] [data-week2date]').html(data.move.week2.date);
        $('[data-moveblock] [data-week2steps]').html(data.move.week2.steps);
        $('[data-moveblock] [data-movepercetage]').html(data.move.overview.percent);
        $('[data-moveblock] [data-movestate]').addClass(data.move.overview.arrow);
        // nourish blovk
        $('[data-nourishblock] [data-week1date]').html(data.nourish.week1.date);
        $('[data-nourishblock] [data-week1calories]').html(data.nourish.week1.calories);
        $('[data-nourishblock] [data-week2date]').html(data.nourish.week2.date);
        $('[data-nourishblock] [data-week2calories]').html(data.nourish.week2.calories);
        $('[data-nourishblock] [data-nourishpercetage]').html(data.nourish.overview.percent);
        $('[data-nourishblock] [data-nourishstate]').addClass(data.nourish.overview.arrow);
        // inspire block
        $('[data-inspireblock] [data-week1date]').html(data.inspire.week1.date);
        $('[data-inspireblock] [data-week1meditations]').html(data.inspire.week1.meditations);
        $('[data-inspireblock] [data-week2date]').html(data.inspire.week2.date);
        $('[data-inspireblock] [data-week2meditations]').html(data.inspire.week2.meditations);
        $('[data-inspireblock] [data-inspirepercetage]').html(data.inspire.overview.percent);
        $('[data-inspireblock] [data-inspirestate]').addClass(data.inspire.overview.arrow);
    }).fail(function(error) {
        console.log("error");
    }).always(function() {});
}

function loadActiveTabData() {
    console.log("loadActiveTabData");
    var pAge = $('#age').val();
    pAge = ((pAge) ? pAge.split('_') : pAge);
    if (currActiveTab == "move" || currActiveTab == "challenge") {
        if (currActiveTab == "challenge") {
            $('#challenge_type').val('').trigger('change');
        }
        $.ajax({
            url: urls.getTabData,
            type: 'POST',
            dataType: 'json',
            data: {
                tab: currActiveTab,
                comapnyId: $('#company_id').val(),
                departmentId: $('#department_id').val(),
                age1: ((pAge) ? pAge[0] : null),
                age2: ((pAge) ? pAge[1] : null)
            }
        }).done(function(data) {
            if (currActiveTab == "move") {
                // Users chart
                $('[data-movetab] [data-userschart] [data-totalusers]').html(data.usersChartData.totalUsers);
                $('[data-movetab] [data-userschart] [data-activeusers]').html(data.usersChartData.activeUsers);
                $('[data-movetab] [data-userschart] #numberofuserschart').val(data.usersChartData.percent).trigger('blur');
                // Teams chart
                $('[data-movetab] [data-teamschart] [data-totalteams]').html(data.teamsChartData.totalTeams);
                $('[data-movetab] [data-teamschart] [data-activeteams]').html(data.teamsChartData.activeTeams);
            } else if (currActiveTab == "challenge") {
                // Teams chart
                $('[data-ongoingChallengesChart] [data-totalongoingchallenges]').html(data.ongoingChallengesChart.totalongoingchallenges);
                $('[data-ongoingChallengesChart] [data-totalchallenges]').html(data.ongoingChallengesChart.totalchallenges);
                $('[data-ongoingChallengesChart] [data-totalcompletedchallenges]').html(data.ongoingChallengesChart.totalcompletedchallenges);
                $('[data-ongoingChallengesChart] #ongoingChallengesChart').val(data.ongoingChallengesChart.ongoingchallengespercent).trigger('blur');
            }
        }).fail(function(error) {
            alert(error.responseText || `Failed to ${currActiveTab} tab data`);
            console.log((error.responseText || `Failed to ${currActiveTab} tab data Check below for more about error`));
            console.log(error);
        }).always(function() {});
    }
    loadAllChartsOfActiveTab();
    loadAllDataTablesOfActiveTab();
}

function loadAllChartsOfActiveTab() {
    switch (currActiveTab) {
        case 'move':
            // intilize popular exercises chart with blank data
            if (typeof charts.popularExercisesChart.object != "object") {
                charts.popularExercisesChart.object = new Chart($('#popularExercisesChart'), charts.popularExercisesChart.config, 1000);
            }
            // intilize exercise hours chart with blank data
            if (typeof charts.exerciseHoursChart.object != "object") {
                charts.exerciseHoursChart.object = new Chart($('#exerciseHoursChart'), charts.exerciseHoursChart.config, 1000);
            }
            loadChart("popularExercises", $('[data-charttype="popularExercises"] li.active'));
            loadChart("exerciseHours", $('[data-charttype="exerciseHours"] li.active'));
            break;
        case 'nourish':
            // intilize calories burned chart with blank data
            if (typeof charts.caloriesBurnedChart.object != "object") {
                charts.caloriesBurnedChart.object = new Chart($('#caloriesBurnedChart'), charts.caloriesBurnedChart.config, 1000);
            }
            // intilize users BMI chart with blank data
            if (typeof charts.usersBMIChart.object != "object") {
                charts.usersBMIChart.object = new Chart($("#usersBMIChart"), charts.usersBMIChart.config, 1000);
            }
            loadChart("caloriesBurned", $('[data-charttype="caloriesBurned"] li.active'));
            var extraData = {
                gender: ($('[data-userbmigenderfilter] li.active').data('filtervalue') || null)
            };
            loadChart("usersBMI", undefined, extraData);
            break;
        case 'inspire':
            // intilize courses details chart with blank data
            if (typeof charts.courseDetailsChart.object != "object") {
                charts.courseDetailsChart.object = new Chart($('#courseDetailsChart'), charts.courseDetailsChart.config, 1000);
            }
            loadChart("courseDetails", undefined);
            // intilize users BMI chart with blank data
            if (typeof charts.meditationHoursChart.object != "object") {
                charts.meditationHoursChart.object = new Chart($("#meditationHoursChart"), charts.meditationHoursChart.config, 1000);
            }
            loadChart("meditationHours", $('[data-charttype="meditationHours"] li.active'));
            // intilize users BMI chart with blank data
            if (typeof charts.courseCompletedChart.object != "object") {
                charts.courseCompletedChart.object = new Chart($("#courseCompletedChart"), charts.courseCompletedChart.config, 1000);
            }
            loadChart("courseCompleted", undefined);
            break;
        case 'challenge':
            break;
    }
}

function loadAllDataTablesOfActiveTab() {
    switch (currActiveTab) {
        case 'move':
            loadDataTable(DTable.moveTeamActivity);
            break;
        case 'nourish':
            loadDataTable(DTable.nourishTeamActivity);
            break;
        case 'inspire':
            loadDataTable(DTable.inspireTeamActivity);
            break;
        case 'challenge':
            loadSimpleTables("mostBadgesEarned");
            loadSimpleTables("mostActiveIndividualUsers");
            break;
    }
}

function loadChart(chartType, durationLink, extraData) {
    var pAge = $('#age').val(),
        durationThreshold = ($(durationLink).data('duration') || 7),
        data = {};
    pAge = ((pAge) ? pAge.split('_') : pAge);
    data = {
        chartType: chartType,
        durationThreshold: durationThreshold,
        comapnyId: $('#company_id').val(),
        departmentId: $('#department_id').val(),
        age1: ((pAge) ? pAge[0] : null),
        age2: ((pAge) ? pAge[1] : null)
    };
    if (extraData != undefined) {
        data = $.extend(data, extraData);
    }
    $.ajax({
        url: urls.chartData,
        type: 'POST',
        dataType: 'json',
        data: data
    }).done(function(data) {
        switch (chartType) {
            case 'popularExercises':
                // Update popular exercises chart data
                charts.popularExercisesChart.config.data.labels = data.labels;
                charts.popularExercisesChart.config.data.datasets[0].data = data.data;
                charts.popularExercisesChart.config.data.datasets[0].backgroundColor = poolColors(data.data.length);
                charts.popularExercisesChart.object.update();
                break;
            case 'exerciseHours':
                // Update exercises hours chart data
                charts.exerciseHoursChart.config.data.labels = data.labels;
                charts.exerciseHoursChart.config.data.datasets[0].data = data.data;
                charts.exerciseHoursChart.object.update();
                $('[data-exercisehourschartdata] [data-totaluser]').html(data.totalUsers);
                $('[data-exercisehourschartdata] [data-totalhours]').html(data.avgHours);
                break;
            case 'caloriesBurned':
                // Update calories burned chart data
                charts.caloriesBurnedChart.config.data.labels = data.labels;
                charts.caloriesBurnedChart.config.data.datasets[0].data = data.data;
                charts.caloriesBurnedChart.object.update();
                $('[data-caloriesburnedchartdata] [data-totaluser]').html(data.totalUsers);
                $('[data-caloriesburnedchartdata] [data-avgburnedcalories]').html(data.avgBurnedCalories);
                break;
            case 'usersBMI':
                // Update users BMI chart data
                charts.usersBMIChart.config.data.labels = data.labels;
                charts.usersBMIChart.config.data.datasets[0].data = data.data;
                charts.usersBMIChart.object.update();
                // Avg
                $('[data-usersbmichartdata] [data-totaluser]').html(data.totalUsers);
                $('[data-usersbmichartdata] [data-avgweight]').html(data.avgWeight);
                // Legends
                $('[data-usersbmichartlegendsdata] [data-underweight]').html(`${data.data[0]}%`);
                $('[data-usersbmichartlegendsdata] [data-normal]').html(`${data.data[1]}%`);
                $('[data-usersbmichartlegendsdata] [data-overweight]').html(`${data.data[2]}%`);
                $('[data-usersbmichartlegendsdata] [data-obese]').html(`${data.data[3]}%`);
                break;
            case 'courseDetails':
                charts.courseDetailsChart.config.data.labels = data.labels;
                charts.courseDetailsChart.config.data.datasets[0].data = data.data;
                charts.courseDetailsChart.object.update();
                $('#joinedCourses').empty().append(data.courseData[0]);
                $('#startedCourses').empty().append(data.courseData[1]);
                $('#completedCourses').empty().append(data.courseData[2]);
                break;
            case 'meditationHours':
                charts.meditationHoursChart.config.data.labels = data.labels;
                charts.meditationHoursChart.config.data.datasets[0].data = data.data;
                charts.meditationHoursChart.object.update();
                $('[data-meditationhourschartdata] [data-totalusers]').html(data.totalUsers);
                $('[data-meditationhourschartdata] [data-avgmeditationhours]').html(data.avgMeditationTime);
                break;
            case 'courseCompleted':
                charts.courseCompletedChart.config.data.labels = data.labels;
                charts.courseCompletedChart.config.data.datasets[0].data = data.data;
                charts.courseCompletedChart.config.data.datasets[0].backgroundColor = poolColors(data.data.length);
                charts.courseCompletedChart.object.update();
                break;
        }
    }).fail(function(error) {
        alert(error.responseText || 'Failed to load chart');
        console.log(error.responseText || 'Failed to load chart Check below for more about error');
        console.log(error);
    }).always(function() {
        if (durationLink != undefined) {
            $(durationLink).removeClass('process');
        }
    });
}

function loadDataTable(config) {
    var pAge = $('#age').val();
    pAge = ((pAge) ? pAge.split('_') : pAge);
    if ($.fn.DataTable.isDataTable(config.selector)) {
        config.object.clear().destroy();
    }
    config.object = $(config.selector).DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: urls.DataTableData,
            data: {
                dtType: config.type,
                comapnyId: $('#company_id').val(),
                departmentId: $('#department_id').val(),
                age1: ((pAge) ? pAge[0] : null),
                age2: ((pAge) ? pAge[1] : null)
            }
        },
        columns: config.columns,
        paging: (config.paging || false),
        pageLength: (config.pagination || dataTableConf.pagination),
        lengthChange: false,
        searching: (config.searching || false),
        ordering: (config.ordering || true),
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        language: (config.language || {}),
        stateSave: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
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
// function to generate random color in hex form
function dynamicColors() {
    var r = Math.floor(Math.random() * 255);
    var g = Math.floor(Math.random() * 255);
    var b = Math.floor(Math.random() * 255);
    return "rgba(" + r + "," + g + "," + b + ", 0.9)";
}

function poolColors(a) {
    var pool = [];
    for (i = 0; i < a; i++) {
        pool.push(dynamicColors());
    }
    return pool;
}

function loadSimpleTables(tableType) {
    var pAge = $('#age').val();
    pAge = ((pAge) ? pAge.split('_') : pAge);
    $.ajax({
        url: urls.TableData,
        type: 'POST',
        dataType: 'html',
        data: {
            tableType: tableType,
            comapnyId: $('#company_id').val(),
            departmentId: $('#department_id').val(),
            age1: ((pAge) ? pAge[0] : null),
            age2: ((pAge) ? pAge[1] : null)
        }
    }).done(function(data) {
        if (tableType == "mostBadgesEarned") {
            $('#mostBadgesEarned tbody').html(data);
        } else if (tableType == "mostActiveIndividualUsers") {
            if (typeof carousel.mostActiveIndividualUsers.object == "object") {
                $(carousel.mostActiveIndividualUsers.selector).owlCarousel('destroy');
            }
            $('#mostActiveIndividualUsers').html(data);
            carousel.mostActiveIndividualUsers.object = $(carousel.mostActiveIndividualUsers.selector).owlCarousel(carousel.mostActiveIndividualUsers.config).on('mousewheel', '.owl-stage', function(e) {
                if (e.deltaY > 0) {
                    carousel.mostActiveIndividualUsers.object.trigger('next.owl');
                } else {
                    carousel.mostActiveIndividualUsers.object.trigger('prev.owl');
                }
            });
        }
    }).fail(function(error) {
        console.log('Failed to load simple table Check below for more about error');
        console.log(error);
    });
}

function loadChallenges() {
    var type = $("#challenge_type").val(),
        reqData = {
            type: type,
            comapnyId: $('#company_id').val()
        },
        challenge_name_options = "";
    if (type != "" && type != null) {
        $.post(urls.getTypeWiseChallenges, reqData, function(data, textStatus, xhr) {
            if (xhr.status == 200) {
                $.each(data, function(index, challenge) {
                    challenge_name_options += `<option value="${index}">${challenge}</option>`;
                });
            }
            $('#challenge_name').empty().append(challenge_name_options).val('').trigger('change');
        });
    } else {
        $('#challenge_name').empty().val('').trigger('change');
    }
}

function loadChallengeData() {
    var challengeId = $("#challenge_name").val(),
        challengeType = $('#challenge_type').val();
    if (challengeId != "" && challengeId != null) {
        $.post(urls.getChallengeData, {
            challengeType: challengeType,
            challengeId: challengeId
        }, function(data, textStatus, xhr) {
            if (xhr.status == 200) {
                $('[data-challengesdetails] [data-percentage]').html(data.percentage);
                $('[data-challengesdetails] [data-activemembers]').html(data.active_members);
                $('[data-challengesdetails] [data-noofmembers]').html(data.no_of_members);
                $('[data-challengesdetails] [data-noofcompanies]').html(data.no_of_companies);
            }
        });
    } else {
        $('[data-challengesdetails] [data-percentage]').html('0%');
        $('[data-challengesdetails] [data-activemembers]').html('0');
        $('[data-challengesdetails] [data-noofmembers]').html('0');
        $('[data-challengesdetails] [data-noofcompanies]').html('0');
    }
}
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
            type: "doughnut",
            data: {
                datasets: [{
                    data: [],
                    backgroundColor: ["#E21067", "#FFD600", "#50C9B5", "#5261AC"],
                    label: ["Low", "Moderate", "High", "Very High"],
                    information: ["(0-1 Hours)", "(1-4 Hours)", "(4-10 Hours)", "(10+ Hours)"]
                }],
                labels: ["Low", "Moderate", "High", "Very High"]
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
                legendCallback: function(chart) {
                    var text = [];
                    text.push('<ul class="' + chart.id + '-legend chart-legend" style="list-style:none">');
                    for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                        text.push('<li><div class="legend-color" style="border-color:' + chart.data.datasets[0].backgroundColor[i] + '" ></div>');
                        if (chart.data.datasets[0].label[i]) {
                            text.push("<span class='legend-text'>" + chart.data.datasets[0].label[i] + "</span><span class='legend-info'>" + chart.data.datasets[0].information[i] + "</span>");
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
                    bodyFontSize: 14,
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
    mostPopulerExercises: {
        object: '',
        config: {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    label: "Percentage",
                    data: [],
                    fill: true,
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
                        barThickness: 20,
                        maxBarThickness: 30,
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: true
                        },
                        ticks: {
                            beginAtZero: true,
                            max: 100
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Percentage",
                        }
                    }]
                },
            }
        }
    },
    mostPopulerExercisesManual: {
        object: '',
        config: {
            type: "bar",
            data: {
                labels: [],
                datasets: [{
                    label: "Percentage",
                    data: [],
                    fill: true,
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
                        barThickness: 20,
                        maxBarThickness: 30,
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: true
                        },
                        ticks: {
                            beginAtZero: true,
                            max: 100
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Percentage",
                        }
                    }]
                },
            }
        }
    },
    /*populerExerciseRangeDoughnut: {
        object: '',
        config: {
            type: "doughnut",
            data: {
                datasets: [{
                    data: [],
                    backgroundColor: ["#FF0000", "#00FF00", "#0000FF"],
                    label: ["Least Popular", "Moderate", "Most Popular"],
                    information: ["(0-10 Sessions)", "(10-30 Sessions)", "(30+ Sessions)"]
                }],
                labels: ["Least Popular", "Moderate", "Most Popular"]
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
                legendCallback: function(chart) {
                    console.log(chart.data.datasets[0].data.length);
                    var text = [];
                    text.push('<ul class="' + chart.id + '-legend chart-legend" style="list-style:none">');
                    for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                        if (chart.data.datasets[0].backgroundColor[i] != undefined) {
                            text.push('<li><div class="legend-color" style="border-color:' + chart.data.datasets[0].backgroundColor[i] + '" ></div>');
                            if (chart.data.datasets[0].label[i]) {
                                text.push("<span class='legend-text'>" + chart.data.datasets[0].label[i] + "</span><span class='legend-info'>" + chart.data.datasets[0].information[i] + "</span>");
                            }
                            text.push("</li>");
                        }
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
                    bodyFontSize: 14,
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
    populerExerciseRangeDoughnutManual: {
        object: '',
        config: {
            type: "doughnut",
            data: {
                datasets: [{
                    data: [],
                    backgroundColor: ["#FF0000", "#00FF00", "#0000FF"],
                    label: ["Least Popular", "Moderate", "Most Popular"],
                    information: ["(0-10 Sessions)", "(10-30 Sessions)", "(30+ Sessions)"]
                }],
                labels: ["Least Popular", "Moderate", "Most Popular"]
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
                legendCallback: function(chart) {
                    var text = [];
                    text.push('<ul class="' + chart.id + '-legend chart-legend" style="list-style:none">');
                    for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                        if (chart.data.datasets[0].backgroundColor[i] != undefined) {
                            text.push('<li><div class="legend-color" style="border-color:' + chart.data.datasets[0].backgroundColor[i] + '" ></div>');
                            if (chart.data.datasets[0].label[i]) {
                                text.push("<span class='legend-text'>" + chart.data.datasets[0].label[i] + "</span><span class='legend-info'>" + chart.data.datasets[0].information[i] + "</span>");
                            }
                            text.push("</li>");
                        }
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
                    bodyFontSize: 14,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex]
                            return (`${data.labels[tooltipItem.index]}: ${dataset.data[tooltipItem.index]}%`);
                        }
                    }
                }
            }
        }
    },*/
    stepsRangeDoughnut: {
        object: '',
        config: {
            type: "doughnut",
            data: {
                datasets: [{
                    data: [],
                    backgroundColor: ["#E21067", "#FFD600", "#50C9B5"],
                    label: ["Low", "Moderate", "High"],
                    information: ["(0-8k Steps)", "(8k-12k Steps)", "(12k+ Steps)"]
                }],
                labels: ["Low", "Moderate", "High"]
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
                legendCallback: function(chart) {
                    var text = [];
                    text.push('<ul class="' + chart.id + '-legend chart-legend" style="list-style:none">');
                    for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                        text.push('<li><div class="legend-color" style="border-color:' + chart.data.datasets[0].backgroundColor[i] + '" ></div>');
                        if (chart.data.datasets[0].label[i]) {
                            text.push("<span class='legend-text'>" + chart.data.datasets[0].label[i] + "</span><span class='legend-info'>" + chart.data.datasets[0].information[i] + "</span>");
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
                    bodyFontSize: 14,
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
            type: "doughnut",
            data: {
                datasets: [{
                    data: [40, 10, 20, 20],
                    backgroundColor: ["#5261AC", "#50C9B5", "#FFD600", "#E21067"],
                    label: ["Underweight", "Normal", "Overweight", "Obese"],
                    information: ["Less than 18.5", "18.5 to 25", "25 to 30", " More than 30"]
                }],
                labels: ["Underweight", "Normal", "Overweight", "Obese"]
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
                legendCallback: function(chart) {
                    var text = [];
                    text.push('<ul class="' + chart.id + '-legend chart-legend" style="list-style:none">');
                    for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                        text.push('<li><div class="legend-color" style="border-color:' + chart.data.datasets[0].backgroundColor[i] + '" ></div>');
                        if (chart.data.datasets[0].label[i]) {
                            text.push("<span class='legend-text'>" + chart.data.datasets[0].label[i] + "</span><span class='legend-info'>" + chart.data.datasets[0].information[i] + "</span>");
                        }
                        text.push("</li>");
                    }
                    text.push("</ul>");
                    return text.join("");
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
                        barThickness: 20,
                        maxBarThickness: 30,
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Types of mood",
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display: true
                        },
                        ticks: {
                            beginAtZero: true,
                            max: 100
                        },
                        scaleLabel: {
                            display: true,
                            fontSize: 14,
                            labelString: "Percentage",
                        }
                    }]
                },
            }
        }
    }
};
var syncInfoChart = {
    object: '',
    config: {
        type: "doughnut",
        data: {
            datasets: [{
                data: [],
                backgroundColor: ["#FF6C2E", "#EBECF0"],
                label: ["Synced", "Not Synced"],
            }],
            labels: ["Synced", "Not Synced"]
        },
        options: {
            // tooltips: {
            //     enabled: false
            // },
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 90,
            rotation: 180,
            elements: {
                arc: {
                    borderWidth: 0,
                }
            },
            legendCallback: function(chart) {
                var text = [];
                text.push('<ul class="' + chart.id + '-legend chart-legend" style="list-style:none">');
                for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                    text.push('<li><div class="legend-color" style="border-color:' + chart.data.datasets[0].backgroundColor[i] + '" ></div>');
                    if (chart.data.datasets[0].label[i]) {
                        text.push("<span class='legend-text'>" + chart.data.datasets[0].label[i] + "</span><span class='legend-info d-block'><strong>" + chart.data.datasets[0].data[i] + " % </strong></span>");
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
};
/*
 * Physical tab common AJAX call
 */
function physicalTabAjaxCall(tier, options = null) {
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

    if(roleSlug!= 'super_admin' && roleSlug!= 'wellbeing_specialist' && roleSlug!= 'wellbeing_team_lead'){
        companyIds = companyIds.match(pattern) ? companyIds : null ;
    }else{
        companyIds = $.isNumeric(companyIds) ? companyIds : null;
    }

    var departmentId = $('#department_id').val();
    var locationId = $('#location_id').val();
    var age1 = ((age) ? age[0] : null);
    var age2 = ((age) ? age[1] : null);
    $.ajax({
        url: urls.behaviour,
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
            break;
        case 2:
            // Update exercise range chart data on date range filter
            if (data.change == 'daterangeExerciseRanges') {
                physicalCharts.exerciseRangeDoughnut.config.data.datasets[0].data = data.exercisesData || [];
                physicalCharts.exerciseRangeDoughnut.object.update();
                document.getElementById("exerciseRange-legend").innerHTML = physicalCharts.exerciseRangeDoughnut.object.generateLegend();
                return;
            }
            // Update steps range chart data on date range filter
            if (data.change == 'daterangeStepRanges') {
                physicalCharts.stepsRangeDoughnut.config.data.datasets[0].data = data.stepsData || [];
                physicalCharts.stepsRangeDoughnut.object.update();
                document.getElementById("stepRange-legend").innerHTML = physicalCharts.stepsRangeDoughnut.object.generateLegend();
                return;
            }
            // Update most popular exercise tracker range chart data on date range filter
            if (data.change == 'daterangeMostPopularExTracker') {
                physicalCharts.mostPopulerExercises.config.data.labels = data.popularTrackerExercise ? data.popularTrackerExercise.title : [];
                physicalCharts.mostPopulerExercises.config.data.datasets[0].data = data.popularTrackerExercise ? data.popularTrackerExercise.percent : [];
                physicalCharts.mostPopulerExercises.object.update();
                return;
            }
            // Update most popular exercise manual range chart data on date range filter
            if (data.change == 'daterangeMostPopularExManual') {
                physicalCharts.mostPopulerExercisesManual.config.data.labels = data.popularManualExercise ? data.popularManualExercise.title : [];
                physicalCharts.mostPopulerExercisesManual.config.data.datasets[0].data = data.popularManualExercise ? data.popularManualExercise.percent : [];
                physicalCharts.mostPopulerExercisesManual.object.update();
                return;
            }
            // intilize exercise range chart with blank data
            if (typeof physicalCharts.exerciseRangeDoughnut.object != "object") {
                physicalCharts.exerciseRangeDoughnut.object = new Chart($('#doughnutExerciseRanges'), physicalCharts.exerciseRangeDoughnut.config);
            }
            // Update exercise range chart data
            physicalCharts.exerciseRangeDoughnut.config.data.datasets[0].data = data.exercisesData ? data.exercisesData : [];
            physicalCharts.exerciseRangeDoughnut.object.update();
            document.getElementById("exerciseRange-legend").innerHTML = physicalCharts.exerciseRangeDoughnut.object.generateLegend();
            // intilize steps range chart with blank data
            if (typeof physicalCharts.stepsRangeDoughnut.object != "object") {
                physicalCharts.stepsRangeDoughnut.object = new Chart($('#doughnutStepsRanges'), physicalCharts.stepsRangeDoughnut.config);
            }
            // Update steps range chart data
            physicalCharts.stepsRangeDoughnut.config.data.datasets[0].data = data.stepsData ? data.stepsData : [];
            physicalCharts.stepsRangeDoughnut.object.update();
            document.getElementById("stepRange-legend").innerHTML = physicalCharts.stepsRangeDoughnut.object.generateLegend();
            // intilize exercise range chart with blank data
            /*if (typeof physicalCharts.populerExerciseRangeDoughnut.object != "object") {
                physicalCharts.populerExerciseRangeDoughnut.object = new Chart($('#chartExerciseTracker'), physicalCharts.populerExerciseRangeDoughnut.config);
            }
            // Update exercise range chart data
            physicalCharts.populerExerciseRangeDoughnut.config.data.datasets[0].data = data.exercisesData ? data.exercisesData : [];
            physicalCharts.populerExerciseRangeDoughnut.object.update();
            document.getElementById("exerciseTracker-legend").innerHTML = physicalCharts.populerExerciseRangeDoughnut.object.generateLegend();
            // intilize steps range chart with blank data
            if (typeof physicalCharts.populerExerciseRangeDoughnutManual.object != "object") {
                physicalCharts.populerExerciseRangeDoughnutManual.object = new Chart($('#chartExerciseManual'), physicalCharts.populerExerciseRangeDoughnutManual.config);
            }
            // Update steps range chart data
            physicalCharts.populerExerciseRangeDoughnutManual.config.data.datasets[0].data = data.exercisesData ? data.exercisesData : [];
            physicalCharts.populerExerciseRangeDoughnutManual.object.update();
            document.getElementById("exerciseManual-legend").innerHTML = physicalCharts.populerExerciseRangeDoughnutManual.object.generateLegend();*/

            // intilize exercise range chart with blank data
            if (typeof physicalCharts.mostPopulerExercises.object != "object") {
                physicalCharts.mostPopulerExercises.object = new Chart($('#chartExerciseTracker'), physicalCharts.mostPopulerExercises.config);
            }
            // Update exercise range chart data
            physicalCharts.mostPopulerExercises.config.data.labels = data.popularTrackerExercise ? data.popularTrackerExercise.title : [];
            physicalCharts.mostPopulerExercises.config.data.datasets[0].data = data.popularTrackerExercise ? data.popularTrackerExercise.percent : [];
            physicalCharts.mostPopulerExercises.object.update();

            // intilize exercise range chart with blank data
            if (typeof physicalCharts.mostPopulerExercisesManual.object != "object") {
                physicalCharts.mostPopulerExercisesManual.object = new Chart($('#chartExerciseManual'), physicalCharts.mostPopulerExercisesManual.config);
            }
            // Update exercise range chart data
            physicalCharts.mostPopulerExercisesManual.config.data.labels = data.popularManualExercise ? data.popularManualExercise.title : [];
            physicalCharts.mostPopulerExercisesManual.config.data.datasets[0].data = data.popularManualExercise ? data.popularManualExercise.percent : [];
            physicalCharts.mostPopulerExercisesManual.object.update();
            
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
            if (typeof syncInfoChart.object != "object") {
                syncInfoChart.object = new Chart($('#syncInfo'), syncInfoChart.config, 1000);
            }
            // Update calories period chart data
            if (data.syncDetails) {
                $('[data-sync-percent]').html(data.syncDetails.syncPercent + ' %');
                syncInfoChart.config.data.datasets[0].data = data.syncDetails ? [data.syncDetails.syncPercent, data.syncDetails.notSyncPercent] : [];
                syncInfoChart.object.update();
                document.getElementById("syncInfo-legend").innerHTML = syncInfoChart.object.generateLegend();
            }
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
            // if (data.syncDetails) {
            // $('[data-sync-details] [data-sync-percent]').html(data.syncDetails.syncPercent + ' %');
            // $('[data-sync-details] [data-notsync-percent]').html(data.syncDetails.notSyncPercent + ' %');
            // $('[data-sync-details] [data-knob]').empty();
            // $('[data-sync-details] [data-knob]').append('<input class="knob-chart knob-chart-font-18" data-fgcolor="#ffb35e" data-height="180" data-linecap="round" data-readonly="true" data-thickness=".1" data-width="180" readonly="readonly" type="text" value="' + data.syncDetails.syncPercent + ' %"/>');
            // knobInit();
            // }
            break;
        case 4:
            // Update BMI chart data on date range filter
            if (data.change == 'genderFilter') {
                physicalCharts.bmiDoughnut.config.data.datasets[0].data = data.bmiData ? data.bmiData : [];
                physicalCharts.bmiDoughnut.object.update();
                document.getElementById("bmiAll-legend").innerHTML = physicalCharts.bmiDoughnut.object.generateLegend();
                if (data.totalUsers && data.avgWeight) {
                    $('[data-bmi-block] [data-bmi-users]').html(num_format_short(data.totalUsers));
                    $('[data-bmi-block] [data-bmi-weight]').html(data.avgWeight + ' KG');
                } else {
                    $('[data-bmi-block] [data-bmi-users]').html(0);
                    $('[data-bmi-block] [data-bmi-weight]').html(0);
                }
                return;
            }
            if (data.change == 'moodsAnalysisDuration') {
                psychologicalCharts.moodAnalysis.config.data.labels = data.moodAnalysis ? data.moodAnalysis.title : [];
                psychologicalCharts.moodAnalysis.config.data.datasets[0].data = data.moodAnalysis ? data.moodAnalysis.percent : [];
                psychologicalCharts.moodAnalysis.object.update();
                return;
            }
            if (data.change == 'daterangeSuperstars') {
                // Active team block
                if (data.activeTeamData) {
                    $('[data-active-team] .sx-list').empty();
                    data.activeTeamData.forEach(function(item, key) {
                        if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                            $('[data-active-team] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> <small> Company: ' + item.company + '</small> </div> <div class="sx-list-hr text-nowrap"> ' + item.averageHours + ' hrs </div> </li>');
                        } else {
                            $('[data-active-team] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> </div> <div class="sx-list-hr text-nowrap"> ' + item.averageHours + ' hrs </div> </li>');
                        }
                    });
                } else {
                    $('[data-active-team] .sx-list').html('<h5 class="sx-text text-center mt-5">Get Moving.!</h5>');
                }
                // Active individual block
                if (data.activeIndividualData) {
                    $('[data-active-individual] .sx-list').empty();
                    data.activeIndividualData.forEach(function(item, key) {
                        if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                            $('[data-active-individual] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> <small> Company: ' + item.company + '</small> </div> <div class="sx-list-hr text-nowrap"> ' + item.totalHours + ' hrs </div> </li>');
                        } else {
                            $('[data-active-individual] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> </div> <div class="sx-list-hr text-nowrap"> ' + item.totalHours + ' hrs </div> </li>');
                        }
                    });
                } else {
                    $('[data-active-individual] .sx-list').html('<h5 class="sx-text text-center mt-5">Get Moving.!</h5>');
                }
                // Badges Earned block
                if (data.badgesEarnedData) {
                    $('[data-badges-earned] .sx-list').empty();
                    data.badgesEarnedData.forEach(function(item, key) {
                        if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                            $('[data-badges-earned] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> <small> Company: ' + item.company + '</small> </div> <div class="sx-list-hr text-nowrap"> ' + num_format_short(item.mostBadges) + ' </div> </li>');
                        } else {
                            $('[data-badges-earned] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> </div> <div class="sx-list-hr text-nowrap"> ' + num_format_short(item.mostBadges) + ' </div> </li>');
                        }
                    });
                } else {
                    $('[data-badges-earned] .sx-list').html('<h5 class="sx-text text-center mt-5">Get Moving.!</h5>');
                }
                return;
            }
            // intilize BMI chart with blank data
            if (typeof physicalCharts.bmiDoughnut.object != "object") {
                physicalCharts.bmiDoughnut.object = new Chart($('#doughnutBMI'), physicalCharts.bmiDoughnut.config);
            }
            // Update BMI chart data
            physicalCharts.bmiDoughnut.config.data.datasets[0].data = data.bmiData ? data.bmiData : [];
            physicalCharts.bmiDoughnut.object.update();
            document.getElementById("bmiAll-legend").innerHTML = physicalCharts.bmiDoughnut.object.generateLegend();
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
                $('[data-active-team] .sx-list').empty();
                data.activeTeamData.forEach(function(item, key) {
                    if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                        $('[data-active-team] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> <small> Company: ' + item.company + '</small> </div> <div class="sx-list-hr text-nowrap"> ' + item.averageHours + ' hrs </div> </li>');
                    } else {
                        $('[data-active-team] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> </div> <div class="sx-list-hr text-nowrap"> ' + item.averageHours + ' hrs </div> </li>');
                    }
                });
            } else {
                $('[data-active-team] .sx-list').html('<h5 class="sx-text text-center mt-5">Get Moving.!</h5>');
            }
            // Active individual block
            if (data.activeIndividualData) {
                $('[data-active-individual] .sx-list').empty();
                data.activeIndividualData.forEach(function(item, key) {
                    if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                        $('[data-active-individual] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> <small> Company: ' + item.company + '</small> </div> <div class="sx-list-hr text-nowrap"> ' + item.totalHours + ' hrs </div> </li>');
                    } else {
                        $('[data-active-individual] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> </div> <div class="sx-list-hr text-nowrap"> ' + item.totalHours + ' hrs </div> </li>');
                    }
                });
            } else {
                $('[data-active-individual] .sx-list').html('<h5 class="sx-text text-center mt-5">Get Moving.!</h5>');
            }
            // Badges Earned block
            if (data.badgesEarnedData) {
                $('[data-badges-earned] .sx-list').empty();
                data.badgesEarnedData.forEach(function(item, key) {
                    if (data.role == 'zevo' || (data.role == 'reseller' && data.company_parent_id == null)) {
                        $('[data-badges-earned] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> <small> Company: ' + item.company + '</small> </div> <div class="sx-list-hr text-nowrap"> ' + num_format_short(item.mostBadges) + ' </div> </li>');
                    } else {
                        $('[data-badges-earned] .sx-list').append('<li> <div class="sx-user-img flex-shrink-0"> <img alt="icon" class="" src="' + item.logo + '"/> </div> <div class="sx-list-name-area"> <h6 class="sx-list-name"> ' + item.name + '</h6> </div> <div class="sx-list-hr text-nowrap"> ' + num_format_short(item.mostBadges) + ' </div> </li>');
                    }
                });
            } else {
                $('[data-badges-earned] .sx-list').html('<h5 class="sx-text text-center mt-5">Get Moving.!</h5>');
            }
            break;
            /*case 5:
            // Update exercise range chart data on date range filter
            if (data.change == 'daterangeMostPopularExManual') {
                console.log(data.change);
                physicalCharts.exerciseRangeDoughnut.config.data.datasets[0].data = data.exercisesData || [];
                physicalCharts.exerciseRangeDoughnut.object.update();
                document.getElementById("exerciseManual-legend").innerHTML = physicalCharts.exerciseRangeDoughnut.object.generateLegend();
                return;
            }
            // Update steps range chart data on date range filter
            if (data.change == 'daterangeMostPopularExTracker') {

                physicalCharts.stepsRangeDoughnut.config.data.datasets[0].data = data.stepsData || [];
                physicalCharts.stepsRangeDoughnut.object.update();
                document.getElementById("exerciseTracker-legend").innerHTML = physicalCharts.stepsRangeDoughnut.object.generateLegend();
                return;
            }
            // intilize exercise range chart with blank data
            if (typeof physicalCharts.exerciseRangeDoughnut.object != "object") {
                physicalCharts.exerciseRangeDoughnut.object = new Chart($('#chartExerciseTracker'), physicalCharts.exerciseRangeDoughnut.config);
            }
            // Update exercise range chart data
            physicalCharts.exerciseRangeDoughnut.config.data.datasets[0].data = data.exercisesData ? data.exercisesData : [];
            physicalCharts.exerciseRangeDoughnut.object.update();
            // intilize steps range chart with blank data
            if (typeof physicalCharts.stepsRangeDoughnut.object != "object") {
                physicalCharts.stepsRangeDoughnut.object = new Chart($('#chartExerciseManual'), physicalCharts.stepsRangeDoughnut.config);
            }
            // Update steps range chart data
            physicalCharts.stepsRangeDoughnut.config.data.datasets[0].data = data.stepsData ? data.stepsData : [];
            physicalCharts.stepsRangeDoughnut.object.update();
            document.getElementById("exerciseManual-legend").innerHTML = physicalCharts.stepsRangeDoughnut.object.generateLegend();
           */
            // break;
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
$(document).on("click", '[data-gender-filter] li a', function(e) {
    var _this = $(this);
    var gender = (_this.data('gender') || null);
    var parent = _this.parent().parent();
    $(parent).find('li a').removeClass('active');
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
    options.change = 'moodsAnalysisDuration';
    physicalTabAjaxCall(4, options);
});
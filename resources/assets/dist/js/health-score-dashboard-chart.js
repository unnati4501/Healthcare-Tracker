(function ($) {

    // --------------- Doughnut Chart Area ---------------
    Chart.pluginService.register({
        beforeDraw: function (chart) {
            if (chart.config.options.elements.center) {
                //Get ctx from string
                var ctx = chart.chart.ctx;

                //Get options from the center object in options
                var centerConfig = chart.config.options.elements.center;
                var fontStyle = centerConfig.fontStyle || 'Arial';
                var txt = centerConfig.text;
                var color = centerConfig.color || '#000';
                var sidePadding = centerConfig.sidePadding || 20;
                var sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2)
                //Start with a base font of 30px
                ctx.font = "30px " + fontStyle;

                //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                var stringWidth = ctx.measureText(txt).width;
                var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

                // Find out how much the font can grow in width.
                var widthRatio = elementWidth / stringWidth;
                var newFontSize = Math.floor(30 * widthRatio);
                var elementHeight = (chart.innerRadius * 2);

                // Pick a new font size so it will not be larger than the height of label.
                var fontSizeToUse = Math.min(newFontSize, elementHeight);

                //Set font settings to draw it correctly.
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
                ctx.font = fontSizeToUse + "px " + fontStyle;
                ctx.fillStyle = color;

                //Draw text in center
                ctx.fillText(txt, centerX, centerY);
            }
        }
    });


    var config = {
        type: 'doughnut',
        data: {
            labels: [
                "Underweight", "Normal"
            ],
            datasets: [{
                data: [25, 25],
                backgroundColor: [
                    "#00a7d2",
                    "#45D25D",
                ],
                hoverBackgroundColor: [
                    "#00a7d2",
                    "#45D25D",
                ]
            }]
        },
        options: {
            cutoutPercentage: 60,
            elements: {
                center: {
                    text: '76%',
                    color: '#000',
                    fontStyle: 'Arial',
                    sidePadding: 200
                }
            },
            legend: {
                display: false
            },
            plugins: {
                labels: {
                    render: 'percentage',
                    fontColor: '#ffffff'
                }
            }
        }
    };


    var ctx = document.getElementById("doughnutHealthScoreSurvey").getContext("2d");
    var myChart = new Chart(ctx, config);

    var physicalScoreConfig = {
        type: 'doughnut',
        data: {
            labels: [
                "Underweight", "Normal", "Overweight", "Obese"
            ],
            datasets: [{
                data: [25, 25, 25, 25],
                backgroundColor: [
                    "#00a7d2",
                    "#45D25D",
                    "#FFA304",
                    "#E80707"
                ],
                hoverBackgroundColor: [
                    "#00a7d2",
                    "#45D25D",
                    "#FFA304",
                    "#E80707"
                ]
            }]
        },
        options: {
            cutoutPercentage: 60,
            elements: {
                center: {
                    text: '76%',
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
                        render: 'label',
                        fontColor: '#000',
                        position: 'outside',
                        arc: true,
                    },
                    {
                        fontColor: '#ffffff',
                        render: 'percentage'
                    }
                ]
            }
        }
    };

    var physicalScore = document.getElementById("doughnutPhysicalScore").getContext("2d");
    var myChart = new Chart(physicalScore, physicalScoreConfig);


    var psychologicalScoreConfig = {
        type: 'doughnut',
        data: {
            labels: [
                "Achievement", "Meaning", "Engagement", "Relationships", "Positive affect"
            ],
            datasets: [{
                data: [15, 20, 15, 10, 40],
                backgroundColor: [
                    "#00a7d2",
                    "#45D25D",
                    "#FFA304",
                    "#E80707",
                    "#FFB35E"
                ],
                hoverBackgroundColor: [
                    "#00a7d2",
                    "#45D25D",
                    "#FFA304",
                    "#E80707",
                    "#FFB35E"
                ]
            }]
        },
        options: {
            cutoutPercentage: 60,
            elements: {
                center: {
                    text: '44%',
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
                        render: 'label',
                        fontColor: '#000',
                        position: 'outside',
                        arc: true,
                    },
                    {
                        fontColor: '#ffffff',
                        render: 'percentage'
                    }
                ]
            }
        }
    };

    var psychologicalScore = document.getElementById("doughnutPsychologicalScore").getContext("2d");
    var myChart = new Chart(psychologicalScore, psychologicalScoreConfig);


})(jQuery);
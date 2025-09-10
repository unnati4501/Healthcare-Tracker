(function ($) {

    // // --------------- Chart Area ---------------
    // Chart.elements.Rectangle.prototype.draw = function () {
    //     var ctx = this._chart.ctx;
    //     var vm = this._view;
    //     var left, right, top, bottom, signX, signY, borderSkipped, radius;
    //     var borderWidth = vm.borderWidth;
    //     var cornerRadius = 30;
    //     if (!vm.horizontal) {
    //         // bar
    //         left = vm.x - vm.width / 2;
    //         right = vm.x + vm.width / 2;
    //         top = vm.y;
    //         bottom = vm.base;
    //         signX = 1;
    //         signY = bottom > top ? 1 : -1;
    //         borderSkipped = vm.borderSkipped || 'bottom';
    //     } else {
    //         // horizontal bar
    //         left = 0;
    //         right = vm.x;
    //         top = vm.y - vm.height / 2;
    //         bottom = vm.y + vm.height / 2;
    //         signX = right > left ? 1 : -1;
    //         signY = 1;
    //         borderSkipped = vm.borderSkipped || 'left';
    //     }

    //     // Canvas doesn't allow us to stroke inside the width so we can
    //     // adjust the sizes to fit if we're setting a stroke on the line
    //     if (borderWidth) {
    //         // borderWidth shold be less than bar width and bar height.
    //         var barSize = Math.min(Math.abs(left - right), Math.abs(top - bottom));
    //         borderWidth = borderWidth > barSize ? barSize : borderWidth;
    //         var halfStroke = borderWidth / 2;
    //         // Adjust borderWidth when bar top position is near vm.base(zero).
    //         var borderLeft = left + (borderSkipped !== 'left' ? halfStroke * signX : 0);
    //         var borderRight = right + (borderSkipped !== 'right' ? -halfStroke * signX : 0);
    //         var borderTop = top + (borderSkipped !== 'top' ? halfStroke * signY : 0);
    //         var borderBottom = bottom + (borderSkipped !== 'bottom' ? -halfStroke * signY : 0);
    //         // not become a vertical line?
    //         if (borderLeft !== borderRight) {
    //             top = borderTop;
    //             bottom = borderBottom;
    //         }
    //         // not become a horizontal line?
    //         if (borderTop !== borderBottom) {
    //             left = borderLeft;
    //             right = borderRight;
    //         }
    //     }

    //     ctx.beginPath();
    //     ctx.fillStyle = vm.backgroundColor;
    //     ctx.strokeStyle = vm.borderColor;
    //     ctx.lineWidth = borderWidth;

    //     // Corner points, from bottom-left to bottom-right clockwise
    //     // | 1 2 |
    //     // | 0 3 |
    //     var corners = [
    //         [left, bottom],
    //         [left, top],
    //         [right, top],
    //         [right, bottom]
    //     ];

    //     // Find first (starting) corner with fallback to 'bottom'
    //     var borders = ['bottom', 'left', 'top', 'right'];
    //     var startCorner = borders.indexOf(borderSkipped, 0);
    //     if (startCorner === -1) {
    //         startCorner = 0;
    //     }

    //     function cornerAt(index) {
    //         return corners[(startCorner + index) % 4];
    //     }

    //     // Draw rectangle from 'startCorner'
    //     var corner = cornerAt(0);
    //     ctx.moveTo(corner[0], corner[1]);

    //     for (var i = 1; i < 4; i++) {
    //         corner = cornerAt(i);
    //         nextCornerId = i + 1;
    //         if (nextCornerId == 4) {
    //             nextCornerId = 0
    //         }

    //         nextCorner = cornerAt(nextCornerId);

    //         width = corners[2][0] - corners[1][0];
    //         height = corners[0][1] - corners[1][1];
    //         x = corners[1][0];
    //         y = corners[1][1];

    //         var radius = cornerRadius;

    //         // Fix radius being too large
    //         if (radius > height / 2) {
    //             radius = height / 2;
    //         }
    //         if (radius > width / 2) {
    //             radius = width / 2;
    //         }

    //         ctx.moveTo(x + radius, y);
    //         ctx.lineTo(x + width - radius, y);
    //         ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
    //         ctx.lineTo(x + width, y + height - radius);
    //         ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    //         ctx.lineTo(x + radius, y + height);
    //         ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
    //         ctx.lineTo(x, y + radius);
    //         ctx.quadraticCurveTo(x, y, x + radius, y);

    //     }

    //     ctx.fill();
    //     if (borderWidth) {
    //         ctx.stroke();
    //     }
    // };



    // // --------------- Chart Area chartStepsPeriod ---------------
    // var ctx = document.getElementById('chartStepsPeriod');
    // var myBarChart = new Chart(ctx, {
    //     type: "bar",
    //     data: {
    //         labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    //         datasets: [{
    //             label: "Calories",
    //             data: [34, 30, 100, 81, 56, 55, 40],
    //             fill: false,
    //             backgroundColor: "rgb(0, 165, 209, 0.2)",
    //             borderColor: "rgb(0, 165, 209)",
    //             borderWidth: 1
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //         scales: {
    //             xAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 ticks: {
    //                     beginAtZero: true
    //                 },
    //                 barThickness: 6,
    //                 maxBarThickness: 8
    //             }],
    //             yAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 }
    //             }]
    //         }
    //     }
    // }, 1000);

    // // --------------- Chart Area chartCaloriesPeriods ---------------
    // var ctx = document.getElementById('chartCaloriesPeriods');
    // var myBarChart = new Chart(ctx, {
    //     type: "bar",
    //     data: {
    //         labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    //         datasets: [{
    //             label: "Calories",
    //             data: [34, 30, 100, 81, 56, 55, 40],
    //             fill: false,
    //             backgroundColor: "rgb(0, 165, 209, 0.2)",
    //             borderColor: "rgb(0, 165, 209)",
    //             borderWidth: 1
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //         scales: {
    //             xAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 ticks: {
    //                     beginAtZero: true
    //                 },
    //                 barThickness: 6,
    //                 maxBarThickness: 8
    //             }],
    //             yAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 }
    //             }]
    //         }
    //     }
    // }, 1000);
    // // --------------- ./ Chart Area chartCaloriesPeriods ---------------
    // // --------------- Chart Area popularExercises ---------------
    // var dataPopularExercises = [34, 30, 70, 81, 56, 55, 40, 50, 10, 67];
    // var ctx = document.getElementById('popularExercises');
    // var myBarChart = new Chart(ctx, {
    //     type: "horizontalBar",
    //     data: {
    //         labels: ["Weight", "Lifting", "Cross Fit", "Running", "Ball Sport", "Walking", "Swimming", "Cardio", "Yoga",
    //             "Cycling"
    //         ],
    //         datasets: [{
    //             label: "Popular Exercises",
    //             data: dataPopularExercises,
    //             fill: false,
    //             backgroundColor: poolColors(dataPopularExercises.length),
    //             borderColor: "#9e9e9e",
    //             borderWidth: 0,
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //         scales: {
    //             xAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 ticks: {
    //                     beginAtZero: true
    //                 }
    //             }],
    //             yAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 barThickness: 8,
    //                 maxBarThickness: 8
    //             }]
    //         }
    //     }
    // }, 1000);

    // function dynamicColors() {
    //     var r = Math.floor(Math.random() * 255);
    //     var g = Math.floor(Math.random() * 255);
    //     var b = Math.floor(Math.random() * 255);
    //     return "rgba(" + r + "," + g + "," + b + ", 0.9)";
    // }

    // function poolColors(a) {
    //     var pool = [];
    //     for (i = 0; i < a; i++) {
    //         pool.push(dynamicColors());
    //     }
    //     return pool;
    // }
    // // --------------- Chart Area popularExercises ---------------
    // // --------------- Chart Area ExerciseRanges ---------------
    // var dataExerciseRanges = [34, 30, 70, 81, 56, 55, 40, 50, 10, 67];
    // var ctx = document.getElementById('chartExerciseRanges');
    // var myBarChart = new Chart(ctx, {
    //     type: "horizontalBar",
    //     data: {
    //         labels: ["Weight", "Lifting", "Cross Fit", "Running", "Ball Sport", "Walking", "Swimming", "Cardio", "Yoga",
    //             "Cycling"
    //         ],
    //         datasets: [{
    //             label: "Popular Exercises",
    //             data: dataExerciseRanges,
    //             fill: false,
    //             backgroundColor: poolColors(dataExerciseRanges.length),
    //             borderColor: "#9e9e9e",
    //             borderWidth: 0,
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //         scales: {
    //             xAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 ticks: {
    //                     beginAtZero: true
    //                 }
    //             }],
    //             yAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 barThickness: 8,
    //                 maxBarThickness: 8
    //             }]
    //         }
    //     }
    // }, 1000);

    // function dynamicColors() {
    //     var r = Math.floor(Math.random() * 255);
    //     var g = Math.floor(Math.random() * 255);
    //     var b = Math.floor(Math.random() * 255);
    //     return "rgba(" + r + "," + g + "," + b + ", 0.9)";
    // }

    // function poolColors(a) {
    //     var pool = [];
    //     for (i = 0; i < a; i++) {
    //         pool.push(dynamicColors());
    //     }
    //     return pool;
    // }
    // // --------------- Chart Area ExerciseRanges ---------------
    // // --------------- Doughnut Chart Area -------------------------
    // Chart.pluginService.register({
    //     beforeDraw: function (chart) {
    //         if (chart.config.options.elements.center) {
    //             //Get ctx from string
    //             var ctx = chart.chart.ctx;
    //             //Get options from the center object in options
    //             var centerConfig = chart.config.options.elements.center;
    //             var fontStyle = centerConfig.fontStyle || 'Arial';
    //             var txt = centerConfig.text;
    //             var color = centerConfig.color || '#000';
    //             var sidePadding = centerConfig.sidePadding || 20;
    //             var sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2)
    //             //Start with a base font of 30px
    //             ctx.font = "30px " + fontStyle;
    //             //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
    //             var stringWidth = ctx.measureText(txt).width;
    //             var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;
    //             // Find out how much the font can grow in width.
    //             var widthRatio = elementWidth / stringWidth;
    //             var newFontSize = Math.floor(30 * widthRatio);
    //             var elementHeight = (chart.innerRadius * 2);
    //             // Pick a new font size so it will not be larger than the height of label.
    //             var fontSizeToUse = Math.min(newFontSize, elementHeight);
    //             //Set font settings to draw it correctly.
    //             ctx.textAlign = 'center';
    //             ctx.textBaseline = 'middle';
    //             var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
    //             var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
    //             ctx.font = fontSizeToUse + "px " + fontStyle;
    //             ctx.fillStyle = color;
    //             //Draw text in center
    //             ctx.fillText(txt, centerX, centerY);
    //         }
    //     }
    // });
    // var config = {
    //     type: 'doughnut',
    //     data: {
    //         labels: [
    //             "Low", "Moderate", "Hight", "Very Hight"
    //         ],
    //         datasets: [{
    //             data: [25, 25, 25, 25],
    //             backgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D",
    //                 "#00a7d2"
    //             ],
    //             hoverBackgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D",
    //                 "#00a7d2"
    //             ]
    //         }]
    //     },
    //     options: {
    //         cutoutPercentage: 60,
    //         // elements: {
    //         //     center: {
    //         //         text: '25%',
    //         //         color: '#000',
    //         //         fontStyle: 'Arial',
    //         //         sidePadding: 200
    //         //     }
    //         // },
    //         legend: {
    //             display: false
    //         },
    //     }
    // };
    // var ctx = document.getElementById("doughnutExerciseRanges").getContext("2d");
    // var myChart = new Chart(ctx, config);
    // // --------------- Doughnut Chart Area -------------------------
    // // --------------- Doughnut Chart Area -------------------------
    // var config = {
    //     type: 'doughnut',
    //     data: {
    //         labels: [
    //             "Low", "Moderate", "Hight", "Very Hight"
    //         ],
    //         datasets: [{
    //             data: [25, 25, 25, 25],
    //             backgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D",
    //                 "#00a7d2"
    //             ],
    //             hoverBackgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D",
    //                 "#00a7d2"
    //             ]
    //         }]
    //     },
    //     options: {
    //         cutoutPercentage: 60,
    //         // elements: {
    //         //     center: {
    //         //         text: '25%',
    //         //         color: '#000',
    //         //         fontStyle: 'Arial',
    //         //         sidePadding: 200
    //         //     }
    //         // },
    //         legend: {
    //             display: false
    //         },
    //     }
    // };
    // var ctx = document.getElementById("doughnutStepsRanges").getContext("2d");
    // var myChart = new Chart(ctx, config);
    // // --------------- Doughnut Chart Area -------------------------
    // // --------------- Doughnut Chart Area -------------------------
    // var config = {
    //     type: 'doughnut',
    //     data: {
    //         labels: [
    //             "Low", "Moderate", "Hight",
    //         ],
    //         datasets: [{
    //             data: [25, 25, 25, 25],
    //             backgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D"
    //             ],
    //             hoverBackgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D"
    //             ]
    //         }]
    //     },
    //     options: {
    //         cutoutPercentage: 80,
    //         // elements: {
    //         //     center: {
    //         //         text: '25%',
    //         //         color: '#000',
    //         //         fontStyle: 'Arial',
    //         //         sidePadding: 200
    //         //     }
    //         // },
    //         legend: {
    //             display: false
    //         },
    //     }
    // };
    // var ctx = document.getElementById("doughnutPhysicalScore").getContext("2d");
    // var myChart = new Chart(ctx, config);
    // // --------------- Doughnut Chart Area -------------------------
    // // --------------- Doughnut Chart Area -------------------------
    // var config = {
    //     type: 'doughnut',
    //     data: {
    //         labels: [
    //             "Low", "Moderate", "Hight", "Very Hight"
    //         ],
    //         datasets: [{
    //             data: [25, 25, 25, 25],
    //             backgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D",
    //                 "#00a7d2"
    //             ],
    //             hoverBackgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D",
    //                 "#00a7d2"
    //             ]
    //         }]
    //     },
    //     options: {
    //         cutoutPercentage: 60,
    //         // elements: {
    //         //     center: {
    //         //         text: '25%',
    //         //         color: '#000',
    //         //         fontStyle: 'Arial',
    //         //         sidePadding: 200
    //         //     }
    //         // },
    //         legend: {
    //             display: false
    //         },
    //     }
    // };
    // var ctx = document.getElementById("doughnutNumberOfUsers").getContext("2d");
    // var myChart = new Chart(ctx, config);
    // // --------------- Doughnut Chart Area -------------------------

    // // --------------- Meditation Hours Chart Area ---------------
    // var ctx = document.getElementById('chartMeditationHours');
    // var myBarChart = new Chart(ctx, {
    //     type: "bar",
    //     data: {
    //         labels: ["1-Sep", "2-Sep", "3-Sep", "4-Sep", "5-Sep", "6-Sep", "7-Sep", "8-Sep", "9-Sep", "10-Sep", "11-Sep", "12-Sep", "13-Sep", "14-Sep", "15-Sep"],
    //         datasets: [{
    //             label: "Calories",
    //             data: [34, 30, 100, 81, 56, 55, 40, 100, 81, 56, 55, 40, 55, 0, 40],
    //             fill: false,
    //             backgroundColor: "rgb(0, 165, 209, 0.2)",
    //             borderColor: "rgb(0, 165, 209)",
    //             borderWidth: 1
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //         scales: {
    //             xAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 ticks: {
    //                     beginAtZero: true
    //                 },
    //                 barThickness: 6,
    //                 maxBarThickness: 8
    //             }],
    //             yAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 }
    //             }]
    //         }
    //     }
    // }, 1000);
    // // --------------- Meditation Hours Chart Area ---------------
    // // --------------- Chart Area chartPopularMeditationCategory ---------------
    // var dataExerciseRanges = [34, 30, 70, 81, 56, 55, 40, 50, 10, 67];
    // var ctx = document.getElementById('chartPopularMeditationCategory');
    // var myBarChart = new Chart(ctx, {
    //     type: "horizontalBar",
    //     data: {
    //         labels: ["Weight", "Lifting", "Cross Fit", "Running", "Ball Sport", "Walking", "Swimming", "Cardio", "Yoga",
    //             "Cycling"
    //         ],
    //         datasets: [{
    //             label: "Popular Exercises",
    //             data: dataExerciseRanges,
    //             fill: false,
    //             backgroundColor: poolColors(dataExerciseRanges.length),
    //             borderColor: "#9e9e9e",
    //             borderWidth: 0,
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //         scales: {
    //             xAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 ticks: {
    //                     beginAtZero: true
    //                 }
    //             }],
    //             yAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 barThickness: 8,
    //                 maxBarThickness: 8
    //             }]
    //         }
    //     }
    // }, 1000);

    // function dynamicColors() {
    //     var r = Math.floor(Math.random() * 255);
    //     var g = Math.floor(Math.random() * 255);
    //     var b = Math.floor(Math.random() * 255);
    //     return "rgba(" + r + "," + g + "," + b + ", 0.9)";
    // }

    // function poolColors(a) {
    //     var pool = [];
    //     for (i = 0; i < a; i++) {
    //         pool.push(dynamicColors());
    //     }
    //     return pool;
    // }
    // // --------------- Chart Area chartPopularMeditationCategory ---------------
    // // --------------- Chart Area chartTopTrack ---------------
    // var dataExerciseRanges = [34, 30, 70, 81, 56, 55, 40, 50, 10, 67];
    // var ctx = document.getElementById('chartTopTrack');
    // var myBarChart = new Chart(ctx, {
    //     type: "horizontalBar",
    //     data: {
    //         labels: ["Weight", "Lifting", "Cross Fit", "Running", "Ball Sport", "Walking", "Swimming", "Cardio", "Yoga",
    //             "Cycling"
    //         ],
    //         datasets: [{
    //             label: "Popular Exercises",
    //             data: dataExerciseRanges,
    //             fill: false,
    //             backgroundColor: poolColors(dataExerciseRanges.length),
    //             borderColor: "#9e9e9e",
    //             borderWidth: 0,
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //         scales: {
    //             xAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 ticks: {
    //                     beginAtZero: true
    //                 }
    //             }],
    //             yAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 barThickness: 8,
    //                 maxBarThickness: 8
    //             }]
    //         }
    //     }
    // }, 1000);

    // function dynamicColors() {
    //     var r = Math.floor(Math.random() * 255);
    //     var g = Math.floor(Math.random() * 255);
    //     var b = Math.floor(Math.random() * 255);
    //     return "rgba(" + r + "," + g + "," + b + ", 0.9)";
    // }

    // function poolColors(a) {
    //     var pool = [];
    //     for (i = 0; i < a; i++) {
    //         pool.push(dynamicColors());
    //     }
    //     return pool;
    // }
    // // --------------- Chart Area chartTopTrack ---------------
    // // --------------- Moods analysis Chart Area ---------------
    // var ctx = document.getElementById('chartMoodsAnalysis');
    // var myBarChart = new Chart(ctx, {
    //     type: "bar",
    //     data: {
    //         labels: ["Happy", "Anger", "Loved", "Surprised", "One QA", "Three QA Length"],
    //         datasets: [{
    //             label: "Calories",
    //             data: [34, 30, 100, 81, 56, 55, 40],
    //             fill: false,
    //             backgroundColor: "rgb(0, 165, 209, 0.2)",
    //             borderColor: "rgb(0, 165, 209)",
    //             borderWidth: 1
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //         scales: {
    //             xAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 },
    //                 ticks: {
    //                     beginAtZero: true
    //                 },
    //                 barThickness: 6,
    //                 maxBarThickness: 8
    //             }],
    //             yAxes: [{
    //                 gridLines: {
    //                     display: false
    //                 }
    //             }]
    //         }
    //     }
    // }, 1000);
    // // --------------- Doughnut Chart Area -------------------------
    // var config = {
    //     type: 'doughnut',
    //     data: {
    //         labels: [
    //             "Low", "Moderate", "Hight",
    //         ],
    //         datasets: [{
    //             data: [25, 25, 25],
    //             backgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D"
    //             ],
    //             hoverBackgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D"
    //             ]
    //         }]
    //     },
    //     options: {
    //         cutoutPercentage: 80,
    //         // elements: {
    //         //     center: {
    //         //         text: '25%',
    //         //         color: '#000',
    //         //         fontStyle: 'Arial',
    //         //         sidePadding: 200
    //         //     }
    //         // },
    //         legend: {
    //             display: false
    //         },
    //     }
    // };
    // var ctx = document.getElementById("doughnutPsychologicalWBScore").getContext("2d");
    // var myChart = new Chart(ctx, config);
    // // --------------- Doughnut Chart Area -------------------------
    // //-------------------------------- gauge Chart ------------------------------
    // var opts = {
    //     angle: 0.0, // The span of the gauge arc
    //     lineWidth: 0.25, // The line thickness
    //     radiusScale: 1, // Relative radius
    //     pointer: {
    //         length: 0.6, // // Relative to gauge radius
    //         strokeWidth: 0.025, // The thickness
    //         color: '#000000' // Fill color
    //     },
    //     limitMax: false, // If false, max value increases automatically if value > maxValue
    //     limitMin: false, // If true, the min value of the gauge will be fixed
    //     colorStart: '#21c393', // Colors
    //     colorStop: '#21c393', // just experiment with them
    //     strokeColor: '#f2f4f4', // to see which ones work best for you
    //     generateGradient: true,
    //     highDpiSupport: true, // High resolution support

    // };
    // var target = document.getElementById('gaugeChartCompanyScore'); // your canvas element
    // var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!
    // gauge.maxValue = 5; // set max gauge value
    // gauge.setMinValue(0); // Prefer setter over gauge.minValue = 0
    // gauge.animationSpeed = 32; // set animation speed (32 is default value)
    // gauge.set(4); // set actual value
    // //-------------------------------- gauge Chart ------------------------------
    // // --------------- lineChartCompanyScore ---------------
    // new Chart(document.getElementById("lineChartCompanyScore"), {
    //     type: "line",
    //     gridLines: {
    //         display: true,
    //         drawBorder: true,
    //         drawOnChartArea: false,
    //     },
    //     data: {
    //         labels: ["1 Jan - 7 Jan", "8 Jan - 14 Jan", "15 Jan - 21 Jan", "22 Jan - 27 Jan", "28 Jan - 3 Feb", "4 Feb - 10 Feb", "11 Feb - 17 Feb"],
    //         datasets: [{
    //             label: "My First Dataset ",
    //             data: [65, 59, 80, 81, 56, 55, 40],
    //             fill: false,
    //             borderColor: "rgb(0, 165, 209)",
    //             lineTension: 0.5
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //     }
    // });
    // // --------------- lineChartCompanyScore ---------------
    // // --------------- Doughnut Chart Area -------------------------
    // var config = {
    //     type: 'doughnut',
    //     data: {
    //         labels: [
    //             "Low", "Moderate", "Hight",
    //         ],
    //         datasets: [{
    //             data: [25, 25, 25],
    //             backgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D"
    //             ],
    //             hoverBackgroundColor: [
    //                 "#E80707",
    //                 "#FFA304",
    //                 "#45D25D"
    //             ]
    //         }]
    //     },
    //     options: {
    //         cutoutPercentage: 80,
    //         // elements: {
    //         //     center: {
    //         //         text: '25%',
    //         //         color: '#000',
    //         //         fontStyle: 'Arial',
    //         //         sidePadding: 200
    //         //     }
    //         // },
    //         legend: {
    //             display: false
    //         },
    //     }
    // };
    // var ctx = document.getElementById("doughnutChildPhysical").getContext("2d");
    // var myChart = new Chart(ctx, config);
    // // --------------- Doughnut Chart Area -------------------------
    // // --------------- lineChartCompanyScore ---------------
    // new Chart(document.getElementById("lineChartChildPhysical"), {
    //     type: "line",
    //     gridLines: {
    //         display: true,
    //         drawBorder: true,
    //         drawOnChartArea: false,
    //     },
    //     data: {
    //         labels: ["1 Jan - 7 Jan", "8 Jan - 14 Jan", "15 Jan - 21 Jan", "22 Jan - 27 Jan", "28 Jan - 3 Feb", "4 Feb - 10 Feb", "11 Feb - 17 Feb"],
    //         datasets: [{
    //             label: "My First Dataset ",
    //             data: [65, 59, 80, 81, 56, 55, 40],
    //             fill: false,
    //             borderColor: "rgb(0, 165, 209)",
    //             lineTension: 0.5
    //         }]
    //     },
    //     options: {
    //         maintainAspectRatio: false,
    //         legend: {
    //             display: false
    //         },
    //     }
    // });
    // // --------------- lineChartCompanyScore ---------------
    // //-------------------------------- gauge Chart ------------------------------
    // var opts = {
    //     angle: 0.0, // The span of the gauge arc
    //     lineWidth: 0.25, // The line thickness
    //     radiusScale: 1, // Relative radius
    //     pointer: {
    //         length: 0.6, // Relative to gauge radius
    //         strokeWidth: 0.025, // The thickness
    //         color: '#000000' // Fill color
    //     },
    //     limitMax: false, // If false, max value increases automatically if value > maxValue
    //     limitMin: false, // If true, the min value of the gauge will be fixed
    //     colorStart: '#21c393', // Colors
    //     colorStop: '#21c393', // just experiment with them
    //     strokeColor: '#f2f4f4', // to see which ones work best for you
    //     generateGradient: true,
    //     highDpiSupport: true, // High resolution support

    // };
    // var target = document.getElementById('gaugeChartSubScore_1'); // your canvas element
    // var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!

    // gauge.maxValue = 5; // set max gauge value
    // gauge.setMinValue(0); // Prefer setter over gauge.minValue = 0
    // gauge.animationSpeed = 32; // set animation speed (32 is default value)
    // gauge.set(4); // set actual value


    // var target = document.getElementById('gaugeChartSubScore_2'); // your canvas element
    // var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!
    // var target = document.getElementById('gaugeChartSubScore_3'); // your canvas element
    // var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!
    // var target = document.getElementById('gaugeChartSubScore_4'); // your canvas element
    // var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!

    //-------------------------------- gauge Chart ------------------------------
})(jQuery);
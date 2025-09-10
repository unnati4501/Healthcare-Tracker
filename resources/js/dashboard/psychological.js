/*
 * Psychological tab common AJAX call
 */
function psychologicalTabAjaxCall(tier, options = null) {
    var age = $('#age').val();
    age = ((age) ? age.split('_') : age);
    var companyIds = $('#company_id').val();
    var departmentId = $('#department_id').val();
    var locationId = $('#location_id').val();
    var age1 = ((age) ? age[0] : null);
    var age2 = ((age) ? age[1] : null);
    $.ajax({
        url: urls.psychological,
        type: 'POST',
        dataType: 'json',
        data: {
            tier: tier,
            companyId: ($.isNumeric(companyIds) ? companyIds : null),
            departmentId: ($.isNumeric(departmentId) ? departmentId : null),
            locationId: ($.isNumeric(locationId) ? locationId : null),
            age1: ($.isNumeric(age1) ? age1 : null),
            age2: ($.isNumeric(age2) ? age2 : null),
            options: options
        }
    }).done(function(data) {
        loadPsychologicalTabData(data, tier);
    }).fail(function(error) {
        toastr.error('Failed to load Psychological tab data.');
    })
}
/*
 * Load Psychological Tab Data Tier by Tier
 */
function loadPsychologicalTabData(data, tier) {
    switch (tier) {
        case 1:
            // intilize psychological hs chart with blank data
            if (typeof psychologicalCharts.psychologicalHsDoughnut.object != "object") {
                psychologicalCharts.psychologicalHsDoughnut.object = new Chart($('#doughnutPsychologicalScore'), psychologicalCharts.psychologicalHsDoughnut.config);
            }
            // Update psychological hs category chart data
            psychologicalCharts.psychologicalHsDoughnut.config.data.datasets[0].data = data.hsCategoryData ? data.hsCategoryData : [];
            psychologicalCharts.psychologicalHsDoughnut.object.update();
            // Update psychological hs sub categories chart data
            $('[data-sub-category-block]').empty();
            if (data.hsSubCategoryData) {
                data.hsSubCategoryData.forEach(function(item, key) {
                    $('[data-sub-category-block]').append('<div class="col-sm-4"> <div class="total-recipes-chart mt-auto mb-2"> <input class="knob-chart knob-chart-font-18" data-fgcolor="' + item.color + '" data-height="110" data-linecap="round" data-readonly="true" data-thickness=".15" data-width="110" readonly="readonly" type="text" value="' + item.percent + '"/> </div> <h6 class="text-center">' + item.sub_category + '</h6> </div>');
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
        case 2:
            // intilize meditation hours chart with blank data
            if (typeof psychologicalCharts.meditationHoursChart.object != "object") {
                psychologicalCharts.meditationHoursChart.object = new Chart($('#chartMeditationHours'), psychologicalCharts.meditationHoursChart.config);
            }
            // Update meditation hours chart data
            psychologicalCharts.meditationHoursChart.config.data.labels = data.labels ? data.labels : [];
            psychologicalCharts.meditationHoursChart.config.data.datasets[0].data = data.data ? data.data : [];
            psychologicalCharts.meditationHoursChart.object.update();
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
            // Update top meditation tracks chart data on date range filter
            if (data.change == 'daterangeTopMeditationTracks') {
                psychologicalCharts.topMeditationTracks.config.data.labels = data.topMeditationTracksData ? data.topMeditationTracksData.meditationTitle : [];
                psychologicalCharts.topMeditationTracks.config.data.datasets[0].data = data.topMeditationTracksData ? data.topMeditationTracksData.totalViews : [];
                psychologicalCharts.topMeditationTracks.config.data.datasets[0].backgroundColor = data.topMeditationTracksData ? poolColors(data.topMeditationTracksData.meditationTitle.length) : [];
                psychologicalCharts.topMeditationTracks.object.update();
                return;
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
            // intilize top meditation tracks chart with blank data
            if (typeof psychologicalCharts.topMeditationTracks.object != "object") {
                psychologicalCharts.topMeditationTracks.object = new Chart($('#chartTopTrack'), psychologicalCharts.topMeditationTracks.config);
            }
            // Update top meditation tracks chart data
            psychologicalCharts.topMeditationTracks.config.data.labels = data.topMeditationTracksData ? data.topMeditationTracksData.meditationTitle : [];
            psychologicalCharts.topMeditationTracks.config.data.datasets[0].data = data.topMeditationTracksData ? data.topMeditationTracksData.totalViews : [];
            psychologicalCharts.topMeditationTracks.config.data.datasets[0].backgroundColor = data.topMeditationTracksData ? poolColors(data.topMeditationTracksData.meditationTitle.length) : [];
            psychologicalCharts.topMeditationTracks.object.update();


            break;
        case 4:
            // intilize moods analysis chart with blank data
            if (typeof psychologicalCharts.moodAnalysis.object != "object") {
                psychologicalCharts.moodAnalysis.object = new Chart($('#chartMoodsAnalysis'), psychologicalCharts.moodAnalysis.config);
            }
            // Update moods analysis chart data
            psychologicalCharts.moodAnalysis.config.data.labels = data.moodAnalysis ? data.moodAnalysis.title : [];
            psychologicalCharts.moodAnalysis.config.data.datasets[0].data = data.moodAnalysis ? data.moodAnalysis.percent : [];
            psychologicalCharts.moodAnalysis.object.update();
            break;
        default:
            toastr.error('Something went wrong.!');
            break;
    }
}
// /*
//  * Code for week/month/year filter in meditation hours chart
//  */
// $(document).on('click', '[data-meditiation-hours-from-duration] li', function(e) {
//     var _this = $(this);
//     var duration = (_this.data('meditiation-hours-duration') || null);
//     var parent = _this.parent();
//     $(parent).find('li').removeClass('active');
//     _this.addClass('active process');
//     if (typeof options == 'undefined') {
//         options = new Object();
//     }
//     options.fromDateMeditationHours = duration;
//     psychologicalTabAjaxCall(2, options);
// });
/*
 * Code for week/month/year filter in moods analysis chart
 */
// $(document).on('click', '[data-mood-analysis-from-duration] li', function(e) {
//     var _this = $(this);
//     var duration = (_this.data('mood-analysis-duration') || null);
//     var parent = _this.parent();
//     $(parent).find('li').removeClass('active');
//     _this.addClass('active process');
//     if (typeof options == 'undefined') {
//         options = new Object();
//     }
//     options.fromDateMoodsAnalysis = duration;
//     psychologicalTabAjaxCall(4, options);
// });
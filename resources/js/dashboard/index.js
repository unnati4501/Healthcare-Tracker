/*
 * Variable declarations
 */
var tabs = ['usage', 'behaviour', 'audit', 'booking', 'eapactivity', 'digitaltherapy'];
var currActiveTab = ((location.hash) ? location.hash.substr(1) : ($('ul li.active').attr('id') == 'audit') ? 'audit' : "usage"),
    currActiveTab = (($.inArray(currActiveTab, tabs) !== -1) ? currActiveTab : "usage");
if ($('#' + currActiveTab).length > 0) {
    //$('#dashboardTabs .main-tabs a[href="#' + currActiveTab + '"]').show();
    //event.preventDefault()
    new bootstrap.Tab('#dashboardTabs .main-tabs a[href="#' + currActiveTab + '"]').show();
    // const currentTab = new bootstrap.Tab('#dashboardTabs .main-tabs a[href="#' + currActiveTab + '"]');
    // currentTab.addEventListener('click', event => {
    //     event.preventDefault();
    //     currentTab.show();
    // });

} else {
    if ($('#usage').length > 0) {
         //$('#dashboardTabs .main-tabs a[href="#usage"]').tab('show');
        new bootstrap.Tab('#dashboardTabs .main-tabs a[href="#usage"]');
        // usageTab.addEventListener('click', event => {
        //     event.preventDefault();
        //     usageTab.show();
        // });
        currActiveTab = "usage";
    } else if ($('#behaviour').length > 0) {
       // $('#dashboardTabs .main-tabs a[href="#behaviour"]').tab('show');
        new bootstrap.Tab('#dashboardTabs .main-tabs a[href="#behaviour"]').show();
        currActiveTab = "behaviour";
    } else if ($('#audit').length > 0) {
        //$('#dashboardTabs .main-tabs a[href="#audit"]').tab('show');
        new bootstrap.Tab('#dashboardTabs .main-tabs a[href="#audit"]').show();
        currActiveTab = "audit";
    } else if ($('#booking').length > 0) {
        //$('#dashboardTabs .main-tabs a[href="#booking"]').tab('show');
        new bootstrap.Tab('#dashboardTabs .main-tabs a[href="#booking"]').show();
        currActiveTab = "booking";
    } else if ($('#eapactivity').length > 0) {
        //$('#dashboardTabs .main-tabs a[href="#eapactivity"]').tab('show');
        new bootstrap.Tab('#dashboardTabs .main-tabs a[href="#eapactivity"]').show();
        currActiveTab = "eapactivity";
    } else if ($('#digitaltherapy').length > 0) {
        //$('#dashboardTabs .main-tabs a[href="#digitaltherapy"]').tab('show');
        new bootstrap.Tab('#dashboardTabs .main-tabs a[href="#digitaltherapy"]').show();
        currActiveTab = "digitaltherapy";
    }
}
/*
 * Document ready load
 */
$(document).ready(function() {
    /*--------------- Tabbing Initialize ---------------*/
    $('#dashboardTabs .main-tabs a').click(function(e) {
        $('.page-loader-wrapper').show();
        currActiveTab = $(this).parent().attr('data-id');
        var scroll = $(window).scrollTop();
        window.location.hash = currActiveTab;
        $(window).scrollTop(scroll);
        e.stopImmediatePropagation();
        if (typeof options == 'undefined') {
            options = new Object();
        }
        loadTabData(options);
        $('.page-loader-wrapper').hide();
    });

    $('#service_id').val('all').select2();
    /*--------------- ajaxSetup ---------------*/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    /*--------------- jQueryKnob ---------------*/
    knobInit();
    /*--------------- monthrange ---------------*/
    monthRangeInit();
    /*--------------- Chart settings ---------------*/
    chartAreaSettings();
    /*--------------- Load tab data on document init ---------------*/
    loadTabData();
});
/*
 * Load active tab data on call
 */
function loadTabData(options) {
    if (typeof options == 'undefined') {
        options = new Object();
    }
    $('#department, #age, #location, #industry, #company').show();
    switch (currActiveTab) {
        case 'usage':
            if ($('#usage').length > 0) {
                $('#service, #dtcompany').hide();
                var tier2Options = options,
                    tier4Options = options,
                    now = moment();
                tier4Options.fromDateTopMeditationTracks = moment($('#daterangeTopMeditationTracksFrom').datepicker("getDate"));
                tier4Options.endDateTopMeditationTracks = moment($('#daterangeTopMeditationTracksFromTo').datepicker("getDate")).endOf('month');
                tier4Options.fromDateTopMasterclass = moment($('#daterangeTopMasterclassFrom').datepicker("getDate"));
                tier4Options.endDateTopMasterclass = moment($('#daterangeTopMasterclassFromTo').datepicker("getDate")).endOf('month');
                tier4Options.fromDateTopWebinar = moment($('#daterangeTopWebinarTracksFrom').datepicker("getDate"));
                tier4Options.endDateTopWebinars = moment($('#daterangeTopWebinarTracksFromTo').datepicker("getDate")).endOf('month');
                tier4Options.fromDateTopFeeds = moment($('#daterangeTopFeedsFrom').datepicker("getDate"));
                tier4Options.endDateTopFeeds = moment($('#daterangeTopFeedsFromTo').datepicker("getDate")).endOf('month');
                // get dates from pickers
                tier2Options.fromDateStepsPeriod = moment($('#daterangeStepsPeriodFrom').datepicker("getDate"));
                tier2Options.endDateStepsPeriod = moment($('#daterangeStepsPeriodFromTo').datepicker("getDate")).endOf('month');
                // compare with current time if end time is in future then set end time to now
                tier2Options.endDateStepsPeriod = ((tier2Options.endDateStepsPeriod > now) ? now : tier2Options.endDateStepsPeriod);
                tier2Options.endDateCaloriesPeriod = ((tier2Options.endDateCaloriesPeriod > now) ? now : tier2Options.endDateCaloriesPeriod);
                tier4Options.endDateSuperstars = ((tier4Options.endDateSuperstars > now) ? now : tier4Options.endDateSuperstars);
                // get date and time in specific format
                tier2Options.fromDateStepsPeriod = tier2Options.fromDateStepsPeriod.format('YYYY-MM-DD 00:00:00');
                tier2Options.endDateStepsPeriod = tier2Options.endDateStepsPeriod.format('YYYY-MM-DD 23:59:59');
                tier4Options.fromDateTopMeditationTracks = tier4Options.fromDateTopMeditationTracks.format('YYYY-MM-DD 00:00:00');
                tier4Options.endDateTopMeditationTracks = tier4Options.endDateTopMeditationTracks.format('YYYY-MM-DD 23:59:59');
                tier4Options.fromDateTopWebinar = tier4Options.fromDateTopWebinar.format('YYYY-MM-DD 00:00:00');
                tier4Options.endDateTopWebinars = tier4Options.endDateTopWebinars.format('YYYY-MM-DD 23:59:59');
                tier4Options.fromDateTopMasterclass = tier4Options.fromDateTopMasterclass.format('YYYY-MM-DD 00:00:00');
                tier4Options.endDateTopMasterclass = tier4Options.endDateTopMasterclass.format('YYYY-MM-DD 23:59:59');
                tier4Options.fromDateTopFeeds = tier4Options.fromDateTopFeeds.format('YYYY-MM-DD 00:00:00');
                tier4Options.endDateTopFeeds = tier4Options.endDateTopFeeds.format('YYYY-MM-DD 23:59:59');
                // load tab data
                appUsageTabAjaxCall(1, options);
                appUsageTabAjaxCall(2, tier2Options);
                appUsageTabAjaxCall(3, options);
                appUsageTabAjaxCall(4, tier4Options);
                // psychologicalTabAjaxCall(4, tier3Options);
            }
            break;
        case 'behaviour':
            if ($('#behaviour').length > 0) {
                $('#service, #dtcompany').hide();
                var tier1Options = options,
                    tier2Options = options,
                    tier3Options = options,
                    tier4Options = options,
                    now = moment();
                tier2Options.fromDateStepsPeriod = moment($('#daterangeStepsPeriodFrom').datepicker("getDate"));
                tier2Options.endDateStepsPeriod = moment($('#daterangeStepsPeriodFromTo').datepicker("getDate")).endOf('month');
                tier2Options.fromDateHsPsychological = moment($('#daterangeHsPsychologicalFrom').datepicker("getDate"));
                tier2Options.endDateHsPsychological = moment($('#daterangeHsPsychologicalFromTo').datepicker("getDate")).endOf('month');
                tier2Options.fromDateCaloriesPeriod = moment($('#daterangeCaloriesPeriodFrom').datepicker("getDate"));
                tier2Options.endDateCaloriesPeriod = moment($('#daterangeCaloriesPeriodFromTo').datepicker("getDate")).endOf('month');
                tier4Options.fromDateSuperstars = moment($('#daterangeSuperstarsFrom').datepicker("getDate"));
                tier4Options.endDateSuperstars = moment($('#daterangeSuperstarsFromTo').datepicker("getDate")).endOf('month');
                // get dates from pickers
                tier1Options.fromDateHsPhysical = moment($('#daterangeHsPhysicalFrom').datepicker("getDate"));
                tier1Options.endDateHsPhysical = moment($('#daterangeHsPhysicalFromTo').datepicker("getDate")).endOf('month');
                tier2Options.fromDateExerciseRanges = moment($('#daterangeExerciseRangesFrom').datepicker("getDate"));
                tier2Options.endDateExerciseRanges = moment($('#daterangeExerciseRangesFromTo').datepicker("getDate")).endOf('month');
                tier2Options.fromDateStepRanges = moment($('#daterangeStepRangesFrom').datepicker("getDate"));
                tier2Options.endDateStepRanges = moment($('#daterangeStepRangesFromTo').datepicker("getDate")).endOf('month');
                //Date rage for most popular exercise by tracker
                tier2Options.fromDatePopularExerciseTrackerRanges = moment($('#daterangeMostPopularExTrackerFrom').datepicker("getDate"));
                tier2Options.endDatePopularExerciseTrackerRanges = moment($('#daterangeMostPopularExTrackerTo').datepicker("getDate")).endOf('month');
                //Date rage for most popular exercise by manual
                tier2Options.fromDatePopularExerciseManualRanges = moment($('#daterangeMostPopularExManualFrom').datepicker("getDate"));
                tier2Options.endDatePopularExerciseManualRanges = moment($('#daterangeMostPopularExManualTo').datepicker("getDate")).endOf('month');
                // compare with current time if end time is in future then set end time to now
                tier1Options.endDateHsPhysical = ((tier1Options.endDateHsPhysical > now) ? now : tier1Options.endDateHsPhysical);
                tier2Options.endDateExerciseRanges = ((tier2Options.endDateExerciseRanges > now) ? now : tier2Options.endDateExerciseRanges);
                tier2Options.endDateStepRanges = ((tier2Options.endDateStepRanges > now) ? now : tier2Options.endDateStepRanges);
                // get date and time in specific format
                tier1Options.fromDateHsPhysical = tier1Options.fromDateHsPhysical.format('YYYY-MM-DD 00:00:00');
                tier1Options.endDateHsPhysical = tier1Options.endDateHsPhysical.format('YYYY-MM-DD 23:59:59');
                tier2Options.fromDateExerciseRanges = tier2Options.fromDateExerciseRanges.format('YYYY-MM-DD 00:00:00');
                tier2Options.endDateExerciseRanges = tier2Options.endDateExerciseRanges.format('YYYY-MM-DD 23:59:59');
                tier2Options.fromDateStepRanges = tier2Options.fromDateStepRanges.format('YYYY-MM-DD 00:00:00');
                tier2Options.endDateStepRanges = tier2Options.endDateStepRanges.format('YYYY-MM-DD 23:59:59');
                tier2Options.fromDateStepsPeriod = tier2Options.fromDateStepsPeriod.format('YYYY-MM-DD 00:00:00');
                tier2Options.endDateStepsPeriod = tier2Options.endDateStepsPeriod.format('YYYY-MM-DD 23:59:59');
                tier2Options.fromDatePopularExerciseTrackerRanges = tier2Options.fromDatePopularExerciseTrackerRanges.format('YYYY-MM-DD 00:00:00');
                tier2Options.endDatePopularExerciseTrackerRanges = tier2Options.endDatePopularExerciseTrackerRanges.format('YYYY-MM-DD 23:59:59');
                tier2Options.fromDatePopularExerciseManualRanges = tier2Options.fromDatePopularExerciseManualRanges.format('YYYY-MM-DD 00:00:00');
                tier2Options.endDatePopularExerciseManualRanges = tier2Options.endDatePopularExerciseManualRanges.format('YYYY-MM-DD 23:59:59');
                // get date and time in specific format
                tier2Options.fromDateHsPsychological = tier2Options.fromDateHsPsychological.format('YYYY-MM-DD 00:00:00');
                tier2Options.endDateHsPsychological = tier2Options.endDateHsPsychological.format('YYYY-MM-DD 23:59:59');
                tier2Options.fromDateCaloriesPeriod = tier2Options.fromDateCaloriesPeriod.format('YYYY-MM-DD 00:00:00');
                tier2Options.endDateCaloriesPeriod = tier2Options.endDateCaloriesPeriod.format('YYYY-MM-DD 23:59:59');
                tier4Options.fromDateSuperstars = tier4Options.fromDateSuperstars.format('YYYY-MM-DD 00:00:00');
                tier4Options.endDateSuperstars = tier4Options.endDateSuperstars.format('YYYY-MM-DD 23:59:59');
                // load tab data
                // physicalTabAjaxCall(1, tier1Options);
                physicalTabAjaxCall(2, tier2Options);
                physicalTabAjaxCall(3, options);
                physicalTabAjaxCall(4, options);
            }
            break;
        case 'psychological':
            $('#service, #dtcompany').hide();
            var tier1Options = options,
                tier3Options = options,
                now = moment();
            // get dates from pickers
            tier1Options.fromDateHsPsychological = moment($('#daterangeHsPsychologicalFrom').datepicker("getDate"));
            tier1Options.endDateHsPsychological = moment($('#daterangeHsPsychologicalFromTo').datepicker("getDate")).endOf('month');
            // compare with current time if end time is in future then set end time to now
            tier1Options.endDateHsPsychological = ((tier1Options.endDateHsPsychological > now) ? now : tier1Options.endDateHsPsychological);
            tier3Options.endDateTopMeditationTracks = ((tier3Options.endDateTopMeditationTracks > now) ? now : tier3Options.endDateTopMeditationTracks);
            // get date and time in specific format
            tier1Options.fromDateHsPsychological = tier1Options.fromDateHsPsychological.format('YYYY-MM-DD 00:00:00');
            tier1Options.endDateHsPsychological = tier1Options.endDateHsPsychological.format('YYYY-MM-DD 23:59:59');
            tier3Options.fromDateTopMeditationTracks = tier3Options.fromDateTopMeditationTracks.format('YYYY-MM-DD 00:00:00');
            tier3Options.endDateTopMeditationTracks = tier3Options.endDateTopMeditationTracks.format('YYYY-MM-DD 23:59:59');
            // load tab data
            psychologicalTabAjaxCall(1, tier1Options);
            psychologicalTabAjaxCall(2, options);
            psychologicalTabAjaxCall(3, tier3Options);
            psychologicalTabAjaxCall(4, options);
            break;
        case 'audit':
            if ($('#audit').length > 0) {
                $('#service, #dtcompany').hide();
                var companyScoreOptions = options,
                    categoryWiseCompanyScoreOptions = options,
                    now = moment(),
                    companyfromDate = moment($('#companyScoreFromMonth').datepicker("getDate")),
                    companyendDate = moment($('#companyScoreToMonth').datepicker("getDate")).endOf('month'),
                    categoryfromDate = moment($('#categoryWiseCompanyScoreFromMonth').datepicker("getDate")),
                    categoryendDate = moment($('#categoryWiseCompanyScoreToMonth').datepicker("getDate")).endOf('month');
                // compare with current time if end time is in future then set end time to now
                if (companyendDate > now) {
                    companyendDate = now;
                }
                if (categoryendDate > now) {
                    categoryendDate = now;
                }
                // get date and time in specific format
                companyScoreOptions.fromDateCompanyScore = companyfromDate.format('YYYY-MM-DD 00:00:00');
                companyScoreOptions.endDateCompanyScore = companyendDate.format('YYYY-MM-DD 23:59:59');
                categoryWiseCompanyScoreOptions.fromDateCategoryCompanyScore = categoryfromDate.format('YYYY-MM-DD 00:00:00');
                categoryWiseCompanyScoreOptions.endDateCategoryCompanyScore = categoryendDate.format('YYYY-MM-DD 23:59:59');
                // load tab data
                auditTabAjaxCall(1, companyScoreOptions);
                auditTabAjaxCall(2, companyScoreOptions);
            }
            break;
        case 'booking':
            if ($('#booking').length > 0) {
                $('#department, #age, #service, #location, #dtcompany').hide();
                // Upcoming Events
                bookingTabAjaxCall(1);
                // Events Revenue
                bookingTabAjaxCall(2);
                // Today's Event calendar
                bookingTabAjaxCall(3);
                // Top 10 categories
                bookingTabAjaxCall(4);
            }
            break;
        case 'eapactivity':
            if ($('#eapactivity').length > 0) {
                $('#department, #location, #service, #dtcompany').hide();
                // First block ( Sessions )
                eapActivityTabAjaxCall(1);
                // Second block ( Appointment Trend )
                eapActivityTabAjaxCall(2);
                // Third block ( Skill Trend )
                eapActivityTabAjaxCall(3);
                if ($('#total-counsellors').length > 0) {
                    // Fourth Block ( Therapist )
                    eapActivityTabAjaxCall(4);
                }
            }
            break;
        case 'digitaltherapy':
            if ($('#digitaltherapy').length > 0) {
                //$('#department, #location, #industry, #company').hide();
                $('#industry, #company').hide();
                $('#service, #dtcompany').show();
                //loadDigitalTherapyEnabledcompanies();
                // First block ( Sessions )
                digitalTherapyTabAjaxCall(1);
                // Second block ( Appointment Trend )
                digitalTherapyTabAjaxCall(2);
                // Third block ( Skill Trend )
                digitalTherapyTabAjaxCall(3);
                if ($('#total-wellbeingspecialists').length > 0) {
                    // Fourth Block ( Therapist )
                    digitalTherapyTabAjaxCall(4);
                }
            }
            break;
    }
}
/*
 * Change num format
 */
function num_format_short(n) {
    if (n < 1e3) return n;
    if (n >= 1e3 && n < 1e6) return +(n / 1e3).toFixed(1) + "K";
    if (n >= 1e6 && n < 1e9) return +(n / 1e6).toFixed(1) + "M";
    if (n >= 1e9 && n < 1e12) return +(n / 1e9).toFixed(1) + "B";
    if (n >= 1e12) return +(n / 1e12).toFixed(1) + "T";
}
/*
 * function to generate random color in hex form
 */
function dynamicColors() {
    var r = Math.floor(Math.random() * 255);
    var g = Math.floor(Math.random() * 255);
    var b = Math.floor(Math.random() * 255);
    return "rgba(" + r + "," + g + "," + b + ", 0.9)";
}
/*
 * Returns array of dynamic colors for charts
 */
function poolColors(a) {
    var pool = [];
    for (i = 0; i < a; i++) {
        pool.push(dynamicColors());
    }
    return pool;
}
/*
 * Initialize knob charts
 */
function knobInit() {
    $('.knob-chart').knob({
        min: 0,
        max: 100,
        format: function(value) {
            return value + '%';
        }
    });
}
/*
 * Initialize monthrange pickers
 */
function monthRangeInit() {
    $('.monthranges').datepicker({
        format: "M, yyyy",
        startView: 1,
        minViewMode: 1,
        maxViewMode: 2,
        clearBtn: false,
        autoclose: true,
        endDate: ((moment().isSame(moment().endOf('month'), 'date')) ? moment().endOf('month').add(1, 'd').toDate() : moment().endOf('month').toDate())
    });
    // App usage tab 1 month ranges
    $('#daterangeStepsPeriodFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeStepsPeriodFromTo').datepicker("setDate", moment().toDate());
    $('#daterangeCaloriesPeriodFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeCaloriesPeriodFromTo').datepicker("setDate", moment().toDate());
    $('#daterangeSuperstarsFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeSuperstarsFromTo').datepicker("setDate", moment().toDate());
    // Physical tab 2 month ranges
    $('#daterangeHsPhysicalFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeHsPhysicalFromTo').datepicker("setDate", moment().toDate());
    $('#daterangeExerciseRangesFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeExerciseRangesFromTo').datepicker("setDate", moment().toDate());
    $('#daterangeStepRangesFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeStepRangesFromTo').datepicker("setDate", moment().toDate());
    $('#daterangeMostPopularExTrackerFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeMostPopularExTrackerTo').datepicker("setDate", moment().toDate());
    $('#daterangeMostPopularExManualFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeMostPopularExManualTo').datepicker("setDate", moment().toDate());
    // Psychological tab 3 month ranges
    $('#daterangeHsPsychologicalFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeHsPsychologicalFromTo').datepicker("setDate", moment().toDate());
    $('#daterangeTopMeditationTracksFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeTopMeditationTracksFromTo').datepicker("setDate", moment().toDate());
    $('#daterangeTopWebinarTracksFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeTopWebinarTracksFromTo').datepicker("setDate", moment().toDate());
    $('#daterangeTopMasterclassFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeTopMasterclassFromTo').datepicker("setDate", moment().toDate());
    $('#daterangeTopFeedsFrom').datepicker("setDate", moment().subtract(1, 'months').startOf('month').toDate());
    $('#daterangeTopFeedsFromTo').datepicker("setDate", moment().toDate());
    // Audit tab 4 month ranges
    $('#companyScoreFromMonth').datepicker("setDate", moment().subtract(5, 'months').startOf('month').toDate());
    $('#companyScoreToMonth').datepicker("setDate", moment().toDate());
    $('#categoryWiseCompanyScoreFromMonth').datepicker("setDate", moment().subtract(5, 'months').startOf('month').toDate());
    $('#categoryWiseCompanyScoreToMonth').datepicker("setDate", moment().toDate());
    $('.monthranges').on('changeDate', function(e) {
        let datepickerData = $(this).data('datepicker');
        if (!datepickerData.updating) {
            let now = moment(),
                fromDate = moment(datepickerData.pickers[0].getDate()),
                toDate = moment(datepickerData.pickers[1].getDate()).endOf('month'),
                tier = $(this).data('tier'),
                id = $(this).attr('id'),
                options = new Object();
            if (toDate > now) {
                toDate = now;
            }
            options.change = id;
            switch (currActiveTab) {
                case 'usage':
                    switch (id) {
                        case 'daterangeTopMeditationTracks':
                            options.fromDateTopMeditationTracks = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateTopMeditationTracks = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeTopWebinarTracks':
                            var webinarDuration = ($('[data-popular-webinar-from-duration] li.active').data('popular-webinar-duration') || null)
                            var popularmasterclassDuration = ($('[data-popular-masterclass-from-duration] li.active').data('popular-masterclass-duration') || null)
                            var topMasterclassDuration = ($('[data-top-masterclass-from-duration] li.active').data('top-masterclass-duration') || null)
                            options.fromDateTopWebinar = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateTopWebinars = toDate.format('YYYY-MM-DD 23:59:59');
                            options.fromDatePopularWebinar = webinarDuration;
                            options.fromDatePopularMasterclass = popularmasterclassDuration;
                            options.fromDateTopMasterclass = topMasterclassDuration;
                            break;
                        case 'daterangeTopMasterclass':
                            options.fromDateTopMasterclass = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateTopMasterclass = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeTopFeeds':
                            options.fromDateTopFeeds = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateTopFeeds = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                    }
                    appUsageTabAjaxCall(tier, options);
                    break;
                case 'behaviour':
                    switch (id) {
                        case 'daterangeHsPhysical':
                            options.fromDateHsPhysical = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateHsPhysical = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeExerciseRanges':
                            options.fromDateExerciseRanges = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateExerciseRanges = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeStepRanges':
                            options.fromDateStepRanges = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateStepRanges = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeHsPsychological':
                            options.fromDateHsPsychological = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateHsPsychological = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeStepsPeriod':
                            options.fromDateStepsPeriod = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateStepsPeriod = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeCaloriesPeriod':
                            options.fromDateCaloriesPeriod = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateCaloriesPeriod = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeSuperstars':
                            options.fromDateSuperstars = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateSuperstars = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeMostPopularExTracker':
                            options.fromDatePopularExerciseTrackerRanges = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDatePopularExerciseTrackerRanges = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeMostPopularExManual':
                            options.fromDatePopularExerciseManualRanges = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDatePopularExerciseManualRanges = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                    }
                    physicalTabAjaxCall(tier, options);
                    break;
                case 'psychological':
                    switch (id) {
                        case 'daterangeHsPsychological':
                            options.fromDateHsPsychological = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateHsPsychological = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeTopMeditationTracks':
                            options.fromDateTopMeditationTracks = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateTopMeditationTracks = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                    }
                    psychologicalTabAjaxCall(tier, options);
                    break;
                case 'audit':
                    switch (id) {
                        case 'daterangeAuditCompanyScore':
                            options.fromDateCompanyScore = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateCompanyScore = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                        case 'daterangeAuditCategoryWiseCompanyScore':
                            options.category_id = ($('#audit_category_wise_company_score_tab .selected .item').data('id') || 0);
                            options.fromDateCategoryCompanyScore = fromDate.format('YYYY-MM-DD 00:00:00');
                            options.endDateCategoryCompanyScore = toDate.format('YYYY-MM-DD 23:59:59');
                            break;
                    }
                    auditTabAjaxCall(tier, options);
                    break;
                case 'booking':
                    bookingTabAjaxCall(1);
                    break;
            }
        }
    });
}
/*
 * Default chart area settings
 */
function chartAreaSettings() {
    Chart.elements.Rectangle.prototype.draw = function() {
        var ctx = this._chart.ctx;
        var vm = this._view;
        var left, right, top, bottom, signX, signY, borderSkipped, radius;
        var borderWidth = vm.borderWidth;
        var cornerRadius = 30;
        if (!vm.horizontal) {
            // bar
            left = vm.x - vm.width / 2;
            right = vm.x + vm.width / 2;
            top = vm.y;
            bottom = vm.base;
            signX = 1;
            signY = bottom > top ? 1 : -1;
            borderSkipped = vm.borderSkipped || 'bottom';
        } else {
            // horizontal bar
            left = 0;
            right = vm.x;
            top = vm.y - vm.height / 2;
            bottom = vm.y + vm.height / 2;
            signX = right > left ? 1 : -1;
            signY = 1;
            borderSkipped = vm.borderSkipped || 'left';
        }
        // Canvas doesn't allow us to stroke inside the width so we can
        // adjust the sizes to fit if we're setting a stroke on the line
        if (borderWidth) {
            // borderWidth shold be less than bar width and bar height.
            var barSize = Math.min(Math.abs(left - right), Math.abs(top - bottom));
            borderWidth = borderWidth > barSize ? barSize : borderWidth;
            var halfStroke = borderWidth / 2;
            // Adjust borderWidth when bar top position is near vm.base(zero).
            var borderLeft = left + (borderSkipped !== 'left' ? halfStroke * signX : 0);
            var borderRight = right + (borderSkipped !== 'right' ? -halfStroke * signX : 0);
            var borderTop = top + (borderSkipped !== 'top' ? halfStroke * signY : 0);
            var borderBottom = bottom + (borderSkipped !== 'bottom' ? -halfStroke * signY : 0);
            // not become a vertical line?
            if (borderLeft !== borderRight) {
                top = borderTop;
                bottom = borderBottom;
            }
            // not become a horizontal line?
            if (borderTop !== borderBottom) {
                left = borderLeft;
                right = borderRight;
            }
        }
        ctx.beginPath();
        ctx.fillStyle = vm.backgroundColor;
        ctx.strokeStyle = vm.borderColor;
        ctx.lineWidth = borderWidth;
        // Corner points, from bottom-left to bottom-right clockwise
        // | 1 2 |
        // | 0 3 |
        var corners = [
            [left, bottom],
            [left, top],
            [right, top],
            [right, bottom]
        ];
        // Find first (starting) corner with fallback to 'bottom'
        var borders = ['bottom', 'left', 'top', 'right'];
        var startCorner = borders.indexOf(borderSkipped, 0);
        if (startCorner === -1) {
            startCorner = 0;
        }

        function cornerAt(index) {
            return corners[(startCorner + index) % 4];
        }
        // Draw rectangle from 'startCorner'
        var corner = cornerAt(0);
        ctx.moveTo(corner[0], corner[1]);
        for (var i = 1; i < 4; i++) {
            corner = cornerAt(i);
            nextCornerId = i + 1;
            if (nextCornerId == 4) {
                nextCornerId = 0
            }
            nextCorner = cornerAt(nextCornerId);
            width = corners[2][0] - corners[1][0];
            height = corners[0][1] - corners[1][1];
            x = corners[1][0];
            y = corners[1][1];
            var radius = cornerRadius;
            // Fix radius being too large
            if (radius > height / 2) {
                radius = height / 2;
            }
            if (radius > width / 2) {
                radius = width / 2;
            }
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
        }
        ctx.fill();
        if (borderWidth) {
            ctx.stroke();
        }
    };
    Chart.pluginService.register({
        beforeDraw: function(chart) {
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
                ctx.font = "12px " + fontStyle;
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
    var baselinePlugin = {
        afterDraw: function(chartInstance) {
            var yScale = chartInstance.scales["y-axis-0"];
            var canvas = chartInstance.chart;
            var ctx = canvas.ctx;
            var index;
            var line;
            var style;
            if (chartInstance.options.baseLine) {
                for (index = 0; index < chartInstance.options.baseLine.length; index++) {
                    line = chartInstance.options.baseLine[index];
                    if (!line.style) {
                        style = "rgba(112, 112, 112, 1)";
                    } else {
                        style = line.style;
                    }
                    if (line.y != undefined) {
                        yValue = yScale.getPixelForValue(line.y);
                    } else {
                        yValue = 0;
                    }
                    ctx.lineWidth = 2;
                    if (yValue) {
                        ctx.beginPath();
                        ctx.moveTo(60, yValue);
                        ctx.lineTo((canvas.width - 20), yValue);
                        ctx.strokeStyle = style;
                        ctx.stroke();
                    }
                    if (line.text) {
                        ctx.fillStyle = style;
                        ctx.fillText(line.text, canvas.width - 125, (yValue + ctx.lineWidth + 15));
                    }
                }
                return;
            };
        }
    };
    Chart.pluginService.register(baselinePlugin);
    Chart.defaults.global.pointHitDetectionRadius = 1;
    Chart.defaults.global.plugins.labels = false;
}
/**
 * This function will return the color code for the specific score.
 *
 */
function getScoreColor($score = 0) {
    var _colorCode;
    if ($score <= 0) {
        _colorCode = companyScoreColorCode.red;
    } else if ($score >= 60 && $score < 80) {
        _colorCode = companyScoreColorCode.yellow;
    } else if ($score >= 80 && $score <= 100) {
        _colorCode = companyScoreColorCode.green;
    } else {
        _colorCode = companyScoreColorCode.red;
    }
    return _colorCode;
}
/*
 * On window resize set equalize heights of each element
 */
$(window).resize(function() {
    if ($('#subCategoryWiseCompanyScoreGraph .score-status').length > 0) {
        var iv;
        if (iv !== null) {
            window.clearTimeout(iv);
        }
        iv = setTimeout(function() {
            equalizeHeights($('#subCategoryWiseCompanyScoreGraph .score-status'));
        }, 120);
    }
});
/*
 * Function for set equalize heights of each element
 */
function equalizeHeights(selector) {
    var heights = new Array();
    $(selector).each(function() {
        $(this).css('min-height', '0');
        $(this).css('max-height', 'none');
        $(this).css('height', 'auto');
        heights.push($(this).height());
    });
    var max = Math.max.apply(Math, heights);
    $(selector).each(function() {
        $(this).css('height', max + 'px');
    });
}
/*
 * Code for displaying departments on company change and loading tab data
 */
$('#company_id, #dtcompany_id').on('change', function(e) {
    $('.page-loader-wrapper').show();
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.change = $(this).attr("id");
    var select = $(this).attr("id");
    var value = $(this).val();
    var deptDependent = $(this).attr('target-data');
    var locDependent = $(this).attr('target-location-data');
    if ($.isNumeric($('#company_id').val()) && ($('#company_id').val() != '' && $('#company_id').val() != null) || ($('#dtcompany_id').val() != '' && $('#dtcompany_id').val() != null)) {
        //Get locations
        var _token = $('input[name="_token"]').val();
        locurl = urls.locUrl.replace(':id', value);
        $.ajax({
            url: locurl,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(result) {
                $('#' + locDependent).empty();
                $('#' + locDependent).select2('destroy').select2();
                $('#' + locDependent).attr('disabled', false);
                $('#' + deptDependent).empty();
                $('#' + deptDependent).select2('destroy').select2();
                $('#' + deptDependent).attr('disabled', false);
                $('#' + locDependent).val('').append('<option value="">Select</option>');
                $.each(result.result, function(key, value) {
                    $('#' + locDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                });
                if (Object.keys(result.result).length == 1) {
                    $.each(result.result, function(key, value) {
                        $('#' + locDependent).select2('val', value.id);
                    });
                }
                loadTabData(options);
            }
        })
    } else {
        $('#' + deptDependent).empty();
        $('#' + deptDependent).select2('destroy').select2();
        $('#' + deptDependent).attr('disabled', true);
        $('#' + locDependent).empty();
        $('#' + locDependent).select2('destroy').select2();
        $('#' + locDependent).attr('disabled', true);
        loadTabData(options);
    }
    if ($.isNumeric(value)) {
        var meditationHoursURL = urls.showMeditationHours.replace(':id', value);
        $.ajax({
            url: meditationHoursURL,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(result) {
                if (result.result.flag == false) {
                    if ($('#behaviour.active').length > 0) {
                        $(`#behaviourTab`).hide();
                        $(`#behaviour`).removeClass('active show');
                        $('#dashboardTabs .main-tabs a[href="#usage"]').tab('show');
                        currActiveTab = 'usage';
                        loadTabData(options);
                    } else {
                        $(`#behaviourTab`).hide();
                        $(`#behaviour`).removeClass('active show');
                    }
                } else {
                    $(`#behaviourTab`).show();
                }
            }
        });
    }
    $('.page-loader-wrapper').hide();
});

$("#company_id, #dtcompany_id").on("select2:unselect", function (e) {
    $(`#behaviourTab`).show();
});
/*
 * Code for displaying departments on company change and loading tab data
 */
$('#industry_id').change(function() {
    $('.page-loader-wrapper').show();
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.change = $(this).attr("id");
    var select = $(this).attr("id");
    var value = $(this).val();
    var comapny = $(this).attr('target-data');
    var deptDependent = $('#company_id').attr('target-data');
    var locDependent = $('#company_id').attr('target-location-data');
    if ($('#industry_id').val() == '') {
        $('#department_id').empty();
        $('#department_id').select2('destroy').select2();
        $('#department_id').attr('disabled', true);
        $('#location_id').empty();
        $('#location_id').select2('destroy').select2();
        $('#location_id').attr('disabled', true);
    }
    var _token = $('input[name="_token"]').val();
    if ($.isNumeric(value)) {
        url = urls.industryCompany.replace(':id', value);
        $.ajax({
            url: url,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(result) {
                $('#' + comapny).empty();
                $('#' + comapny).select2('destroy').select2();
                $('#' + comapny).attr('disabled', false);
                $('#' + comapny).val('').append('<option value="">Select</option>');
                $.each(result.result, function(key, value) {
                    $('#' + comapny).append('<option value="' + value.id + '">' + value.name + '</option>');
                });
                var companyIds = result.result.map(function(obj) {
                    return obj['id'];
                }).join(',');
                $('#companiesId').val(companyIds);
                if (Object.keys(result.result).length == 1) {
                    $.each(result.result, function(key, value) {
                        $('#' + comapny).select2('val', value.id);
                    });
                } else {
                    $('#' + comapny).select2('val', '');
                }
                loadTabData(options);
            }
        });
    }
    $('.page-loader-wrapper').hide();
});
/*
 * Load tab data on department or age change
 */
//======
$('#department_id').change(function() {
    $('.page-loader-wrapper').show();
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.change = $(this).attr("id");
    loadTabData(options);
    $('.page-loader-wrapper').hide();
});
////=================
$('#location_id').change(function() {
    $('.page-loader-wrapper').show();
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.change = $(this).attr("id");
    var select = $(this).attr("id");
    var value = $(this).val();
    var deptDependent = $('#company_id').attr('target-data');
    var _token = $('input[name="_token"]').val();
    url = urls.locDepartmentUrl.replace(':id', value);
    if ($.isNumeric(value) && $('#location_id').val() != '' && $('#location_id').val() != null) {
        $.ajax({
            url: url,
            method: 'get',
            data: {
                _token: _token
            },
            success: function(result) {
                $('#' + deptDependent).empty();
                $('#' + deptDependent).select2('destroy').select2();
                $('#' + deptDependent).attr('disabled', false);
                $('#' + deptDependent).val('').append('<option value="">Select</option>');
                if(result.result !=  undefined){
                    $.each(result.result, function(key, value) {
                        $('#' + deptDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    if (Object.keys(result.result).length == 1) {
                        $.each(result.result, function(key, value) {
                            $('#' + deptDependent).select2('val', value.id);
                        });
                    }
                }
                loadTabData(options);
            }
        })
    } else {
        $('#department_id').empty();
        $('#department_id').select2('destroy').select2();
        $('#department_id').attr('disabled', true);
        loadTabData(options);
    }
    $('.page-loader-wrapper').hide();
});
$('.age').change(function() {
    var age = $(this).val();
    $('.page-loader-wrapper').show();
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.change = $(this).attr("id");
    loadTabData(options);
    $('.page-loader-wrapper').hide();
});
$('#service_id').change(function() {
    var age = $(this).val();
    $('.page-loader-wrapper').show();
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.change = $(this).attr("id");
    loadTabData(options);
    $('.page-loader-wrapper').hide();
});
/*
 * Code for displaying departments on company change and loading tab data
 */
$('#dtcompany_id').change(function() {
    $('.page-loader-wrapper').show();
    if (typeof options == 'undefined') {
        options = new Object();
    }
    options.change = $(this).attr("id");
    var select = $(this).attr("id");
    var value = $(this).val();
    loadTabData(options);
    $('.page-loader-wrapper').hide();
});
/*
 * Load graph data on date range filter change
 */
// $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
//     var tier = $(this).data('tier');
//     var id = $(this).attr('id');
//     switch (currActiveTab) {
//         case 'appUsage':
//             if (typeof options == 'undefined') {
//                 options = new Object();
//             }
//             options.change = id;
//             switch (id) {
//                 case 'daterangeStepsPeriod':
//                     options.fromDateStepsPeriod = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateStepsPeriod = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//                 case 'daterangeCaloriesPeriod':
//                     options.fromDateCaloriesPeriod = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateCaloriesPeriod = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//                 case 'daterangeSuperstars':
//                     options.fromDateSuperstars = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateSuperstars = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//             }
//             appUsageTabAjaxCall(tier, options);
//             break;
//         case 'physical':
//             if (typeof options == 'undefined') {
//                 options = new Object();
//             }
//             options.change = id;
//             switch (id) {
//                 case 'daterangeHsPhysical':
//                     options.fromDateHsPhysical = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateHsPhysical = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//                 case 'daterangeExerciseRanges':
//                     options.fromDateExerciseRanges = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateExerciseRanges = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//                 case 'daterangeStepRanges':
//                     options.fromDateStepRanges = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateStepRanges = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//             }
//             physicalTabAjaxCall(tier, options);
//             break;
//         case 'psychological':
//             if (typeof options == 'undefined') {
//                 options = new Object();
//             }
//             options.change = id;
//             switch (id) {
//                 case 'daterangeHsPsychological':
//                     options.fromDateHsPsychological = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateHsPsychological = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//                 case 'daterangeTopMeditationTracks':
//                     options.fromDateTopMeditationTracks = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateTopMeditationTracks = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//             }
//             psychologicalTabAjaxCall(tier, options);
//             break;
//         case 'audit':
//             var diffInWeek = picker.endDate.diff(picker.startDate, 'week');
//             if (diffInWeek > 26) {
//                 toastr.error('Date range should not be greater than 26 weeks.');
//                 $(this).data('daterangepicker').setStartDate(moment().subtract(30, 'days'));
//                 $(this).data('daterangepicker').setEndDate(moment());
//                 return false;
//             }
//             options = ((typeof options == 'undefined') ? new Object() : options);
//             options.change = id;
//             switch (id) {
//                 case 'daterangeAuditCompanyScore':
//                     options.fromDateCompanyScore = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateCompanyScore = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//                 case 'daterangeAuditCategoryWiseCompanyScore':
//                     options.category_id = ($('#audit_category_wise_company_score_tab .selected .item').data('id') || 0);
//                     options.fromDateCategoryCompanyScore = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
//                     options.endDateCategoryCompanyScore = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
//                     break;
//             }
//             auditTabAjaxCall(tier, options);
//             break;
//     }
// });
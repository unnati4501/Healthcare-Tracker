$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#categoryscoreCarousel').owlCarousel({
        navText: ["<i class='far fa-long-arrow-left'></i>", "<i class='far fa-long-arrow-right'></i>"],
        loop: false,
        margin: 10,
        nav: true,
        dots: false,
        responsive: {
            0: {
                items: 2
            },
            500: {
                items: 3
            },
            767: {
                items: 3
            },
            1000: {
                items: 5
            },
            1700: {
                items: 8
            }
        }
    });
    surveyCharts.mainChart = new Gauge($('#surveyChart')[0]).setOptions(gaugeChartOptions);
    surveyCharts.mainChart.setMinValue(0);
    surveyCharts.mainChart.maxValue = 100;
    surveyCharts.mainChart.options.colorStart = mainChartColorCode;
    surveyCharts.mainChart.set(mainChartPercentage);
    if ($('.surveyCategoryChart').length > 0) {
        $('.surveyCategoryChart').each(function(index, canvas) {
            surveyCharts.subcharts[index] = new Gauge($(canvas)[0]).setOptions(gaugeChartOptions);
            surveyCharts.subcharts[index].setMinValue(0);
            surveyCharts.subcharts[index].maxValue = 100;
            surveyCharts.subcharts[index].options.colorStart = $(canvas).data('color-code');
            surveyCharts.subcharts[index].set($(canvas).data('value'));
        });
    }
    if ($('.surveyCategoryQuestions').length > 0) {
        $('.surveyCategoryQuestions').each(function(index, table) {
            var _id = $(table).data('id');
            datatables[_id] = $(`#surveyCategoryQuestionsManagement${_id}`).DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: urls.surveyQs.replace(":category_id", _id),
                    data: {
                        company_id: $('#company_id').val(),
                        publish_date: $('#publish_date').val(),
                        expiry_date: $('#expiry_date').val(),
                        getQueryString: window.location.search
                    },
                },
                columns: [{
                    data: 'id',
                    name: 'id',
                    searchable: false,
                    sortable: false,
                    class: 'text-center',
                }, {
                    data: 'question_type',
                    name: 'question_type',
                }, {
                    data: 'question',
                    name: 'question',
                }, {
                    data: 'category_name',
                    name: 'category_name',
                }, {
                    data: 'sub_category_name',
                    name: 'sub_category_name',
                }, {
                    data: 'responses',
                    name: 'responses',
                    class: 'text-center'
                }, {
                    data: 'options',
                    name: 'options',
                    class: 'text-center'
                }, {
                    data: 'percentage',
                    name: 'percentage',
                    class: 'text-center',
                    render: function(data, type, row) {
                        return ((row.percentage == null) ? 0 : row.percentage) + '%';
                    }
                }, {
                    data: 'actions',
                    name: 'actions',
                    searchable: false,
                    sortable: false,
                    class: 'text-center',
                }],
                paging: true,
                pageLength: pagination.value,
                lengthChange: false,
                searching: false,
                ordering: true,
                order: [],
                info: true,
                autoWidth: false,
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false,
                }],
                dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
                rowCallback: function(row, data, displayNum, displayIndex, dataIndex) {
                    var pageInfo = datatables[_id].page.info();
                    $("td:eq(0)", row).html(((pageInfo.page) * pageInfo.length) + displayIndex + 1);
                    return row;
                },
                language: {
                    paginate: {
                        previous: pagination.previous,
                        next: pagination.next,
                    }
                },
            });
        });
    }
    $(document).on('click', '#questionShow', function(e) {
        var questionId = $(this).data('id');
        $.ajax({
            type: 'GET',
            url: urls.question.replace(':id', questionId),
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function(questionTextData) {
                var activeQuestion = 1;
                $('#userQuestionFreeText').html(null);
                $('#singleQuestionPreviewModalChoiceHtml').html(null);
                if (questionTextData.status == 1) {
                    if (questionTextData.question_type == 'free-text') {
                        if (questionTextData["question_image_src"][activeQuestion].includes('73a90acaae2b1ccc0e969709665bc62f')) {
                            questionTextData["question_image_src"][activeQuestion] = '';
                        }
                        var htmlElements = '<div class="row align-items-center"><div class="col-lg-12 text-center">\n' + '<div class="ans-main-area question-type-one m-0-a">\n' + '<div class="text-center">\n' + '<h2 class="question-text">' + questionTextData["question"][activeQuestion] + '</h2></div>\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + questionTextData["question_image_src"][activeQuestion] + '" alt="">\n' + '</div>\n' + '<div class="form-group ans-textarea">\n' + '<textarea class="form-control animated flash slow h-auto" id="" rows="5"></textarea>\n' + '</div>\n' + '</div>\n' + '</div>\n' + '</div>';
                        $('#userQuestionFreeText').append(htmlElements);
                        $('#userQuestionFreeTextModelBox').modal('show');
                    } else {
                        var dynamicHtml = '';
                        for (var i in questionTextData.image[activeQuestion]) {
                            if (questionTextData.image[activeQuestion][i]["imageSrc"].includes('73a90acaae2b1ccc0e969709665bc62f')) {
                                questionTextData.image[activeQuestion][i]["imageSrc"] = '';
                            }
                            if (questionTextData.image[activeQuestion][i]["imageSrc"] != '' && questionTextData.image[activeQuestion][i]["imageSrc"] != undefined) {
                                var imageLink = questionTextData.image[activeQuestion][i]["imageSrc"];
                            } else {
                                var imageLink = '';
                            }
                            if (questionTextData.choice[activeQuestion][i] != '' && questionTextData.choice[activeQuestion][i] != undefined) {
                                var imageTitle = questionTextData.choice[activeQuestion][i];
                            } else {
                                var imageTitle = '';
                            }
                            dynamicHtml += '<!-- item-box -->' + '<label class="choices-item-box">\n' + '    <input type="radio" name="choices">\n' + '    <div class="markarea">\n' + '        <span class="checkmark animated tada faste"></span>\n' + '        <div class="choices-item-img">\n' + '            <img class="" src="' + imageLink + '" alt="">\n' + '        </div>\n' + '    </div>\n' + '    <div class="choices-box-title">' + imageTitle + '</div>\n' + '</label>\n' + '<!-- item-box /-->';
                        }
                        if (questionTextData["question_image_src"][activeQuestion].includes('73a90acaae2b1ccc0e969709665bc62f')) {
                            questionTextData["question_image_src"][activeQuestion] = '';
                        }
                        var singlePreviewHtml = '<div class="row align-items-center"><div class="col-lg-12 text-center">' + '    <div class="ans-main-area question-type-one m-0-a">\n' + ' <div class="text-center">\n' + '        <h2 class="question-text">' + questionTextData.question[activeQuestion] + '</h2></div>\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + questionTextData["question_image_src"][activeQuestion] + '" alt="">\n' + '</div>\n' + '        <div class="animated flash slow choices-main-box">\n' + dynamicHtml + '        </div>\n' + '    </div>\n' + '</div>\n' + '</div>';
                        $('#singleQuestionPreviewModalChoiceHtml').append(singlePreviewHtml);
                        $('#singleQuestionPreviewModalChoice').modal('show');
                    }
                } else {
                    toastr.error(questionTextData.message);
                }
            },
            error: function(data) {
                toastr.error(data.statusText);
            }
        });
    });
});
var surveyquestionsId = [];

function loadQuestions() {
    datatables.allquestions.draw();
}

function findMaxValueOfKeyFromArray(arr, key) {
    return Math.max.apply(Math, arr.map(function(element) {
        return element.order_priority;
    }));
}

function loadFinalQuestions() {
    var questions = datatables.surveyquestions.rows().data();
    datatables.finalQuestionList.clear().rows.add(questions).draw();
}

function loadFinalQuestionsHtml() {
    return new Promise((resolve, reject) => {
        var questions = datatables.surveyquestions.rows().data(),
            html = "";
        $(questions).each(function(key, question) {
            var template = $('#question_data_block_template').text().trim();
            html += template.replace(/\:id/g, parseInt(question.id)).replace("val:question_id", parseInt(question.id)).replace("val:category", parseInt(question.category_id)).replace("val:subcategory", parseInt(question.sub_category_id)).replace("val:questions_type", parseInt(question.question_type_id));
        });
        $('#survey_questions_data_block').html(html.trim());
        $('#is_premium').val(((datatables.surveyquestions.rows(`[data-is-premium-q="1"]`).any()) ? "1" : "0"));
        resolve(true);
    });
}
$(document).ready(function() {
    $surveyAddForm.validate();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: url._getQuestions.replace(":id", surveyId),
        type: 'GET',
        dataType: 'json',
    }).done(function(data) {
        datatables.surveyquestions.rows.add(data.data).draw();
    }).fail(function(error) {}).always(function() {
        var surveyquestionsData = datatables.surveyquestions.rows().data();
        orderQ = findMaxValueOfKeyFromArray(surveyquestionsData, 'order_priority');
        surveyquestionsId = surveyquestionsData.pluck('id');
        datatables.allquestions = $('#allquestions').DataTable({
            processing: true,
            serverSide: true,
            bInfo: false,
            bFilter: false,
            lengthChange: false,
            pageLength: pagination,
            stateSave: false,
            oLanguage: {
                sEmptyTable: "No questions available.",
                processing: "Loading questions...",
            },
            ajax: {
                url: url._getQuestions.replace(":id", ""),
                data: function(data) {
                    data.question_category = $('#question_category').val();
                    data.question_subcategory = $('#question_subcategory').val();
                    data.question_search = $('#question_search').val();
                }
            },
            columns: [{
                data: 'checkbox',
                name: 'checkbox'
            }, {
                data: 'id',
                name: 'id'
            }, {
                data: 'is_premium',
                name: 'is_premium'
            }, {
                data: 'id',
                name: 'id',
                visible: false
            }, {
                data: 'title',
                name: 'title'
            }, {
                data: 'category_name',
                name: 'category_name'
            }, {
                data: 'subcategory_name',
                name: 'subcategory_name'
            }],
            order: [],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
            rowCallback: function(row, data, displayNum, displayIndex, dataIndex) {
                var pageInfo = datatables.allquestions.page.info();
                $("td:eq(1)", row).html(((pageInfo.page) * pageInfo.length) + displayIndex + 1);
                $(row).addClass('question-row').attr({
                    'data-id': data.id,
                    'data-is-premium-q': data.subcat_is_primum,
                });
                // if (datatables.surveyquestions.rows("[data-id=\"".concat(data.id, "\"]")).any()) {
                if ($.inArray(data.id, surveyquestionsId) >= 0) {
                    $(row).addClass('disabled');
                }
                return row;
            },
            preDrawCallback: function(settings, json) {
                $('#check-all-questions').parents('tr').removeClass('selected');
                if (settings.jqXHR && settings.jqXHR.hasOwnProperty('readyState')) {
                    settings.jqXHR.abort();
                }
            },
            drawCallback: function(settings) {
                $('#allquestions').parents('.table-responsive').mCustomScrollbar({
                    axis: "x",
                    theme: "inset-dark",
                });
            }
        });
    });
    stepObj = $("#serverAddStep").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "fade",
        autoFocus: true,
        enableCancelButton: true,
        startIndex: 0,
        labels: {
            next: buttons.next,
            previous: buttons.previous,
            finish: buttons.finish,
            cancel: buttons.cancel,
        },
        onStepChanging: function(event, currentIndex, newIndex) {
            var stepIsValid = true,
                validator = $surveyAddForm.validate();
            if (currentIndex == 1 && newIndex == 0) {
                return true;
            }
            $(':input', `[data-step="${currentIndex}"]`).each(function() {
                var xy = validator.element(this);
                stepIsValid = stepIsValid && (typeof xy == 'undefined' || xy);
            });
            if (!stepIsValid) {
                if ($(".is-invalid").length > 0) {
                    $('html, body').animate({
                        scrollTop: $(".is-invalid").first().offset().top - 100
                    }, 500);
                }
            }
            if (currentIndex == 0 && newIndex == 1) {
                showPageLoaderWithMessage('Validating...');
                setTimeout(function() {
                    if (!$('#title').valid()) {
                        stepObj.steps("previous");
                        hidesPageLoader();
                    } else {
                        hidesPageLoader();
                    }
                }, 1000);
            } else if (currentIndex == 1 && newIndex == 2) {
                stepIsValid = (datatables.surveyquestions.rows().data().length > 0);
                if (!stepIsValid) {
                    $('#err-box #err-box-message').html('Please select questions from the list and add to the survey.');
                    $('#err-box').modal('show');
                } else {
                    $('#preview_title').html($('#title').val());
                    $('#preview_description').html($('#description').val());
                    loadFinalQuestions();
                }
            }
            return stepIsValid;
        },
        onStepChanged: function(event, currentIndex, priorIndex) {
            $(`.step-${currentIndex+1} select.select2`).each(function(i, obj) {
                if (!$(obj).data('select2')) {
                    $(obj).select2({
                        width: "100%"
                    });
                }
            });
        },
        onCanceled: function(event) {
            var currStep = stepObj.steps("getCurrentIndex");
            if (currStep > 0) {
                $('#leave-survey-box').modal('show');
            } else {
                window.location.href = url._cancelURL;
            }
        },
        onFinishing: async function(event, currentIndex) {
            if (datatables.surveyquestions.rows().data().length <= 0) {
                $('#err-box #err-box-message').html('Survey should have at least one question.');
                $('#err-box').modal('show');
                return false;
            } else {
                loadFinalQuestionsHtml().then((data) => {
                    return true;
                });
            }
        },
        onFinished: function(event, currentIndex) {
            $surveyAddForm.submit();
        },
    });
    datatables.surveyquestions = $('#surveyquestions').DataTable({
        bInfo: false,
        bFilter: false,
        lengthChange: false,
        pageLength: pagination,
        info: true,
        autoWidth: false,
        stateSave: false,
        oLanguage: {
            sEmptyTable: "Please select questions from the list and add to the survey.",
        },
        lengthChange: false,
        columns: [{
            data: 'checkbox',
            name: 'checkbox'
        }, {
            data: 'id',
            name: 'id'
        }, {
            data: 'order_priority',
            name: 'order_priority',
            visible: false,
            /*render: function(data, type, row) {
                orderQ++;
                return (data || orderQ);
            }*/
        }, {
            data: 'is_premium',
            name: 'is_premium'
        }, {
            data: 'id',
            name: 'id',
            visible: false
        }, {
            data: 'title',
            name: 'title'
        }, {
            data: 'category_name',
            name: 'category_name'
        }, {
            data: 'subcategory_name',
            name: 'subcategory_name'
        }],
        order: [
            [2, 'asc']
        ],
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        rowCallback: function(row, data, displayNum, displayIndex, dataIndex) {
            surveyquestionsId = datatables.surveyquestions.rows().data().pluck('id');
            var pageInfo = datatables.surveyquestions.page.info();
            $("td:eq(1)", row).html(displayIndex + 1);
            $(row).addClass('question-row').attr({
                'data-id': data.id,
                'data-is-premium-q': data.subcat_is_primum,
            });
            return row;
        },
        drawCallback: function(settings) {
            $('#surveyquestions').parents('.table-responsive').mCustomScrollbar({
                axis: "x",
                theme: "inset-dark",
            });
            $('#check-all-survey-questions').parents('tr').removeClass('selected');
            $('#surveyquestions tbody tr.question-row').not('tr.disabled').removeClass('selected');
        }
    });
    datatables.finalQuestionList = $('#finalQuestionList').DataTable({
        bInfo: false,
        bFilter: false,
        lengthChange: false,
        pageLength: (zsconfig.maxQuestions > 0 ? zsconfig.maxQuestions : 50),
        info: true,
        autoWidth: false,
        stateSave: false,
        oLanguage: {
            sEmptyTable: "Please select questions from the list and add to the survey.",
        },
        columns: [{
            data: 'id',
            name: 'id',
            className: 'no-sort allow-reorder',
        }, {
            data: 'order_priority',
            name: 'order_priority',
            visible: false,
            className: 'allow-reorder',
        }, {
            data: 'is_premium',
            name: 'is_premium',
            className: 'no-sort allow-reorder',
        }, {
            data: 'title',
            name: 'title',
            className: 'no-sort allow-reorder',
        }, {
            data: 'category_name',
            name: 'category_name',
            className: 'no-sort allow-reorder',
        }, {
            data: 'subcategory_name',
            name: 'subcategory_name',
            className: 'no-sort allow-reorder',
        }, {
            data: 'questiontype_name',
            name: 'questiontype_name',
            className: 'no-sort allow-reorder',
        }, {
            data: 'created_at',
            name: 'created_at',
            className: 'no-sort allow-reorder',
            render: function(data, type, row) {
                return ((row.question_created_at) ? moment.utc(row.question_created_at).tz(timezone).format(date_format) : moment().tz(timezone).format(date_format));
            }
        }, {
            data: 'action',
            name: 'action',
            className: 'no-sort text-center',
            render: function(data, type, row) {
                return `<a class="action-icon preview_question" href="javascript:void(0);" title="View" data-id="${row.id}">
                    <i class="far fa-eye">
                    </i>
                </a>
                <a class="action-icon delete_question danger" href="javascript:void(0);" title="Delete" data-id="${row.id}">
                    <i class="far fa-trash-alt">
                    </i>
                </a>`;
            }
        }],
        order: [
            [1, 'asc']
        ],
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        ordering: true,
        rowReorder: {
            enable: true,
            update: false,
            selector: '.allow-reorder',
            responsive: true
        },
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        },
        rowCallback: function(row, data, displayNum, displayIndex, dataIndex) {
            var pageInfo = datatables.finalQuestionList.page.info();
            $("td:eq(0)", row).html(displayIndex + 1);
            $(row).addClass('question-row').attr({
                'data-id': data.id,
                'data-is-premium-q': data.subcat_is_primum,
            });
            return row;
        }
    });
    datatables.finalQuestionList.on('row-reorder', function(e, movedElements, element) {
        datatables.finalQuestionList.rowReorder.disable();
        $(movedElements).each(function(index, movedElement) {
            var rowData = datatables.finalQuestionList.row(movedElement.node).data(),
                surveyQuestionObj = {};
            datatables.surveyquestions.rows(function(idx, data, node) {
                if (data.id == rowData.id) {
                    surveyQuestionObj.data = data;
                    surveyQuestionObj.node = node;
                    return true;
                }
            });
            rowData.order_priority = movedElement.newPosition + 1;
            surveyQuestionObj.data.order_priority = movedElement.newPosition + 1;
            datatables.surveyquestions.row(surveyQuestionObj.node).data(surveyQuestionObj.data);
            datatables.finalQuestionList.row(movedElement.node).data(rowData);
        });
        datatables.finalQuestionList.order([1, 'asc']).draw();
        datatables.surveyquestions.order([2, 'asc']).draw();
        datatables.finalQuestionList.rowReorder.enable();
    });
    $(document).on('click', '#leave-survey-confirm', function(e) {
        window.location.href = url._cancelURL;
    });
    $(document).on('change', '#question_category', function(e) {
        var _value = ($(this).val() || "");
        if (_value != "") {
            xhr._getSubCategoriesXHR = $.ajax({
                url: url._getSubCategoriesUrl.replace(":id", _value),
                type: 'GET',
                dataType: 'html',
                beforeSend: function() {
                    if (xhr._getSubCategoriesXHR != null && xhr._getSubCategoriesXHR.readyState < 4) {
                        xhr._getSubCategoriesXHR.abort();
                    }
                },
            }).done(function(data) {
                $('#question_subcategory').empty().append(data).val('');
                loadQuestions();
            }).fail(function(error) {
                $('#question_subcategory').empty().val('');
                alert('Please try again! Failed to load subcategories.');
            }).always(function() {
                $('#question_subcategory').select2('destroy').select2();
            });
        } else {
            $('#question_subcategory').empty().val('');
            $('#question_subcategory').select2('destroy').select2();
            loadQuestions();
        }
    });
    $(document).on('change', '#question_subcategory', function(e) {
        loadQuestions();
    });
    $('#question_search').keyup(function(event) {
        loadQuestions();
    });
    $('#allquestions').on('click', 'tbody tr td:not(.dataTables_empty)', function(e) {
        $(this).parent().toggleClass('selected');
    });
    $('#surveyquestions').on('click', 'tbody tr td:not(.dataTables_empty)', function(e) {
        $(this).parent().toggleClass('selected');
    });
    $(document).on('click', '#check-all-questions', function(e) {
        if (!$(this).parents('tr').hasClass('selected')) {
            $(this).parents('tr').addClass('selected');
            $('#allquestions tbody tr.question-row').not('tr.disabled').addClass('selected');
        } else {
            $(this).parents('tr').removeClass('selected');
            $('#allquestions tbody tr.question-row').not('tr.disabled').removeClass('selected');
        }
    });
    $(document).on('click', '#check-all-survey-questions', function(e) {
        if (!$(this).parents('tr').hasClass('selected')) {
            $(this).parents('tr').addClass('selected');
            $('#surveyquestions tbody tr.question-row').not('tr.disabled').addClass('selected');
        } else {
            $(this).parents('tr').removeClass('selected');
            $('#surveyquestions tbody tr.question-row').not('tr.disabled').removeClass('selected');
        }
    });
    $(document).on('click', '#addToSurveyQuestions', function(e) {
        var $rows = $('#allquestions').find("tbody tr.selected"),
            addRows = [];
        if ($rows.length > 0) {
            if ((datatables.surveyquestions.data().count() + $rows.length) > zsconfig.maxQuestions) {
                toastr.clear();
                toastr.error(`Max ${zsconfig.maxQuestions} questions are allowed for the survey.`);
                return false;
            }
            $.each($rows, function(key, row) {
                if (row !== null) {
                    orderQ++;
                    var rowData = datatables.allquestions.row(row).data();
                    rowData.order_priority = orderQ;
                    addRows.push(rowData);
                    $(row).addClass('disabled').removeClass('selected');
                }
            });
            datatables.surveyquestions.rows.add(addRows).draw().page('last').draw('page');
            $('#check-all-questions').parents('tr').removeClass('selected');
        } else {
            toastr.clear();
            toastr.error('Please select question(s) to add!');
        }
    });
    $(document).on('click', '#removeFromSurveyQuestions', function(e) {
        var $rows = $('#surveyquestions').find("tbody tr.selected");
        if ($rows.length > 0) {
            $.each($rows, function(key, row) {
                if (row !== null) {
                    datatables.surveyquestions.row(row).remove();
                    var id = (row.dataset.id || 0);
                    if ($(`#allquestions tr[data-id="${id}"]`).length > 0) {
                        $(`#allquestions tr[data-id="${id}"]`).removeClass('disabled');
                    }
                }
            });
            datatables.surveyquestions.draw(false);
            $('#check-all-survey-questions').parents('tr').removeClass('selected');
        } else {
            toastr.clear();
            toastr.error('Please select question(s) to remove!');
        }
    });
    $(document).on('click', '.delete_question', function(e) {
        var _id = ($(this).data('id') || 0),
            totalQs = datatables.surveyquestions.rows().data().length;
        if (totalQs > 1) {
            if (_id > 0) {
                $('#remove-question-box').data('id', _id);
                $('#remove-question-box').modal('show');
            }
        } else {
            $('#err-box #err-box-message').html('Survey should have at least one question.');
            $('#err-box').modal('show');
        }
    });
    $(document).on('click', '#remove-question-confirm', function(e) {
        var _id = ($('#remove-question-box').data('id') || 0);
        if (_id > 0) {
            var row = datatables.surveyquestions.rows(`[data-id="${_id}"]`);
            if (row.any()) {
                if ($(`#allquestions tr[data-id="${_id}"]`).length > 0) {
                    $(`#allquestions tr[data-id="${_id}"]`).removeClass('disabled');
                }
                row.remove();
                datatables.surveyquestions.draw(false);
                loadFinalQuestions();
            }
        }
        $('#remove-question-box').modal('hide');
    });
    $(document).on('hidden.bs.modal', '#remove-question-box', function(e) {
        $('#remove-question-box').data('id', 0);
    });
    $(document).on('click', '.preview_question', function(e) {
        e.preventDefault();
        var questionId = ($(this).data('id') || 0);
        if (questionId > 0) {
            $.ajax({
                type: 'GET',
                url: url._getSingleQuestion.replace(":id", questionId),
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
                            var htmlElements = '<div class="row align-items-center"><div class="col-lg-12 align-self-center text-center">\n' + '<div class="ans-main-area question-type-one m-0-a">\n' + '<div class="text-center">\n' + '<h2 class="question-text">' + questionTextData["question"][activeQuestion] + '</h2></div>\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + questionTextData["question_image_src"][activeQuestion] + '" alt="">\n' + '</div>\n' + '<div class="form-group ans-textarea">\n' + '<textarea class="form-control animated flash slow h-auto" id="" rows="5"></textarea>\n' + '</div>\n' + '</div>\n' + '</div>\n' + '</div>';
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
                            var singlePreviewHtml = '<div class="row align-items-center"><div class="col-lg-12 align-self-center text-center">' + '    <div class="ans-main-area question-type-one m-0-a">\n' + ' <div class="text-center">\n' + '        <h2 class="question-text">' + questionTextData.question[activeQuestion] + '</h2></div>\n' + '<div class="text-center w-100 mb-3">\n' + '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + questionTextData["question_image_src"][activeQuestion] + '" alt="">\n' + '</div>\n' + '        <div class="animated flash slow choices-main-box">\n' + dynamicHtml + '        </div>\n' + '    </div>\n' + '</div>\n' + '</div>';
                            $('#singleQuestionPreviewModalChoiceHtml').append(singlePreviewHtml);
                            $('#singleQuestionPreviewModalChoice').modal('show');
                        }
                    } else {
                        toastr.error(questionTextData.message);
                    }
                },
                error: function(data) {
                    console.log(data);
                    toastr.error(data.statusText);
                }
            });
        }
    });
});
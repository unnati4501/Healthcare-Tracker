@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
{{--
<link href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700&display=swap?var=<?= rand() ?>" rel="stylesheet"/>
--}}
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcquestionbank.breadcrumb', [
  'mainTitle' => trans('survey.zcquestionbank.title.index'),
  'breadcrumb' => Breadcrumbs::render('zcquestionbank.index'),
  'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- search-block -->
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('buttons.general.filter') }}
                </h4>
                {{ Form::open(['route' => 'admin.zcquestionbank.index', 'class' => 'form-horizontal', 'method' => 'get', 'category' => 'form', 'id' => 'zcquestionbankSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('category', $categories, request()->get('category') , ['class' => 'form-control select2','id' => 'category', 'placeholder' => 'Select Category', 'data-placeholder' => 'Select Category', 'target-data' => 'subcategories']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('subcategories', ($subcategories ?? []), request()->get('subcategories') , ['class' => 'form-control select2', 'placeholder' => 'Select Subcategory', 'data-placeholder' => 'Select Subcategory', 'id' => 'subcategories', 'disabled' => true]) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('question', request()->get('question'), ['class' => 'form-control', 'placeholder' => 'Enter Question', 'data-placeholder' => 'Enter Question','id' => 'question']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('question_type', $questionTypes, request()->get('question_type'), ['class' => 'form-control select2', 'id' => 'question_type', 'placeholder' => 'Select Question Type', 'data-placeholder' => 'Select Question Type']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('question_status', config('zevolifesettings.zcQuestionStatus'), request()->get('question_status'), ['class' => 'form-control select2','id' => 'question_status', 'placeholder' => 'Select Question Status', 'data-placeholder' => 'Select Question Status']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('with_image', ['yes'=>'Yes', 'no'=>'No'], request()->get('with_image') , ['class' => 'form-control select2','id' => 'with_image', 'placeholder' => 'Select Question has image', 'data-placeholder' => 'Select Question has image']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.zcquestionbank.index') }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{ trans('buttons.general.reset') }}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <a class="btn btn-primary filter-btn" href="javascript:void(0);">
            <i class="far fa-filter me-2 align-middle">
            </i>
            <span class="align-middle">
                {{ trans('buttons.general.filter') }}
            </span>
        </a>
        <!-- /.search-block -->
        <!-- grid -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="questionManagment">
                            <thead>
                                <tr>
                                    <th class="text-center d-none">
                                        Updated At
                                    </th>
                                    <th class="no-sorting-arrow th-btn-sm">
                                        Sr no
                                    </th>
                                    <th>
                                        Category
                                    </th>
                                    <th>
                                        Sub category
                                    </th>
                                    <th>
                                        Questions
                                    </th>
                                    <th class="no-sort">
                                        Images
                                    </th>
                                    <th>
                                        Question Type
                                    </th>
                                    <th class="no-sort th-btn-2">
                                        Status
                                    </th>
                                    <th class="th-btn-3 no-sort">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.grid -->
    </div>
</section>
<div class="modal fade" data-id="0" id="publish-question-modal-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0" id="modal-message">
                </p>
            </div>
            <div class="modal-footer" id="modal-footer">
            </div>
        </div>
    </div>
</div>
<div category="dialog" class="modal fade" data-id="0" id="delete-model-box" tabindex="-1">
    <div category="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Delete Question?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    Are you sure you want to delete this Question?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="delete-model-box-confirm" type="button">
                    {{ trans('buttons.general.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
@include('admin.zcquestionbank.common.free-text.modal-box')
@include('admin.zcquestionbank.common.choice.modal-box')
@endsection
@section('after-scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function rangeColorSliderImg(activeQuestion, slidingbarData, previewType) {
        var rangColorImgUrl = new Array();
        var colorRange = slidingbarData.choice[activeQuestion];
        var rangColor = ['range-color1', 'range-color2', 'range-color3', 'range-color4', 'range-color5',];

        var rangColorChange = new Array();
        var colorIndex = 1

        for (var color in colorRange) {
            var currentArray = new Array();
            if (color == 1) {
                currentArray.push('#' + colorRange[color]);
                currentArray.push('#' + colorRange[color]);
            } else {
                currentArray.push('#' + colorRange[1]);
                currentArray.push('#' + colorRange[color]);
            }
            // rangColorChange.push(currentArray);
            rangColorChange[colorIndex] = currentArray;
            colorIndex++;
        }
        window.setTimeout(function () {
            $("#rangeColorSlider-" + previewType + activeQuestion).ionRangeSlider({
                skin: "round",
                min: 1,
                max: 5,
                from: 3,
                step: 0.01,
                grid: false,
                grid_num: 4,
                grid_snap: false,
                extra_classes: "extra-class",
                onStart: function (data) {
                    var dataFrom = this.renderChange(data.from);
                    $('#rangeColorSliderImg-' + previewType + activeQuestion).prop("src", rangColorImgUrl[dataFrom]);
                    $('#rangeColorSliderImg-' + previewType + activeQuestion).prop("class", rangColor[dataFrom]);

                    $('.range-color-slider-custom-' + previewType + activeQuestion + ' .irs-bar').css("background", 'linear-gradient(to right,' + rangColorChange[dataFrom][0] + ' 0%, ' + rangColorChange[dataFrom][1] + ' 100%)');
                    $('.range-color-slider-custom-' + previewType + activeQuestion + ' .irs-handle').css({
                        "background": rangColorChange[dataFrom][1],
                        "box-shadow": "0 0 0px 1px " + rangColorChange[dataFrom][1]
                    });
                    var cvb = $('.range-color-slider-custom-' + previewType + activeQuestion + ' .irs-handle');
                    left_align_data = cvb[0].style.left;
                    document.getElementById('rangeColorSliderImg-' + previewType + activeQuestion).style.left = left_align_data;
                },
                onChange: function (data) {

                    var targetAdmimationId = 'slidingbar-option-animation-' + activeQuestion;
                    var activeArea = 1;
                    var currentPossition = data.from;
                    if (currentPossition >= 1 && currentPossition <= 1.50) {
                        activeArea = 1;
                    } else if (currentPossition >= 1.51 && currentPossition <= 2.50) {
                        activeArea = 2;
                    } else if (currentPossition >= 2.51 && currentPossition <= 3.50) {
                        activeArea = 3
                    } else if (currentPossition >= 3.51 && currentPossition <= 4.50) {
                        activeArea = 4;
                    } else if (currentPossition >= 4.51 && currentPossition <= 5.50) {
                        activeArea = 5;
                    }
                    var animationCalssForOptions = 'animated bounceIn slow';
                    for (var ii = 1; ii <= 5; ii++) {
                        if (activeArea != ii) {
                            $('#' + targetAdmimationId + ii).removeClass(animationCalssForOptions);
                        }
                    }
                    $('#' + targetAdmimationId + activeArea).addClass(animationCalssForOptions);


                    var dataFrom = this.renderChange(data.from);
                    $('#rangeColorSliderImg-' + previewType + activeQuestion).prop("src", rangColorImgUrl[dataFrom]);
                    $('#rangeColorSliderImg-' + previewType + activeQuestion).prop("class", rangColor[dataFrom]);

                    $('.range-color-slider-custom-' + previewType + activeQuestion + ' .irs-bar').css("background", 'linear-gradient(to right,' + rangColorChange[dataFrom][0] + ' 0%, ' + rangColorChange[dataFrom][1] + ' 100%)');
                    $('.range-color-slider-custom-' + previewType + activeQuestion + ' .irs-handle').css({
                        "background": rangColorChange[dataFrom][1],
                        "box-shadow": "0 0 0px 1px " + rangColorChange[dataFrom][1]
                    });

                    var cvb = $('.range-color-slider-custom-' + previewType + activeQuestion + ' .irs-handle');
                    left_align_data = cvb[0].style.left;
                    document.getElementById('rangeColorSliderImg-' + previewType + activeQuestion).style.left = left_align_data;

                },
                onFinish: function (data) {
                    var slider = $("#rangeColorSlider-" + previewType + activeQuestion).data("ionRangeSlider");
                    var targetAdmimationId = 'slidingbar-option-animation-' + activeQuestion;
                    var activeArea = 1;
                    var currentPossition = data.from;
                    if (currentPossition >= 1 && currentPossition <= 1.50) {
                        slider.update({from: 1, to: 1});
                        activeArea = 1;
                    } else if (currentPossition >= 1.51 && currentPossition <= 2.50) {
                        slider.update({from: 2, to: 2});
                        activeArea = 2;
                    } else if (currentPossition >= 2.51 && currentPossition <= 3.50) {
                        slider.update({from: 3, to: 3});
                        activeArea = 3
                    } else if (currentPossition >= 3.51 && currentPossition <= 4.50) {
                        slider.update({from: 4, to: 4});
                        activeArea = 4;
                    } else if (currentPossition >= 4.51 && currentPossition <= 5.50) {
                        slider.update({from: 5, to: 5});
                        activeArea = 5;
                    }
                    var animationCalssForOptions = 'animated bounceIn slow';
                    for (var ii = 1; ii <= 5; ii++) {
                        if (activeArea != ii) {
                            $('#' + targetAdmimationId + ii).removeClass(animationCalssForOptions);
                        }
                    }
                    $('#' + targetAdmimationId + activeArea).addClass(animationCalssForOptions);


                    var dataFrom = this.renderChange(data.from);
                    $('#rangeColorSliderImg-' + previewType + activeQuestion).prop("src", rangColorImgUrl[dataFrom]);
                    $('#rangeColorSliderImg-' + previewType + activeQuestion).prop("class", rangColor[dataFrom]);

                    $('.range-color-slider-custom-' + previewType + activeQuestion + ' .irs-bar').css("background", 'linear-gradient(to right,' + rangColorChange[dataFrom][0] + ' 0%, ' + rangColorChange[dataFrom][1] + ' 100%)');
                    $('.range-color-slider-custom-' + previewType + activeQuestion + ' .irs-handle').css({
                        "background": rangColorChange[dataFrom][1],
                        "box-shadow": "0 0 0px 1px " + rangColorChange[dataFrom][1]
                    });

                    var cvb = $('.range-color-slider-custom-' + previewType + activeQuestion + ' .irs-handle');
                    left_align_data = cvb[0].style.left;
                    document.getElementById('rangeColorSliderImg-' + previewType + activeQuestion).style.left = left_align_data;


                },
                renderChange(value) {
                    var dataFrom;
                    if (value >= 1 && value <= 1.5) {
                        dataFrom = 1;
                    } else if (value >= 1.5 && value <= 2.5) {
                        dataFrom = 2;
                    } else if (value >= 2.5 && value <= 3.5) {
                        dataFrom = 3;
                    } else if (value >= 3.5 && value <= 4.5) {
                        dataFrom = 4;
                    } else if (value >= 4.5 && value <= 5) {
                        dataFrom = 5;
                    }
                    return dataFrom;
                }
            });
        }, 0)
    }

    function initRanger(i, obj,previewType) {
        var imageUrl = obj["image"][i];
        for (var key in imageUrl) {
            // skip loop if the property is from prototype
            if (!imageUrl.hasOwnProperty(key)) {
                continue;
            }
            var imageObject = imageUrl[key];
            if (imageObject == undefined || imageObject == '') {
                var imageUrlForObject = APPURL + '/assets/dist/img/73a90acaae2b1ccc0e969709665bc62f' + key + '.png';
                imageUrl[key] = imageUrlForObject;
            }
        }
        var className = [
            'cus-fade1',
            'cus-fade2',
            'cus-fade3',
            'cus-fade4',
            'cus-fade5',
        ];
        var range = obj["choice"][i];
        window.setTimeout(function () {
            $("#rangeImageSlider-" +previewType+ i).ionRangeSlider({
                skin: "round",
                min: 0,
                max: 100,
                from: 50,
                step: 1,
                grid: false,
                grid_num: 0,
                grid_snap: false,
                extra_classes: "extra-class",
                onStart: function (data) {
                    var dataFrom = this.renderChange(data.from);
                    if(imageUrl[dataFrom].includes('73a90acaae2b1ccc0e969709665bc62f')){
                        // imageUrl[dataFrom] = '';
                    }
                    $("#ran_slider_img-" +previewType+ i).prop("src", imageUrl[dataFrom]);
                    $("#ran_slider_img-" +previewType+ i).prop("class", className[dataFrom]);
                    $('.irange-image-slider-' +previewType+ i + ' .irs-handle').html('<span class="range-lable"> ' + range[dataFrom] + ' </span>');
                },
                onChange: function (data) {
                    var dataFrom = this.renderChange(data.from);
                    if(imageUrl[dataFrom].includes('73a90acaae2b1ccc0e969709665bc62f')){
                        // imageUrl[dataFrom] = '';
                    }
                    $("#ran_slider_img-" +previewType+ i).prop("src", imageUrl[dataFrom]);
                    $("#ran_slider_img-" +previewType+ i).prop("class", className[dataFrom]);
                    $('.irange-image-slider-' +previewType+ i + ' .irs-handle').html('<span class="range-lable"> ' + range[dataFrom] + ' </span>');
                },
                renderChange(value) {
                    var dataFrom;
                    if (value >= 0 && value <= 20) {
                        dataFrom = 1;
                    }
                    else if (value >= 21 && value <= 40) {
                        dataFrom = 2;
                    }
                    else if (value >= 41 && value <= 60) {
                        dataFrom = 3;
                    }
                    else if (value >= 61 && value <= 80) {
                        dataFrom = 4;
                    }
                    else if (value >= 81 && value <= 100) {
                        dataFrom = 5;
                    }
                    return dataFrom;
                }
            });
        }, 0);
    }

    $(document).ready(function() {
        var category = $('#category').val();
        if (category != '' && category != undefined) {
            $('#subcategories').removeAttr('disabled');
        }

        setTimeout(function () {
            var subcategories = "{{ request()->get('subcategories') }}";
            if (subcategories != '' && subcategories != undefined) {
                $('#subcategories').removeAttr('disabled');
                // $('#subcategories').select2('val', subcategories);
            }
        }, 1000);

        $('#questionManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.zcquestionbank.getQuestions') }}',
                data: {
                    question_type: $('#question_type').val(),
                    question_status: $('#question_status').val(),
                    category: $('#category').val(),
                    subcategories: $('#subcategories').val(),
                    question: $('#question').val(),
                    with_image: $('#with_image').val(),
                },
            },
            columns: [
                {data: 'updated_at', name: 'updated_at', visible: false},
                {data: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'category', name: 'category'},
                {data: 'subcategory', name: 'subcategory'},
                {data: 'question', name: 'question'},
                {data: 'images', name: 'images'},
                {data: 'question_type', name: 'question_type'},
                {data: 'question_status', name: 'question_status'},
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
            paging: true,
            pageLength: pagination.value,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
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
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
        });

        $(document).on('click', '#reviewQuestion', function(e) {
            var publishConfirmModalBox = '#publish-question-modal-box';
            var action = $(this).data("action");
            $(publishConfirmModalBox).attr("data-id", $(this).data('id'));
            $(publishConfirmModalBox).attr('actionstatus', action);
            $(publishConfirmModalBox).find('#modal-title').html('Reviewed Question?');
            $(publishConfirmModalBox).find('#modal-message').html('Are you sure you want to move this question from '+action+' to publish?');
            $(publishConfirmModalBox).find('#modal-footer').html('<button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">No</button><button class="btn btn-primary" id="question-modal-box-confirm" status="publish" type="button">Yes</button>');
            $(publishConfirmModalBox).modal('show');
        });

        $(document).on('click', '#publishQuestion', function(e) {
            var publishConfirmModalBox = '#publish-question-modal-box';
            var action = $(this).data("action");
            $(publishConfirmModalBox).attr("data-id", $(this).data('id'));
            $(publishConfirmModalBox).attr('actionstatus', action);
            $(publishConfirmModalBox).find('#modal-title').html('Published/Draft Question?');
            $(publishConfirmModalBox).find('#modal-message').html('Are you sure you want to change the status?')
            $(publishConfirmModalBox).find('#modal-footer').html('<button class="btn btn-outline-primary" id="question-modal-box-confirm" status="publish" type="button">Published</button><button class="btn btn-primary" id="question-modal-box-confirm" status="Draft" type="button">Draft</button>');
            $(publishConfirmModalBox).modal('show');
        });

        $(document).on('click', '#unpublishQuestion', function(e) {
            var publishConfirmModalBox = '#publish-question-modal-box';
            var action = $(this).data("action");
            $(publishConfirmModalBox).attr("data-id", $(this).data('id'));
            $(publishConfirmModalBox).attr('actionstatus', action);
            $(publishConfirmModalBox).find('#modal-title').html('Unpublish/Draft Question?');
            $(publishConfirmModalBox).find('#modal-message').html('Are you sure you want to change the status?')
            $(publishConfirmModalBox).find('#modal-footer').html('<button class="btn btn-outline-primary" id="question-modal-box-confirm" status="unpublish" type="button">Publish</button><button class="btn btn-primary" id="question-modal-box-confirm" status="Draft" type="button">Draft</button>');
            $(publishConfirmModalBox).modal('show');
        });

        $(document).on('click', '#question-modal-box-confirm', function(e) {
            var publishConfirmModalBox = '#publish-question-modal-box';
            var _this = $(this),
                objectId = $(publishConfirmModalBox).attr("data-id");
                action = $(publishConfirmModalBox).data("action");

            _this.prop('disabled', 'disabled');
            var status = _this.attr('status');
            var actionStatus = $(publishConfirmModalBox).attr('actionstatus');

            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
            });
            $.ajax({
                type: 'POST',
                url: "{{ route('admin.zcquestionbank.publish', '/') }}" + `/${objectId}`,
                data: $.param({ action: actionStatus, status: status }),
                crossDomain: true,
                cache: false,
                dataType: 'json',
            }).done(function(data) {
                if (data.published == true) {
                    $('#questionManagment').DataTable().ajax.reload(null, false);
                    toastr.success(data.message);
                } else {
                    toastr.error(data.message);
                }
            }).fail(function(data) {
                if (data == 'Forbidden') {
                    toastr.error(`Failed to ${action} question.`);
                }
            }).always(function() {
                _this.removeAttr('disabled');
                $('#publish-question-modal-box').modal('hide');
            });
        });

        $(document).on('click', '#questionDelete', function (t) {
            var deleteConfirmModalBox = '#delete-model-box';
            $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
            $(deleteConfirmModalBox).modal('show');
        });
        $(document).on('click', '#delete-model-box-confirm', function (e) {
            $('.page-loader-wrapper').show();
            var deleteConfirmModalBox = '#delete-model-box';
            var objectId = $(deleteConfirmModalBox).attr("data-id");

            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
            });

            $.ajax({
                type: 'DELETE',
                url: "{{route('admin.zcquestionbank.delete','/')}}"+ '/' + objectId,
                data: null,
                crossDomain: true,
                cache: false,
                contentType: 'json',
                success: function (data) {
                    $('#questionManagment').DataTable().ajax.reload(null, false);
                    if (data['deleted'] == 'true') {
                        toastr.success("Question deleted successfully.!");
                    } else if(data['deleted'] == 'use') {
                        toastr.error("The question is in use!");
                    } else {
                        toastr.error("delete error.");
                    }
                    var deleteConfirmModalBox = '#delete-model-box';
                    $(deleteConfirmModalBox).modal('hide');
                    $('.page-loader-wrapper').hide();
                },
                error: function (data) {
                    $('#questionManagment').DataTable().ajax.reload(null, false);
                    toastr.error("This action is unauthorized.");
                    var deleteConfirmModalBox = '#delete-model-box';
                    $(deleteConfirmModalBox).modal('hide');
                    $('.page-loader-wrapper').hide();
                }
            });
        });

        $(document).on('click', '#questionShow', function (e) {
            var questionId = $(this).data('id');
            $.ajax({
                type: 'GET',
                url: "{{route('admin.zcquestionbank.show','/')}}"+ '/' + questionId,
                data: null,
                crossDomain: true,
                cache: false,
                contentType: 'json',
                success: function (questionTextData) {
                    var activeQuestion = 1;
                    $('#userQuestionFreeText').html(null);
                    $('#singleQuestionPreviewModalChoiceHtml').html(null);
                    if (questionTextData.status == 1) {
                        if (questionTextData.question_type == 'free-text') {
                            if (questionTextData["question_image_src"][activeQuestion].includes('73a90acaae2b1ccc0e969709665bc62f')) {
                                questionTextData["question_image_src"][activeQuestion] = '';
                            }
                            var htmlElements = '<div class="row align-items-center"><div class="col-lg-12 text-center">\n' +
                                '<div class="ans-main-area question-type-one m-0-a">\n' +
                                '<div class="text-center">\n' +
                                '<h2 class="question-text">' + questionTextData["question"][activeQuestion] + '</h2></div>\n' +
                                '<div class="text-center w-100 mb-3">\n' +
                                '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + questionTextData["question_image_src"][activeQuestion] + '" alt="">\n' +
                                '</div>\n' +
                                '<div class="form-group ans-textarea">\n' +
                                '<textarea class="form-control animated flash slow h-auto" id="" rows="5"></textarea>\n' +
                                '</div>\n' +
                                '</div>\n' +
                                '</div>\n' +
                                '</div>';

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

                                dynamicHtml += '<!-- item-box -->' +
                                    '<label class="choices-item-box">\n' +
                                    '    <input type="radio" name="choices">\n' +
                                    '    <div class="markarea">\n' +
                                    '        <span class="checkmark animated tada faste"></span>\n' +
                                    '        <div class="choices-item-img">\n' +
                                    '            <img class="" src="' + imageLink + '" alt="">\n' +
                                    '        </div>\n' +
                                    '    </div>\n' +
                                    '    <div class="choices-box-title">' + imageTitle + '</div>\n' +
                                    '</label>\n' +
                                    '<!-- item-box /-->';
                            }

                            if (questionTextData["question_image_src"][activeQuestion].includes('73a90acaae2b1ccc0e969709665bc62f')) {
                                questionTextData["question_image_src"][activeQuestion] = '';
                            }

                            var singlePreviewHtml = '<div class="row align-items-center"><div class="col-lg-12 text-center">' +
                                '    <div class="ans-main-area question-type-one m-0-a">\n' +
                                ' <div class="text-center">\n' +
                                '        <h2 class="question-text">' + questionTextData.question[activeQuestion] + '</h2></div>\n' +
                                '<div class="text-center w-100 mb-3">\n' +
                                '<img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="' + questionTextData["question_image_src"][activeQuestion] + '" alt="">\n' +
                                '</div>\n' +
                                '        <div class="animated flash slow choices-main-box">\n' +
                                dynamicHtml +
                                '        </div>\n' +
                                '    </div>\n' +
                                '</div>\n' +
                                '</div>';

                            $('#singleQuestionPreviewModalChoiceHtml').append(singlePreviewHtml);
                            $('#singleQuestionPreviewModalChoice').modal('show');
                        }
                    } else {
                        toastr.error(questionTextData.message);
                    }
                },
                error: function (data) {
                    toastr.error(data.statusText);
                }
            });
        });

        $('#category').change(function() {
            if ($('#category').val() != '' && $('#category').val() != null) {
                if ($(this).attr("id") == 'category' && $(this).attr('target-data') == 'subcategories') {
                    var select = $(this).attr("id");
                    var value = $(this).val();
                    var categoryDependent = $(this).attr('target-data');
                    var _token = $('input[name="_token"]').val();
                    categoryUrl = '{{ route("admin.ajax.zcSubCategories", ":id") }}'
                    url = categoryUrl.replace(':id', value);
                    $.ajax({
                        url: url,
                        method: 'get',
                        data: {
                            _token: _token
                        },
                        success: function(result) {
                            $('#' + categoryDependent).empty();
                            $('#' + categoryDependent).attr('disabled', false);
                            $('#' + categoryDependent).val('').trigger('change').append('<option value="">Select</option>');
                            $('#' + categoryDependent).removeClass('is-valid');
                            $.each(result.result, function(key, value) {
                                $('#' + categoryDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                            if (Object.keys(result.result).length == 1) {
                                $.each(result.result, function(key, value) {
                                    $('#' + categoryDependent).select2('val', value.id);
                                });
                            }
                        }
                    })
                }
            }
        });
    });
</script>
@endsection

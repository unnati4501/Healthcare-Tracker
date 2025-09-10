{{ Form::open(['route'=>['admin.zcquestionbank.store.choice-question'],"class"=>"form-tab-2 qus-form-manager survey-form-choice zevo_form_submit",'id'=>'choice-question', 'autocomplete' => 'off', 'files' => true]) }}
<div class="card-body">
    <input id="total-form-choice" type="hidden" value="1"/>
    <input id="question-type-choice" name="question_type" type="hidden" value="choice"/>
    <input class="form-data" name="form-data" type="hidden" value="0"/>
    <div class="card-inner">
        @include('admin.zcquestionbank.common.category-dropdown')
        <h3 class="card-inner-title border-0 mb-0">
            What would you like to ask?
        </h3>
        <div class="question-wrap" data-order="1">
            <div class="qus-inline qus-inline-choice">
                <div class="input-group question-text-span-choice form-group mb-0">
                    <span class="input-group-text data-id" data-id="1">
                        Q1
                    </span>
                    <input class="form-control m-b-10 m-b-xs-0 question-input-text" id="question[1]" name="question[1]" placeholder="Type your question here" type="text"/>
                </div>
                <div class="qus-btn-area">
                    <div class="qus-main-img-upload img-upload-area image-selection-choice cu-tooltip-wrap" data-input-ref="question_image-choice[1]">
                        <div class="cu-tooltip">
                            {{ getHelpTooltipText('question.logo') }}
                        </div>
                        <label for="question_image-choice[1]" class="mb-0">
                            <img data-id-ref="previewImg2[1]" id="previewImg2[1]" src="{{asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png')}}"/>
                        </label>
                        <input class="image-selector-choice" data-previewelement="1" id="question_image-choice[1]" data-width="{{ config('zevolifesettings.imageConversions.question.logo.width') }}" data-height="{{ config('zevolifesettings.imageConversions.question.logo.height') }}" data-ratio="{{ config('zevolifesettings.imageAspectRatio.question.logo') }}" name="question_image-choice[1]" type="file"/>
                        <input class="image-hidden-selector" id="question_image-choice_src[1]" name="question_image-choice_src[1]" type="hidden"/>
                    </div>
                    <a class="preview-button-choice" href="javascript:void(0);">
                        <span>
                            <i aria-hidden="true" class="far fa-eye">
                            </i>
                        </span>
                    </a>
                    <a class="delete-que delete-toast question-delete-choice action-icon danger" href="javascript:void(0);">
                        <i aria-hidden="true" class="far fa-trash-alt">
                        </i>
                    </a>
                </div>
            </div>
            <p class="m-t-25 m-b-20">
                <strong>
                    Note:
                </strong>
                Question Image is mandatory.
            </p>
            <div class="question-offset" data-order="1">
                <div class="row">
                    <div class="col-md-5">
                        <div class="qus-choices-option qus-option1" data-order="1">
                            <div class="qus-inline mb-4 question-group-outer">
                                <div class="input-group form-group question-group mb-0">
                                    <div class="m-0">
                                        <select class="form-control double-error score-field" id="score[1][1]" name="score[1][1]">
                                            <option value="">
                                                Score
                                            </option>
                                            <option value="0">
                                                0
                                            </option>
                                            <option value="1">
                                                1
                                            </option>
                                            <option value="2">
                                                2
                                            </option>
                                            <option value="3">
                                                3
                                            </option>
                                            <option value="4">
                                                4
                                            </option>
                                            <option value="5">
                                                5
                                            </option>
                                            <option value="6">
                                                6
                                            </option>
                                            <option value="7">
                                                7
                                            </option>
                                        </select>
                                    </div>
                                    <div class="choices-option-input">
                                        <input class="form-control choice-input" id="choice[1][1]" name="choice[1][1]" placeholder="Choice" type="text"/>
                                    </div>
                                    <div class="img-upload-area image-selection-choice cu-tooltip-wrap" data-input-ref="image[1][1][imageId]">
                                        <div class="cu-tooltip">
                                            {{ getHelpTooltipText('questionoption.logo') }}
                                        </div>
                                        <label for="image[1][1][imageId]" class="mb-0">
                                            <img data-id-ref="previewImg3[1][1]" id="previewImg3[1][1]" src="{{asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png')}}"/>
                                        </label>
                                        <input class="image-selector-choice-option" data-previewoptionelement="1" id="image[1][1][imageId]" name="image[1][1][imageId]" data-width="{{ config('zevolifesettings.imageConversions.questionoption.logo.width') }}" data-height="{{ config('zevolifesettings.imageConversions.questionoption.logo.height') }}" data-ratio="{{ config('zevolifesettings.imageAspectRatio.questionoption.logo') }}" type="file"/>
                                        <input class="choice-image-hidden-selector" id="image[1][1][imageSrc]" name="image[1][1][imageSrc]" type="hidden"/>
                                    </div>
                                </div>
                                <div class="qus-btn-area">
                                    <a class="delete-question action-icon danger ms-3 align-self-center question-delete-choice-options" href="javascript:void(0);">
                                        <i class="far fa-trash-alt">
                                        </i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="qus-choices-option qus-option1" data-order="2">
                            <div class="qus-inline mb-4 question-group-outer">
                                <div class="input-group form-group question-group mb-0">
                                    <div class="m-0">
                                        <select class="form-control double-error score-field" id="score[1][2]" name="score[1][2]">
                                            <option value="">
                                                Score
                                            </option>
                                            <option value="0">
                                                0
                                            </option>
                                            <option value="1">
                                                1
                                            </option>
                                            <option value="2">
                                                2
                                            </option>
                                            <option value="3">
                                                3
                                            </option>
                                            <option value="4">
                                                4
                                            </option>
                                            <option value="5">
                                                5
                                            </option>
                                            <option value="6">
                                                6
                                            </option>
                                            <option value="7">
                                                7
                                            </option>
                                        </select>
                                    </div>
                                    <div class="choices-option-input">
                                        <input class="form-control choice-input" id="choice[1][2]" name="choice[1][2]" placeholder="Choice" type="text"/>
                                    </div>
                                    <div class="img-upload-area image-selection-choice cu-tooltip-wrap" data-input-ref="image[1][2][imageId]">
                                        <div class="cu-tooltip">
                                            {{ getHelpTooltipText('questionoption.logo') }}
                                        </div>
                                        <label for="image[1][2][imageId]" class="mb-0">
                                            <img data-id-ref="previewImg3[1][2]" id="previewImg3[1][2]" src="{{asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png')}}"/>
                                        </label>
                                        <input class="image-selector-choice-option" data-previewoptionelement="2" id="image[1][2][imageId]" name="image[1][2][imageId]" data-width="{{ config('zevolifesettings.imageConversions.questionoption.logo.width') }}" data-height="{{ config('zevolifesettings.imageConversions.questionoption.logo.height') }}" data-ratio="{{ config('zevolifesettings.imageAspectRatio.questionoption.logo') }}" type="file"/>
                                        <input class="choice-image-hidden-selector" id="image[1][2][imageSrc]" name="image[1][2][imageSrc]" type="hidden"/>
                                    </div>
                                </div>
                                <div class="qus-btn-area">
                                    <a class="delete-question action-icon danger ms-3 align-self-center question-delete-choice-options" href="javascript:void(0);">
                                        <i class="far fa-trash-alt">
                                        </i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="imageNotice text-center">
                </div>
                <a class="addAnotherChoice" href="javascript:void(0);">
                    <i class="far fa-plus me-2">
                    </i>
                    Add choice
                </a>
            </div>
        </div>
        <div class="mt-5">
            <button class="btn btn-outline-primary add-quesiton-toast" id="addAnotherQuestionChoice" type="button">
                + Add Question
            </button>
        </div>
    </div>
</div>
<div class="card-footer">
    <div class="save-cancel-wrap flex-wrap">
        <a class="btn btn-outline-primary w-auto" href="{{route('admin.zcquestionbank.index')}}">
            Cancel
        </a>
        <div>
            <button class="btn btn-outline-primary me-2 w-auto jello saveButton" onclick="validateFormAndSubmit('#choice-question','#choice-question','#choice-question')" type="button">
                Save
            </button>
            <button class="btn btn-outline-primary me-2 w-auto jello saveAndNewButton" name="saveAndNew" onclick="validateFormAndSubmit('#choice-question','#choice-question','#choice-question')" value="saveAndNew">
                Save & New
            </button>
            <button class="btn btn-primary me-2 w-auto question-delete-all-choice " type="button">
                Delete all
            </button>
            <button class="btn btn-primary w-auto preview-all-button-choice" type="button">
                Preview all
            </button>
        </div>
    </div>
</div>
{{ Form::close() }}
@include('admin.zcquestionbank.common.choice.modal-box')

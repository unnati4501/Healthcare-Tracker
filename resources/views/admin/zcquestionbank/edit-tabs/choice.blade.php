{{ Form::open(['route'=>['admin.zcquestionbank.store.choice-question',$question->id],"class"=>"form-tab-2 qus-form-manager survey-form-choice zevo_form_submit",'id'=>'choice-question', 'autocomplete' => 'off', 'files'=>true]) }}
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
                        Q1.
                    </span>
                    <input class="form-control question-input-text" id="question[1]" name="question[1]" placeholder="Type your question here" type="text" value="{{ $question->title }}">
                    </input>
                </div>
                <div class="qus-btn-area">
                    <div class="qus-main-img-upload img-upload-area image-selection-choice cu-tooltip-wrap" data-input-ref="question_image-choice[1]">
                        <div class="cu-tooltip">
                            {{ getHelpTooltipText('question.logo') }}
                        </div>
                        <label class="mb-0" for="question_image-choice[1]">
                            @if(!empty($question->getFirstMediaUrl('logo')))
                            <img data-id-ref="previewImg2[1]" id="previewImg2[1]" src="{{$questionOptions['meta']['imageUrl'] ?? asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png')}}"/>
                            @else
                            <img data-id-ref="previewImg2[1]" id="previewImg2[1]" src="{{asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png')}}"/>
                            @endif
                        </label>
                        <input class="image-selector-choice" data-previewelement="1" id="question_image-choice[1]" data-width="{{ config('zevolifesettings.imageConversions.question.logo.width') }}" data-height="{{ config('zevolifesettings.imageConversions.question.logo.height') }}" data-ratio="{{ config('zevolifesettings.imageAspectRatio.question.logo') }}"  name="question_image-choice[1]" type="file" value="{{$questionOptions['meta']['imageId']}}"/>
                        <input class="image-hidden-selector" id="question_image-choice_src[1]" name="question_image-choice_src[1]" type="hidden" value="{{$questionOptions['meta']['imageUrl']}}"/>
                    </div>
                    <a class="edit-question action-icon preview-button-choice" href="javascript:void(0);">
                        <i class="far fa-eye">
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
                    @foreach($questionOptions['score'] as $index => $questionOption)
                    <div class="col-md-5">
                        <div class="qus-choices-option qus-option1" data-order="{{$index}}">
                            <div class="qus-inline mb-4 question-group-outer">
                                <div class="input-group form-group question-group mb-0">
                                    <div class="m-0">
                                        @if($question->status == 1)
                                        {{ Form::select("", [null => 'Score',0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7], (int) $questionOption['score'], ['class' => "form-control double-error score-field", 'disabled' => true]) }}
                                        {{ Form::hidden("score[1][$index]", (int) $questionOption['score'], ['id' => "score[1][$index]"]) }}
                                        @else
                                        {{ Form::select("score[1][$index]",[null => 'Score',0=>0,1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7],(int) $questionOption['score'],['class'=>"form-control double-error score-field",'id'=>"score[1][$index]"]) }}
                                        @endif
                                    </div>
                                    <div class="choices-option-input">
                                        <input class="form-control choice-field" id="choice[1][{{$index}}]" name="choice[1][{{$index}}]" placeholder="Choice" type="text" value="{{$questionOption['choice']}}"/>
                                    </div>
                                    <div class="img-upload-area image-selection-choice cu-tooltip-wrap" data-input-ref="image[1][{{$index}}][imageId]">
                                        <div class="cu-tooltip">
                                            {{ getHelpTooltipText('questionoption.logo') }}
                                        </div>
                                        <label class="mb-0" for="image[1][{{$index}}][imageId]">
                                            @if(!empty($questionOption['imageUrl']))
                                            <img data-id-ref="previewImg3[1][{{$index}}]" id="previewImg3[1][{{$index}}]" src="{{$questionOption['imageUrl'] ?? asset('assets/dist/img/choice-$index.png')}}"/>
                                            @else
                                            <img data-id-ref="previewImg3[1][{{$index}}]" id="previewImg3[1][{{$index}}]" src="{{asset('assets/dist/img/choice-'.$index.'.png')}}"/>
                                            @endif
                                        </label>
                                        <input class="image-selector-choice-option" data-previewoptionelement="{{$index}}" id="image[1][{{$index}}][imageId]" data-width="{{ config('zevolifesettings.imageConversions.questionoption.logo.width') }}" data-height="{{ config('zevolifesettings.imageConversions.questionoption.logo.height') }}" data-ratio="{{ config('zevolifesettings.imageAspectRatio.questionoption.logo') }}" name="image[1][{{$index}}][imageId]" type="file" value="{{$questionOption['imageId']}}"/>
                                        <input class="choice-image-hidden-selector" id="image[1][{{$index}}][imageSrc]" name="image[1][{{$index}}][imageSrc]" type="hidden" value="{{$questionOption['imageUrl']}}"/>
                                        <input id="image[1][{{$index}}][optionId]" name="image[1][{{$index}}][optionId]" type="hidden" value="{{$questionOption['optionId']}}"/>
                                    </div>
                                </div>
                                @if($question->status != 1)
                                <div class="qus-btn-area">
                                    <a class="delete-question action-icon danger ms-3 align-self-center question-delete-choice-options" href="javascript:void(0);">
                                        <i class="far fa-trash-alt">
                                        </i>
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="imageNotice text-center">
                </div>
                @if($question->status != 1)
                <a class="addAnotherChoice" href="javascript:void(0);">
                    <i class="far fa-plus me-2">
                    </i>
                    Add choice
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="card-footer">
    <div class="save-cancel-wrap flex-wrap">
        <a class="btn btn-outline-primary w-auto" href="{{route('admin.zcquestionbank.index')}}">
            Cancel
        </a>
        <div>
            <button class="btn btn-primary w-auto jello" onclick="validateFormAndSubmit('#choice-question','#choice-question','#choice-question')" type="button">
                Update
            </button>
        </div>
    </div>
</div>
{{ Form::close() }}
@include('admin.zcquestionbank.common.choice.modal-box')

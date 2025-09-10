{{ Form::open(['route'=>['admin.zcquestionbank.store.free-text-question',$question->id],"class"=>"form-tab-1 qus-form-manager survey-form-free-text zevo_form_submit",'id'=>'free-text', 'autocomplete' => 'off', 'files'=>true]) }}
<div class="card-body">
    <input id="total-form-free-text" type="hidden" value="1"/>
    <input id="question-type-free-text" name="question_type" type="hidden" value="free-text"/>
    <input class="form-data" name="form-data" type="hidden" value="0"/>
    <div class="card-inner">
        @include('admin.zcquestionbank.common.category-dropdown')
        <h3 class="card-inner-title border-0 mb-0">
            What would you like to ask?
        </h3>
        <div class="question-wrap" data-order="1">
            <div class="form-group que-head-free-text" data-order="1">
                <div class="qus-inline qus-inline-free-text">
                    <div class="input-group question-text question-text-span-free-text">
                        <span class="input-group-text data-id" data-id="1">
                            Q1.
                        </span>
                        <input class="form-control question-input-text" id="question[1]" name="question[1]" placeholder="Type your question here" type="text" value="{{$question->title}}"/>
                    </div>
                    <div class="qus-btn-area">
                        <div class="qus-main-img-upload image-selection-free-text img-upload-area cu-tooltip-wrap" data-input-ref="question_image-free-text[1]">
                            <div class="cu-tooltip">
                                {{ getHelpTooltipText('question.logo') }}
                            </div>
                            <label for="question_image-free-text[1]" class="mb-0">
                                @if(!empty($question->getFirstMediaUrl('logo')))
                                <img data-id-ref="previewImg[1]" id="previewImg[1]" src="{{$questionOptions['imageUrl'] ?? asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png')}}"/>
                                @else
                                <img data-id-ref="previewImg[1]" id="previewImg[1]" src="{{asset('assets/dist/img/73a90acaae2b1ccc0e969709665bc62f.png')}}"/>
                                @endif
                            </label>
                            <input class="image-selector" data-previewelement="1" id="question_image-free-text[1]" data-width="{{ config('zevolifesettings.imageConversions.question.logo.width') }}" data-height="{{ config('zevolifesettings.imageConversions.question.logo.height') }}" data-ratio="{{ config('zevolifesettings.imageAspectRatio.question.logo') }}" name="question_image-free-text[1]" type="file" value="{{$questionOptions['imageId']}}"/>
                            <input class="image-hidden-selector" id="question_image-free-text_src[1]" name="question_image-free-text_src[1]" type="hidden" value="{{$questionOptions['imageUrl']}}"/>
                        </div>
                        <a class="edit-question action-icon preview-button-free-text" href="javascript:void(0);">
                            <i class="far fa-eye">
                            </i>
                        </a>
                    </div>
                </div>
                <div class="imageNotice text-center">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card-footer">
    <div class="save-cancel-wrap flex-wrap">
        <a class="btn btn-outline-primary w-auto cancleButton" href="{{route('admin.zcquestionbank.index')}}">
            Cancel
        </a>
        <div>
            <button class="btn btn-primary w-auto jello" onclick="validateFormAndSubmit(['#free-text'],'#free-text','#free-text')" type="button">
                Update
            </button>
        </div>
    </div>
</div>
{{ Form::close() }}
@include('admin.zcquestionbank.common.free-text.modal-box')

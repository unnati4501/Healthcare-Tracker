<div class="left-dot-effect single-question-block {{ ((isset($edit) && $edit == true) ? 'old-added' : '') }}" data-id="{{ $id }}">
    @if(!isset($view))
    <button class="btn btn-sm btn-danger qus-delete-btn remove-question" data-id="{{ $id }}" type="button">
        <i class="fas fa-trash">
        </i>
    </button>
    @endif
    <div class="row">
        <div class="col-md-7">
            <div class="form-group">
                <div class="input-group qus-input-area form-group">
                    <span class="input-group-text">
                        Q.
                    </span>
                    {{ Form::text("question[{$id}]", ($question->title ?? null), ['class' => 'form-control question_required', 'id' => "question[{$id}]", 'placeholder' => trans('masterclass.survey.form.placeholder.question'), 'maxlength' => 150]) }}
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group">
                {{ Form::select("type[{$id}]", $question_types, ($question->type ?? 'single_choice'), ['class' => 'form-control select2 question-type', 'id' => "type[{$id}]", "disabled" => $courseStatus]) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-7">
            <h6 class="">
                {{  trans('masterclass.survey.form.labels.option')  }}
            </h6>
            <table class="table custom-table no-border gap-adjust no-hover qus-option-table question-option-{{ $id }}">
                <tbody>
                    @forelse ($options as $option)
                        @include('admin.course.survey.option_block', ["id" => $id, "oid" => $option->getKey(), "edit" => true, 'option' => $option])
                    @empty
                        @include('admin.course.survey.option_block', ["id" => $id, "oid" => $oid, "edit" => false, 'option' => null])
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="col-md-5 mb-2">
            <div class="form-group">
                @php
                    $mediaInfo = ((!empty($question) && !empty($question->getFirstMedia('logo'))) ? $question->getFirstMedia('logo') : null);
                @endphp
                {{ Form::label('', trans('masterclass.survey.form.labels.survey_question_logo')) }}
                @if(!isset($view))
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('course_survey_question.logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                @endif
                <div class="custom-file custom-file-preview">
                    <input accept="image/jpg,image/jpeg,image/png" class="form-control custom-file-input question_logo_tag {{ (($edit === true) ? 'ignore-validation' : '') }}" data-previewelement="#question_logo_preview_{{ $id }}" id="logo_{{ $id }}" data-width="{{ config('zevolifesettings.imageConversions.course_survey_question.logo.width') }}" data-height="{{ config('zevolifesettings.imageConversions.course_survey_question.logo.height') }}" data-ratio="{{ config('zevolifesettings.imageAspectRatio.course_survey_question.logo') }}" name="logo[{{ $id }}]" type="file" />
                    <label class="file-preview-img" for="logo_{{ $id }}">
                        <img id="question_logo_preview_{{ $id }}" src="{{ (!empty($mediaInfo) ? $mediaInfo->getUrl() : asset('assets/dist/img/boxed-bg.png')) }}"/>
                    </label>
                    <label class="custom-file-label" for="logo_{{ $id }}">
                        {{ (!empty($mediaInfo) ? $mediaInfo->name : trans('masterclass.survey.form.placeholder.choose_file')) }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
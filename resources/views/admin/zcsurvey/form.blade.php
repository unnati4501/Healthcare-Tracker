<div class="tab-content" id="serverAddStep">
    <!-- Step-1 -->
    <h3>
        @if(isset($edit) && $edit == true)
            {{ trans('labels.zcsurvey.edit_form_title') }}
        @else
            {{ trans('labels.zcsurvey.create_form_title') }}
        @endif
    </h3>
    <section class="step-1" data-step="0">
        <div class="card-body">
            <div class="card-inner">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            {{ Form::label('title', trans('labels.zcsurvey.title')) }}
                            {{ Form::text('title', old('title', ($data->title ?? null)), ['class' => 'form-control', 'placeholder' => 'Survey title', 'id' => 'title', 'autocomplete' => 'off', 'maxlength' => 100, "onkeyup" => "validateTitle()"]) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('description', trans('labels.zcsurvey.description')) }}
                            {{ Form::textarea('description', old('description', ($data->description ?? null)), ['class' => 'form-control', 'placeholder' => 'Survey description', 'id' => 'description', 'rows' => 3, 'maxlength' => 250]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Step-2 -->
    <h3>
        {{ trans('labels.zcsurvey.add_question_list') }}
    </h3>
    <section class="step-2" data-step="1">
        <div class="card-header detailed-header has-elements">
            <div class="d-flex flex-wrap">
                <div>
                    <div class="form-group mb-0">
                        {{ Form::select('question_category', $question_category, null, ['class' => "form-control select2", 'id' => 'question_category', 'data-allow-clear' => 'true', 'data-placeholder' => "Question category", 'placeholder' => 'Question category']) }}
                    </div>
                </div>
                <div>
                    <div class="form-group mb-0">
                        {{ Form::select('question_subcategory', $question_subcategory, null, ['class' => "form-control select2", 'id' => 'question_subcategory', 'data-allow-clear' => 'true', 'data-placeholder' => "Question subcategory", 'placeholder' => 'Question subcategory']) }}
                    </div>
                </div>
                <div>
                    <div class="form-group mb-0">
                        {{ Form::text('question_search', null, ['class' => 'form-control', 'placeholder' => 'Search', 'id' => 'question_search', 'autocomplete' => 'off']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-2">
            <div class="row h-100 second-step-wraper">
                <div class="col-lg-12 col-xl-5 col-xxl border-xl-right pb-3">
                    <div class="table-responsive">
                        <table class="dataTable table custom-table table-hover" id="allquestions">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <div class="check-row-box" id="check-all-questions">
                                            <i class="fal fa-check">
                                            </i>
                                        </div>
                                    </th>
                                    <th class="no-sort ">
                                        {{ trans('labels.zcsurvey.sr_no') }}
                                    </th>
                                    <th class="no-sort">
                                    </th>
                                    <th class="no-sort">
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.questions') }}
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.category') }}
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.subcategory') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-12 col-xl-2 col-xxl-auto align-self-center">
                    <div class="text-center">
                        <button class="btn btn-newteal right-arrow-btn mb-4" id="addToSurveyQuestions" type="button">
                            {{ trans('labels.zcsurvey.add') }}
                            <span class="button-arrow">
                                <i class="far fa-arrow-right">
                                </i>
                            </span>
                        </button>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary left-arrow-btn" id="removeFromSurveyQuestions" type="button">
                            <span class="button-arrow">
                                <i class="far fa-arrow-left">
                                </i>
                            </span>
                            {{ trans('labels.zcsurvey.remove') }}
                        </button>
                    </div>
                </div>
                <div class="col-lg-12 col-xl-5 col-xxl border-xl-left pt-3 pb-3">
                    <div class="table-responsive">
                        <table class="dataTable table custom-table table-hove" id="surveyquestions">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <div class="check-row-box" id="check-all-survey-questions">
                                            <i class="fal fa-check">
                                            </i>
                                        </div>
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('labels.zcsurvey.sr_no') }}
                                    </th>
                                    <th class="no-sort">
                                    </th>
                                    <th class="no-sort">
                                    </th>
                                    <th class="no-sort">
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('labels.zcsurvey.questions') }}
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('labels.zcsurvey.category') }}
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('labels.zcsurvey.subcategory') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Step-3 -->
    <h3>
        {{ trans('labels.zcsurvey.submit') }}
    </h3>
    <section class="step-3" data-step="2">
        <div class="card-body">
            <div class="card-header detailed-header p-0">
                <div class="flex-wrap">
                    <div>
                        {{ Form::label('preview_title', trans('labels.zcsurvey.title'), ['class' => 'text-muted mb-1']) }}
                        <p id="preview_title">
                        </p>
                    </div>
                    <div>
                        {{ Form::label('preview_description', trans('labels.zcsurvey.description'), ['class' => 'text-muted mb-1']) }}
                        <p id="preview_description">
                        </p>
                    </div>
                </div>
            </div>
            <div class="card-table-outer">
                <div class="table-responsive">
                    <table class="table custom-table selected-questions-preview-table" id="finalQuestionList">
                        <thead>
                            <tr>
                                <th class="no-sort th-btn-sm">
                                    {{ trans('labels.zcsurvey.sr_no') }}
                                </th>
                                <th class="">
                                </th>
                                <th class="no-sort">
                                </th>
                                <th class="no-sort">
                                    {{ trans('labels.zcsurvey.questions') }}
                                </th>
                                <th class="no-sort">
                                    {{ trans('labels.zcsurvey.category') }}
                                </th>
                                <th class="no-sort">
                                    {{ trans('labels.zcsurvey.subcategory') }}
                                </th>
                                <th class="no-sort">
                                    {{ trans('labels.zcsurvey.question_type') }}
                                </th>
                                <th class="no-sort">
                                    {{ trans('labels.zcsurvey.added_date') }}
                                </th>
                                <th class="no-sort th-btn-4">
                                    {{ trans('labels.zcsurvey.action') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<div id="survey_questions_data_block">
</div>
{{ Form::hidden('is_premium', ($data->is_premium ?? 0), ['id' => 'is_premium']) }}

@include('admin.zcquestionbank.common.free-text.modal-box')
@include('admin.zcquestionbank.common.choice.modal-box')

<!-- Image upload box start -->
<!-- Image upload box end -->
<!-- Delete all confirmation Model START -->
<div class="modal fade delete-all-question-model-choice" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Delete all questions ?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    Are you sure you want to delete all the question(s)?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary question-delete-all-confirm-choice" type="button">
                    Delete all
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Delete all confirmation Model END -->
<!-- Multi question Preview Model START -->
<div aria-hidden="true" aria-labelledby="shortAnswerTitle" class="modal fade full-screen-popup preview-all-model preview-question" id="allQuestionPreviewModalChoice" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-area">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        Preview Questions
                    </h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="fal fa-times">
                        </i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="user-question-main wizard" id="allQuestionPreviewModalChoiceHtml">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Preview Model END -->
<!-- Single all Model START -->
<div aria-hidden="true" aria-labelledby="shortAnswerTitle" class="modal fade full-screen-popup preview-all-questions" id="singleQuestionPreviewModalChoice" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-area">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        Preview Question
                    </h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="fal fa-times">
                        </i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="user-question-main wizard" id="singleQuestionPreviewModalChoiceHtml">
                            {{--
                            <div class="container">
                                <div class="row align-items-center">
                                    <div class="col-lg-12 align-self-center text-center">
                                        <div class="ans-main-area question-type-one">
                                            <p class="question-text-title">
                                                QUESTION
                                                <i aria-hidden="true" class="fa fa-question-circle-o">
                                                </i>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Preview all Model END -->

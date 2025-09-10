<!-- Image upload box start -->
<!-- Image upload box end -->
<!-- Delete all confirmation Model START -->
<div class="modal fade delete-all-question-model-free-text" role="dialog" tabindex="-1">
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
                <button class="btn btn-primary question-delete-all-confirm-free-text" type="button">
                    Delete all
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Delete all confirmation Model END -->
<!-- Modal-1 Short Answer Single preview -->
<div aria-hidden="true" aria-labelledby="shortAnswerTitle" class="modal fade full-screen-popup" id="userQuestionFreeTextModelBox" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-area">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="shortAnswerTitle">
                        Preview Question
                    </h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="fal fa-times">
                        </i>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="container-fluid" id="userQuestionFreeText">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.model -->
<!-- Modal-1 Short Answer multi preview -->
<div aria-hidden="true" aria-labelledby="shortAnswerTitle" class="modal fade full-screen-popup preview-all-model" id="userQuestionFreeTextModelBoxdAll" role="dialog" tabindex="-1">
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
                        <div class="user-question-main wizard" id="userQuestionFreeTextAll">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.model -->

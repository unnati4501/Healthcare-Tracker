<!-- Modal-1 Short Answer Single preview -->
<div aria-hidden="true" aria-labelledby="shortAnswerTitle" class="modal fade full-screen-popup" id="userQuestionFreeTextModelBox" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-area">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="shortAnswerTitle">
                        {{ trans('survey.insights.labels.preview_question') }}
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
                        {{ trans('survey.insights.labels.preview_question') }}
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

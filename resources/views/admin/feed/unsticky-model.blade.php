<div class="modal fade" data-id="0" id="unstick-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('feed.modal.unstick_feed') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    {{ trans('feed.modal.unstick_feed_message') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" title="{{ trans('labels.buttons.cancel') }}" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="unstick-model-box-confirm" title="{{ trans('labels.buttons.unstick') }}" type="button">
                    {{ trans('feed.buttons.unstick') }}
                </button>
            </div>
        </div>
    </div>
</div>
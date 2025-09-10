<div category="dialog" class="modal fade" data-id="0" id="add-location-slot-model-box" tabindex="-1">
    <div category="document" class="modal-dialog modal-lg">
        <div class="modal-content">
            {{ Form::open(['class' => 'form-horizontal', 'method'=>'POST', 'role' => 'form', 'id'=>'addSubcategoryForm', 'files' => true]) }}
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title"></h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive set-availability-block">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                @if($dt_servicemode == false)
                <button class="btn btn-primary subcategory-save" data-bs-dismiss="modal" type="button"> 
                    {{ trans('buttons.general.save') }}
                </button>
                @endif
                <button class="btn btn-primary subcategory-update" data-bs-dismiss="modal" type="button" style="display:none;">
                    {{ trans('buttons.general.save') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
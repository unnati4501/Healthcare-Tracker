<div class="modal fade" data-id="0" data-backdrop="static" id="subcategory_visibility_preview" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('services.table.visible_to_services') }}
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <table class="table custom-table" id="visible_to_subcategory_tbl">
                    <thead>
                        <tr>
                            <th>
                                {{trans('services.table.no')}}
                            </th>
                            <th>
                                {{trans('services.table.subcategory')}}
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
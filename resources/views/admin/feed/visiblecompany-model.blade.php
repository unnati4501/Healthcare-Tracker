<div class="modal fade" data-id="0" data-backdrop="static" id="company_visibility_preview" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('feed.title.visible_to_company') }}
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <table class="table custom-table" id="visible_to_company_tbl">
                    <thead>
                        <tr>
                            <th>
                                {{ trans('feed.table.no') }}
                            </th>
                            <th>
                                {{ trans('feed.table.group_type') }}
                            </th>
                            <th>
                                {{ trans('feed.table.company') }}
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
<div class="modal fade" data-id="0" data-backdrop="static" id="participate_visibility_preview" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('Cronofy.session_list.title.participants') }}
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <table class="table custom-table" id="visible_to_participate_tbl">
                    <thead>
                        <tr>
                            <th>
                                {{ trans('Cronofy.session_list.table.no') }}
                            </th>
                            <th>
                                {{ trans('Cronofy.session_list.table.name') }}
                            </th>
                            <th>
                                {{ trans('Cronofy.session_list.table.email') }}
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
<div class="modal fade" data-id="0" data-backdrop="static" id="user_preview" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('adminalert.table.user_list') }}
                </h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fal fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <table class="table custom-table" id="userlist_tbl">
                    <thead>
                        <tr>
                            <th>
                                {{trans('adminalert.table.no')}}
                            </th>
                            <th>
                                {{trans('adminalert.table.user_name')}}
                            </th>
                            <th>
                                {{trans('adminalert.table.user_email')}}
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
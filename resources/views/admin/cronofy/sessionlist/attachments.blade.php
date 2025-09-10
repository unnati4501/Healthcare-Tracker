<!-- .session-attachments -->
<div class="card ">
    <!-- search-block -->
    <div class="card search-card">
        <div class="card-body">
            <h4 class="d-md-none">
                {{ trans('buttons.general.filter') }}
            </h4>
            
            <div class="search-outer d-md-flex justify-content-between">
                <h5 class="text-end">
                    {{ trans('Cronofy.session_details.attachments.labels.title') }}
                </h5>
                @if (Route::currentRouteName() == 'admin.cronofy.sessions.show')
                <a class="btn btn-outline-primary upload-attachments" data-bs-target="#bulk-upload-model-box" data-bs-toggle="modal" href="javascript:vodi(0);">
                    <i class="far fa-upload me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('Cronofy.session_details.buttons.add_attachment') }}
                    </span>
                </a>
                @endif
            </div>
        </div>
    </div>
    <!-- /.search-block -->
    <div class="card-body">
        <div class="card-table-outer">
            <div class="table-responsive">
                <table class="table custom-table" id="sessionAttachments">
                    <thead>
                        <tr>
                            <th>
                                {{ trans('Cronofy.session_details.attachments.table.file_name') }}
                            </th>
                            <th>
                                {{ trans('Cronofy.session_details.attachments.table.datetime') }}
                            </th>
                            <th class="no-sort th-btn-2">
                                {{ trans('Cronofy.session_details.attachments.table.action') }}
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
<!-- /.session-attachments -->
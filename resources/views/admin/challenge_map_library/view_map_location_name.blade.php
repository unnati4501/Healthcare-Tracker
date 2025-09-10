<div class="modal fade" data-backdrop="static" data-id="0" id="map_location_name_preview" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('challengeMap.modal.map_location.location_name') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <table class="table custom-table" id="map_location_name">
                    <thead>
                        <tr>
                            <th>
                                {{ trans('challengeMap.modal.map_location.no') }}
                            </th>
                            <th>
                                {{ trans('challengeMap.modal.map_location.name_of_location') }}
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
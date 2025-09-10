<div category="dialog" class="modal fade" data-id="0" id="location-specific-ws-slot"  data-backdrop="static" tabindex="-1">
    <div category="document" class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title"></h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-lg-6 col-xl-6">
                    <div class="form-group">
                        Select Wellbeing Specialist
                        {{ Form::select('location_specific_wellbeing_sp_ids[]', $wellbeingSp, $dtWsIds , ['class' => 'form-control select2 location_specific_wellbeing_sp_ids', 'id' => 'location_specific_wellbeing_sp_ids', 'data-placeholder' => 'Select Staff', 'multiple' => true, 'disabled' => $dt_servicemode,  'data-allow-clear'=>'false']) }}
                    </div>
                </div>
                <hr/>
                <div class="table-responsive set-availability-block">
                    <table class="table custom-table" id="dtspecificwshours">
                        <thead>
                            <tr>
                                <th>Wellbeing Specialists</th>
                                <th width="110">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <input type="hidden" name="selectedWsIdsHidden" id="selectedWsIdsHidden" value="8006,8018">
                            @foreach($dtWsNames as $key => $ws)
                                <tr id="ws_{{$ws['id']}}" data-from="db">
                                    <td>{{ $ws['user_name'] }}</td>
                                    <td class="text-center"><a class="action-icon bs-calendar-slidebar" id="{{ $ws['id'] }}" href="javascript:;" data-toggle="canvas" data-target="#bs-canvas-right" aria-expanded="false" aria-controls="bs-canvas-right" title="Edit">
                                        <i class="far fa-edit">
                                        </i>
                                    </a></td>
                                </tr>
                            @endforeach
                            <div class="set_dynamic_wbs"></div>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
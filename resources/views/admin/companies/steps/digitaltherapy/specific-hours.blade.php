<div id="bs-canvas-right" class="bs-canvas bs-canvas-anim bs-canvas-right position-fixed bg-white shadow h-100 p-0">
    <header class="bs-canvas-header p-3 overflow-auto border-bottom d-flex justify-content-between align-items-center">
        <!-- <button type="button" class="bs-canvas-close close" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
        <h5 class="d-inline-block mb-0">Date Specific Availability</h5>
        <button type="button" class="btn-close text-reset font-11 bs-canvas-close close" aria-label="Close" aria-expanded="true"></button>
    </header>
    <div class="bs-canvas-content py-2">
        <!-- <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasExampleLabel">Offcanvas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div> -->
        <div class="offcanvas-body d-flex justify-content-center p-0">
            {{ Form::hidden('wsId', '', ['class' => 'form-control', 'id' => "specificwsId"]) }}
            <div data-bs-toggle="calendar" id="calendar_offcanvas" class="rounded">

            </div>
            <div class="hiddenfields">
                @if(isset($dtSpecificSlot) && !empty($dtSpecificSlot))
                    @foreach($dtSpecificSlot as $slot)
                        @if(!is_null($slot->location_id))
                            @include('admin.companies.steps.digitaltherapy.location-specific-slot-input', [
                                'location_id' => $slot->location_id,
                                'start_time' => date('H:i', strtotime($slot->start_time)),
                                'end_time' => date('H:i', strtotime($slot->end_time)),
                                'datestamp' => strtotime(date('Y-m-d', strtotime($slot->date))) . '000',
                                'date_input' => date('Y-m-d', strtotime($slot->date)),
                                'key' => $slot->ws_id,
                                'id' => $slot->id,
                            ])
                        @else 
                            @include('admin.companies.steps.digitaltherapy.specific-slot-input', [
                                'start_time' => date('H:i', strtotime($slot->start_time)),
                                'end_time' => date('H:i', strtotime($slot->end_time)),
                                'datestamp' => strtotime(date('Y-m-d', strtotime($slot->date))) . '000',
                                'date_input' => date('Y-m-d', strtotime($slot->date)),
                                'key' => $slot->ws_id,
                                'id' => $slot->id,
                            ])
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>    
</div>
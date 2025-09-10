<div class="d-flex align-items-center edit-slot-block-location" data-id="{{ $id }}" data-from="{{ $from }}">
    <div class="set-availability-date-time d-flex slot-control-block-location flex-grow-1">
        <div class="set-availability-inner">
            {{ Form::text('slot_start_time', $start_time, ['class' => 'locationtime start form-control', 'autocomplete' => "off"]) }}
        </div>
        <div class="set-availability-inner">
            {{ Form::text('slot_end_time', $end_time, ['class' => 'locationtime end form-control', 'autocomplete' => "off"]) }}
        </div>
        <div class="set-availability-inner flex-grow-1">
            @if($edit)
            {{ Form::select('dt_wellbeing_slot_ids[]', [], old('dt_wellbeing_slot_ids[]'), ['class' => 'form-control select2 dt_wb_ids', 'id' => 'dt_wb_ids', 'data-placeholder' => 'Select Wellbeing', 'multiple' => true, 'data-close-on-select' => 'false','disabled' => $dt_servicemode,  'data-allow-clear'=>'false']) }}
            @else
            {{ Form::select('dt_wellbeing_slot_ids[]', [], null , ['class' => 'form-control select2 dt_wb_ids', 'id' => 'dt_wb_ids', 'data-placeholder' => 'Select Wellbeing', 'multiple' => true, 'disabled' => $dt_servicemode,  'data-allow-clear'=>'false']) }}
            @endif
        </div>
    </div>
    <div class="d-flex set-availability-btn-area justify-content-end slot-action-block-location">
        <a class="edit-slot-location action-icon me-3 ms-3 save-edit-slot-location" href="javascript:void(0);" title="Save Slot">
            <i class="far fa-save">
            </i>
        </a>
        <a class="delete-slot-location action-icon danger cancel-edit-slot-location" href="javascript:void(0);" title="Cancel">
            <i class="far fa-times">
            </i>
        </a>
    </div>
</div>
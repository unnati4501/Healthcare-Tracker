<div class="slots-wrapper specific-preview-slot-block specific_{{ $id }}" data-id="{{ $id }}">
    <div class="list-group-item p-0">
        <div class="d-flex p-2 justify-content-between align-items-center">
            <p class="mb-0">{{ $time }}</p>
            {{ Form::hidden("specific_slots_temp[$key][$datestamp][start_time][$id]", $start_time, ['class' => 'start-time-data']) }}
            {{ Form::hidden("specific_slots_temp[$key][$datestamp][end_time][$id]", $end_time, ['class' => 'end-time-data']) }}
            <div style="display: {{$previous_slot_block}}">
                <a href="javascript:void(0);" class="p-2 text-primary edit-specific-slot" title="Edit Slot" data-id="{{ $id }}"> <i class="fa fa-pencil"></i> </a>
                <a href="javascript:void(0);" class="p-2 text-danger specific-delete-slot action-icon danger specific-remove-slot" data-id="{{ $id }}"> <i class="fa fa-trash"></i> </a>
            </div>
        </div>
    </div>
</div>

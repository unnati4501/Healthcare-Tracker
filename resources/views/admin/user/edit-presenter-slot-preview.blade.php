<div class="d-flex align-items-center edit-presenter-slot-block" data-id="{{ $id }}">
    <div class="set-availability-date-time d-flex slot-control-block">
        <div class="set-availability-inner">
            {{ Form::text('presenter_slot_start_time', $start_time, ['class' => 'time start form-control', 'autocomplete' => "off"]) }}
        </div>
        <div class="set-availability-inner">
            {{ Form::text('presenter_slot_end_time', $end_time, ['class' => 'time end form-control', 'autocomplete' => "off"]) }}
        </div>
    </div>
    <div class="d-flex set-availability-btn-area justify-content-end slot-action-block">
        <a class="edit-presenter-slot action-icon me-3 ms-3 save-edit-presenter-slot" href="javascript:void(0);" title="Save Slot">
            <i class="far fa-save">
            </i>
        </a>
        <a class="delete-presenter-slot action-icon danger cancel-edit-presenter-slot" href="javascript:void(0);" title="Cancel">
            <i class="far fa-times">
            </i>
        </a>
    </div>
</div>
<div class="d-flex align-items-center add-spefic-new-slot-block ps-2 pe-2 pb-2">
    <div class="set-availability-date-time d-flex slot-control-block">
        <div class="set-availability-inner">
            {{ Form::text('slot_start_time', null, ['class' => 'time start form-control', 'autocomplete' => "off"]) }}
        </div>
        <div class="set-availability-inner">
            {{ Form::text('slot_end_time', null, ['class' => 'time end form-control', 'autocomplete' => "off"]) }}
        </div>
    </div>
    <div class="d-flex set-availability-btn-area justify-content-end slot-action-block">
        <a class="edit-slot action-icon me-3 ms-3 save-specific-new-slot" href="javascript:void(0);" title="Add Slot">
            <i class="far fa-save">
            </i>
        </a>
        <a class="delete-slot action-icon danger close-specific-add-new-slot" href="javascript:void(0);" title="Cancel">
            <i class="far fa-times">
            </i>
        </a>
    </div>
</div>
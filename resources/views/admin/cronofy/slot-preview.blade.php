<div class="d-flex align-items-center preview-slot-block" data-id="{{ $id }}">
    <div class="set-availability-date-time d-flex text-primary">
        <b class="slot-timmings">
            {{ $time }}
        </b>
        {{ Form::hidden("slots[$key][start_time][$id]", $start_time, ['class' => 'start-time-data']) }}
        {{ Form::hidden("slots[$key][end_time][$id]", $end_time, ['class' => 'end-time-data']) }}
    </div>
    <div class="d-flex set-availability-btn-area justify-content-end">
        <a class="edit-slot action-icon edit-slot" href="javascript:void(0);" title="Edit Slot">
            <i class="far fa-edit">
            </i>
        </a>
        <a class="delete-slot action-icon danger remove-slot" href="javascript:void(0);" title="Remove Slot">
            <i class="far fa-trash">
            </i>
        </a>
        <a class="action-icon text-info add-slot" href="javascript:void(0);" title="Add Another Slot3">
            <i class="far fa-plus">
            </i>
        </a>
    </div>
</div>
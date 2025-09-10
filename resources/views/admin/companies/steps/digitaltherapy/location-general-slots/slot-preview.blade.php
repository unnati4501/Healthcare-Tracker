<div class="d-flex align-items-center preview-slot-block-location preview-slot-block" data-id="{{ $id }}" data-from="{{ $from }}">
    <div class="set-availability-date-time d-flex text-primary w-50">
        <b class="slot-timmings-location">
            {{ $time }}
        </b>
        {{ Form::hidden("location_slots[$key][start_time][$id]", $start_time, ['class' => 'start-time-data-location']) }}
        {{ Form::hidden("location_slots[$key][end_time][$id]", $end_time, ['class' => 'end-time-data-location']) }}
    </div>
    <small class="user d-block set-availability-date-time ws_selected_users text-secondary fw-bold" title="{!! $ws_selected !!}">{!! $ws_selected !!}</small>
    <div class="ws_hidden_fields">
        {!! $ws_hidden_field !!}
    </div>
    @if($dt_servicemode == false)
    <div class="d-flex set-availability-btn-area justify-content-end">
        <a class="edit-slot-location action-icon edit-slot-location" href="javascript:void(0);" title="Edit Slot">
            <i class="far fa-edit">
            </i>
        </a>
        <a class="delete-slot action-icon danger remove-slot-location" href="javascript:void(0);" title="Remove Slot">
            <i class="far fa-trash">
            </i>
        </a>
        <a class="action-icon text-info add-location-slot" href="javascript:void(0);" title="Add Another Slot2">
            <i class="far fa-plus">
            </i>
        </a>
    </div>
    @endif
</div>

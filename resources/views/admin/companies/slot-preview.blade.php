<div class="d-flex align-items-center preview-slot-block" data-id="{{ $id }}">
    <div class="set-availability-date-time d-flex text-primary w-50">
        <b class="slot-timmings">
            {{ $time }}
        </b>
        {{ Form::hidden("slots[$key][start_time][$id]", $start_time, ['class' => 'start-time-data']) }}
        {{ Form::hidden("slots[$key][end_time][$id]", $end_time, ['class' => 'end-time-data']) }}
    </div>
    <small class="user d-block set-availability-date-time ws_selected_users text-secondary fw-bold" title="{!! $ws_selected !!}">{!! $ws_selected !!}</small>
    <div class="ws_hidden_fields">
        {!! $ws_hidden_field !!}
    </div>
    @if($dt_servicemode == false)
    <div class="d-flex set-availability-btn-area justify-content-end">
        <a class="edit-slot action-icon edit-slot" href="javascript:void(0);" title="Edit Slot">
            <i class="far fa-edit">
            </i>
        </a>
        <a class="delete-slot action-icon danger remove-slot" href="javascript:void(0);" title="Remove Slot">
            <i class="far fa-trash">
            </i>
        </a>
        <a class="action-icon text-info add-slot" href="javascript:void(0);" title="Add Another Slot1">
            <i class="far fa-plus">
            </i>
        </a>
    </div>
    @endif
</div>

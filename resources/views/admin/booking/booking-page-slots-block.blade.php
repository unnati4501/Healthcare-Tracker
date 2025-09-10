<label class="custom-radio {{ (($disableSlot == true) ? 'pe-none grayed-out-color enable-me bg-light custom-radio-disabled' : '') }}">
    @if($slot->start_time && $slot->end_time)
    <span>
        {{ "{$slot->start_time} - {$slot->end_time}" }}
    </span>
    <hr/>
    @endif
    <img alt="{{ $slot->presenter_name }}" class="presenter-avatar" src="{{ (($slot->user) ? $slot->user->logo : $slot->logo) }}"/>
    {{ $slot->presenter_name }}
    {{ Form::radio('slot', $slot->id, ($slot->id == $bookingLog->slot_id ? true : false), ['id' => "slot_$slot->id"]) }}
    <span class="checkmark">
    </span>
    <span class="box-line">
    </span>
</label>
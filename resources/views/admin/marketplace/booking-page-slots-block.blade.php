{{-- @if(empty($bookingLogExists))
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
@endif --}}
<?php /*if(!empty($bookingLogExists)){*/ ?>
<?php /*foreach ($splitData['splitslots'] as $k => $v){
    $class = "";
    $disableSlot = false;
    $selected = false;
    //echo strtotime($v['from']) .'---'.strtotime($bookingLogExists);
    // if(strtotime($v['from']) == strtotime($bookingLogExists->start_time)){
    //     $disableSlot = true;
    // }else{
    //     $disableSlot = false;
    // }
    if(!empty($bookingLogExists)){
        foreach($bookingLogExists as $key=>$value){
            if(strtotime($v['from']) == strtotime($value['start_time'])){
                $disableSlot = true;
            }
        }
    }
    if(!empty($bookedSlots)){
        foreach($bookedSlots as $key=>$value){
            if(strtotime($v['from']) == strtotime($value['start_time'])){
                $disableSlot = true;
            }
        }
    }
    if ($disableSlot == true) {
        $class = "pe-none grayed-out-color enable-me bg-light custom-radio-disabled";
    } ?>
    <label class="custom-radio <?php echo $class;?>" >
    <span>
        <?php  echo $v['from'] .' - '.$v['to']?>
    </span>
    <hr/>
    <img alt="{{ $splitData['slot']['presenter_name'] }}" class="presenter-avatar" src="{{ (($splitData['slot']['user_logo']) ? $splitData['slot']['user_logo'] : $splitData['slot']['user_logo']) }}"/>
    {{ $splitData['slot']['presenter_name'] }}
    @if(!empty($selectedBookingSlots->start_time))
    {{ Form::radio('slot', $splitData['slot']['id'], (strtotime($v['from']) == strtotime($selectedBookingSlots->start_time) ? true : false), ['id' => "slot_$k",'data-slot-start-time'=>$v['from']]) }}
    @else
    {{ Form::radio('slot', $splitData['slot']['id'], false, ['id' => "slot_$k",'data-slot-start-time'=>$v['from']]) }}
    @endif
    <span class="checkmark">
    </span>
    <span class="box-line">
    </span>
</label>
    ?>
    
<?php }*/
/*} */?>

<?php 
//dump($bookingLogExists);
 ?>
<div class="presenter-item">
    <div class="presenter-top">
        <div class="presenter-top-left">
            <img src="{{ $splitData['slot']['user_logo'] }}" alt="">
            <span class="presenter-name">{{$splitData['slot']['presenter_name']}}</span>
        </div>
        <span class="text-nowrap">Duration : {{$eventDuration}}</span>
    </div>
    <div class="presenter-bottom">
        <?php
            $i = 1; 
            foreach ($splitData['splitslots'] as $key=>$value){
                $checked = "";
                if(!empty($selectedBookingSlots->start_time)){
                    if(strtotime($value['from']) == strtotime($selectedBookingSlots->start_time) && $splitData['slot']['id'] == $selectedBookingSlots->slot_id){
                        $checked = "checked";
                    }
                }
                $disableSlot = "";
                if(!empty($bookingLogExists)){
                    foreach($bookingLogExists as $bookingLogExistsKey=>$bookingLogExistsValue){
                        if(((strtotime($value['date'].' '.$value['from']) >= strtotime($bookingLogExistsValue['start_time'])) && (strtotime($value['date'].' '.$value['to']) <= strtotime($bookingLogExistsValue['end_time']))) || ((strtotime($value['date'].' '.$value['from']) <= strtotime($bookingLogExistsValue['start_time'])) && (strtotime($value['date'].' '.$value['to']) >= strtotime($bookingLogExistsValue['end_time'])))){
                            $disableSlot = "disabled";
                        }
                    }
                }
                if(!empty($bookedSlots)){
                    foreach($bookedSlots as $bookedSlotsKey=>$bookedSlotsValue){
                        if(((strtotime($value['date'].' '.$value['from']) >= strtotime($bookedSlotsValue['start_time'])) && (strtotime($value['date'].' '.$value['to']) <= strtotime($bookedSlotsValue['end_time'])) || (strtotime($value['date'].' '.$value['from']) <= strtotime($bookedSlotsValue['start_time'])) && (strtotime($value['date'].' '.$value['to']) >= strtotime($bookedSlotsValue['end_time']))) && ($splitData['slot']['id'] == $bookedSlotsValue['slot_id'])){
                            $disableSlot = "disabled";
                        }
                    }
                }
            ?>
            @if($i == 8)
                <div class="other-timings">
            @endif
            <label class="custom-radio {{$checked}} {{$disableSlot}} slot_{{$splitData['slot']['id']}}_{{$i}}" >
                {{$value['from']}}
                <input type="radio" name="slot" id="slot_{{$splitData['slot']['id']}}_{{$i}}" value="{{$splitData['slot']['id']}}" class="form-control slots" data-slot-start-time="{{$value['from']}}" data-slot-end-time="{{$value['to']}}">
                <span class="box-line"></span>
            </label>
        <?php $i++;
        } ?>
        <?php  if($i > 8 ) { ?></div><a href="javascript:void(0)" class="view-more">+ Read More</a><?php } ?>         
    </div>
</div>
<?php ?>

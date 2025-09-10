<div class="presenter-item border-0 pe-2">
    <div class="presenter-item-top">
        <div class="presenter-top border-0 p-0">
            <label class="custom-radio w-100 mb-0">
                <div class="presenter-top-left border-0 m-0">
                    <img src="{{ $splitData['slot']['user_logo'] }}" alt="">
                    <span class="presenter-name">{{ $splitData['slot']['presenter_name'] }}</span>
                </div>
                <input type="radio" name="ws_user" class="roleGroup form-control" value="{{ $splitData['slot']['user_id'] }}" slotId="{{ $splitData['slot']['id'] }}">
                <span class="checkmark"></span>
                <span class="box-line"></span>
            </label>
        </div>
    </div>
</div>
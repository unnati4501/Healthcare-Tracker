<div class="presenter-item border-0 pe-2 w-100">
    <div class="presenter-item-top">
        <div class="presenter-top border-0 p-0">
            <label class="custom-radio {{ ($edit && $selectedWS != $wsId) ? 'custom-radio-disabled' : '' }}  w-100 mb-0">
                <div class="presenter-top-left border-0 m-0">
                    <img src="{{ $wsImage }}" alt="">
                    <span class="presenter-name">{{ $wsName }}</span>
                </div>
                <input type="radio" name="ws_user" class="roleGroup form-control" {{ ($edit && $selectedWS == $wsId) ? 'checked=checked' : '' }} {{ ($edit && $selectedWS != $wsId) ? 'disabled=true' : '' }} value="{{ $wsId }}">
                <span class="checkmark"></span>
                <span class="box-line"></span>
            </label>
        </div>
    </div>
</div>
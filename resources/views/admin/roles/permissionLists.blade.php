<select class="form-control" id="set_privileges" multiple="multiple" name="set_privileges">
    @foreach($permissionData as $key => $value)
        @foreach($value['children'] as $val)
		    <option data-section="{{ $value['display_name'] }}" value="{{ $val['id'] }}">
		        {{ $val['display_name'] }}
		    </option>
	    @endforeach 
    @endforeach
</select>
<select class="form-control" id="set_privileges" multiple="multiple" name="set_privileges">
    @foreach($cpFeaturesData as $key => $value)
        @foreach($value['children'] as $val)
		    <option data-section="{{ $value['name'] }}" value="{{ $val['id'] }}">
		        {{ $val['name'] }}
		    </option>
	    @endforeach 
    @endforeach
</select>
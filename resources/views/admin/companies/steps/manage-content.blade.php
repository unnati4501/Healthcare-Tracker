<div id="setPermissionList" class="tree-multiselect-box">
    @if(isset($selectedContent))
    <select id="group_content" name="group_content" multiple="multiple" class="form-control" >
        @foreach($masterContentType as $masterKey => $masterData)
            @foreach($masterData['subcategory'] as $subcategoryKey => $subcategoryData)
                @foreach($subcategoryData[$masterData['categoryName']] as $key => $value)
                    <option value="{{ $masterData['id'].'-'.$subcategoryData['id'].'-'.$key }}" data-section="{{ $masterData['categoryName'] }}/{{ $subcategoryData['subcategoryName'] }}"  {{ (!empty(old('manage_content', $selectedContent)) && in_array($masterData['id'].'-'.$subcategoryData['id'].'-'.$key, old('manage_content', $selectedContent)))? 'selected' : ''   }} >{{ $value }}</option>
                @endforeach
            @endforeach
        @endforeach
    </select>
    @else
    <select id="group_content" name="group_content" multiple="multiple" class="form-control" >
        @foreach($masterContentType as $masterKey => $masterData)
            @foreach($masterData['subcategory'] as $subcategoryKey => $subcategoryData)
                @foreach($subcategoryData[$masterData['categoryName']] as $key => $value)
                    <option value="{{ $masterData['id'].'-'.$subcategoryData['id'].'-'.$key }}" data-section="{{ $masterData['categoryName'] }}/{{ $subcategoryData['subcategoryName'] }}"  {{ (!empty(old('manage_content')) && in_array($masterData['id'].'-'.$subcategoryData['id'].'-'.$key, old('manage_content')))? 'selected' : ''   }} >{{ $value }}</option>
                @endforeach
            @endforeach
        @endforeach
    </select>
    @endif
</div>
<span id="group_content-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
    {{trans('labels.group.group_content_required')}}
</span>
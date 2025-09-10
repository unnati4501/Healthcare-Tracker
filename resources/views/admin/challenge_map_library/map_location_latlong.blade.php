<tr id="{{$trId}}">

    <td>
        <div class="form-group mb-0">
            {{ Form::label('Location', trans('challengeMap.form.labels.location')) }} <span class="location-label">{{$id}}</span>
            @if(!empty($_GET['returnData']))
                @if(str_contains($_GET['returnData'], $recordsId))
                    <span style="color: red">*</span>
                @endif
            @endif
            {{ Form::text('location[]', old('location', $lat_long), ['id' => $locationId, 'rows' => 3, 'class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.location'), 'readonly' => 'true']) }}
        </div>
    </td>

    <td class="th-btn-sm">
        @if($edit)
        <a href="javascript:;" id="{{$mapeditId}}" recordsId="{{$recordsId}}" class="action-icon text-info map-edit">
            <i class="far fa-plus"></i>
        </a>
        @endif
        @if($activeAttechCount <= 0)
        <a href="javascript:;" id="{{$deletedId}}" recordsId="{{$recordsId}}" class="action-icon text-danger map-remove">
            <i class="far fa-trash-alt"></i>
        </a>
        @endif
    </td>

</tr>
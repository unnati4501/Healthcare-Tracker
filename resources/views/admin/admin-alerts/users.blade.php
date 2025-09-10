<tr id="row_{{$id}}">
    <td>
        <span id="user_name_span_{{ $id }}">{{ $user_name ?? "" }}</span>
    </td>
    <td>
        <span id="user_email_span_{{ $id }}">{{ $user_email ?? "" }}</span>
        {{ Form::hidden("user_name[]", $user_name ?? "", ['class' => 'form-control list_user_name', 'id' => 'user_name_'.$id]) }}
        {{ Form::hidden("user_email[]", $user_email ?? "", ['class' => 'form-control list_user_email', 'id' => 'user_email_'.$id]) }}
    </td>
    <td class="text-center">
        <a class="action-icon" orderId="{{ $id }}" href="javascript:void(0);" id="editUser" title="{{ trans('adminalert.buttons.edit') }}">
            <i class="far fa-edit">
            </i>
        </a>
        <a class="action-icon text-danger user-remove" orderId="{{ $id }}" id="user_remove_{{$id}}" href="javascript:void(0);" title="{{ trans('adminalert.buttons.delete') }}">
            <i class="far fa-trash">
            </i>
        </a>
    </td>
</tr>
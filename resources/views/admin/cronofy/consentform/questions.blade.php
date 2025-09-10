<tr id="row_{{$id}}">
    <td>
        {{ $key }}
    </td>
    <td>
        <span id="title_{{ $id }}">{{ $title }}</span>
        {{ Form::hidden("question_title[]", $title, ['class' => 'form-control list_question_title', 'id' => 'question_name_'.$id]) }}
        {{ Form::hidden("question_description[]", $description, ['class' => 'form-control list_question_description', 'id' => 'question_description_'.$id]) }}
    </td>
    <td class="text-center">
        <a class="action-icon" orderId="{{ $id }}" href="javascript:void(0);" id="editQuestion" title="{{ trans('Cronofy.session_list.buttons.tooltips.edit') }}">
            <i class="far fa-edit">
            </i>
        </a>
        <a class="action-icon text-danger question-remove {{ ($key <= 1) ? 'd-none' : 'show' }}" orderId="{{ $id }}" id="question_remove_{{$id}}" href="javascript:void(0);" title="{{ trans('services.buttons.delete_subcategory') }}">
            <i class="far fa-trash">
            </i>
        </a>
    </td>
</tr>
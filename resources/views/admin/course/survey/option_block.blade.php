<tr>
    <td class="w120px">
        <div class="form-group mb-0">
            {{ Form::select("score[{$id}][{$oid}]", $surveys_score, (isset($option->score) ? $option->score : null), ['class' => 'form-control select2', 'id' => "score_{$id}_{$oid}", 'placeholder' => trans('masterclass.survey.form.placeholder.score'), 'data-placeholder' => trans('masterclass.survey.form.placeholder.score'), 'data-allow-clear' => 'false', 'disabled' => $courseStatus]) }}
        </div>
    </td>
    <td>
        <div class="form-group mb-0">
            {{ Form::text("option[{$id}][{$oid}]", ($option->choice ?? null), ['class' => 'form-control input-sm option_required', 'placeholder' => trans('masterclass.survey.form.placeholder.option'), 'id' => "option_{$id}_{$oid}", 'maxlength' => 50, 'onkeyup' => "setDeleteVisibility(this)"]) }}
        </div>
    </td>
    @if(!$courseStatus)
    <td class="qusOption-btn-area show_del">
        <button class="btn btn-sm btn-default list-delete-btn remove-option {{ ((isset($edit) && $edit == true) ? 'old-added' : '') }}" data-oid="{{ $oid }}" data-qid="{{ $id }}" title="{{ trans('masterclass.survey.buttons.delete_op') }}" type="button">
            <i class="fas fa-trash text-danger">
            </i>
        </button>
        <button class="btn btn-sm btn-default add-option {{ ((isset($edit) && $edit == true) ? 'old-added' : '') }}" data-oid="{{ $oid }}" data-qid="{{ $id }}" title="{{ trans('masterclass.survey.buttons.add_op') }}" type="button">
            <i class="far fa-plus text-success">
            </i>
        </button>
    </td>
    @endif
</tr>
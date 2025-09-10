<tr>
    <td class="th-btn-4">
        <div class="form-group mb-0">
            {{ Form::text("ingredients[{$ingdCount}]", $ingredient, ['class' => 'form-control ingredients_required', 'placeholder' => trans('recipe.form.placeholder.ingredients'), 'maxlength' => 100]) }}
        </div>
    </td>
    <td class="th-btn-sm {{ $show_del }}">
        <a class="action-icon text-danger ingriadiant-remove" href="javascript:void(0);" title="{{ trans('recipe.buttons.delete_ingd') }}">
            <i class="far fa-trash">
            </i>
        </a>
        <a class="action-icon text-success" href="javascript:void(0);" id="ingriadiantAdd" title="{{ trans('recipe.buttons.add_ingd') }}">
            <i class="far fa-plus">
            </i>
        </a>
    </td>
</tr>
<input id="total-form-subcategories" type="hidden" value="1"/>
<div class="col-xxl-12 col-md-10">
<tr class="custom-subcategy-wrap" data-order="1">
    <td class="th-btn-4">
        <div class="form-group mb-0">
            <span class="subcategory_logo[1]" id="span_logo_1">
                <img id="subcategory_src_1" width="36" height="36"  src="data:image/png;base64,<?php echo base64_encode(file_get_contents(config('zevolifesettings.fallback_image_url.services.subcategories.other'))) ?>">
            </span>
            <?php $default = 'data:image/png;base64,'.base64_encode(file_get_contents(config('zevolifesettings.fallback_image_url.services.subcategories.other'))).''?> 
            {{ Form::hidden("subcategory_logo[1]", $default, ['class' => 'form-control', 'id' => 'subcategory_logo_1']) }}
            {{ Form::hidden("subcategory_logo_name[1]", 'default.png' , ['class' => 'form-control', 'id' => 'subcategory_logo_name_1']) }}
        </div>
    </td>
    <td class="th-btn-4">
        <div class="form-group mb-0">
            <span class="subcategory_name[1]" id="span_name_1">Default</span>
            {{ Form::hidden("subcategory_name[1]", 'Default', ['class' => 'form-control', 'id' => 'subcategory_name_1']) }}
            {{ Form::hidden("is_default[1]", '1', ['class' => 'form-control', 'id' => 'is_default_1']) }}
        </div>
    </td>
    <td class="th-btn-sm {{ $show_del }}">
        <a class="action-icon text-danger subcategory-remove" id="subcategory_remove_1" href="javascript:void(0);" title="{{ trans('services.buttons.delete_subcategory') }}">
            <i class="far fa-trash">
            </i>
        </a>
        <a class="action-icon subcategory-edit" href="javascript:void(0);" title="{{ trans('services.buttons.edit_subcategory') }}">
            <i class="far fa-edit">
            </i>
        </a>
    </td>
</tr>
</div>
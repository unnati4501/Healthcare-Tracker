<input id="total-form-subcategories" type="hidden" value="{{sizeof($subCategories)}}"/>
<div class="col-xxl-12 col-md-10">
@foreach($subCategories as $index => $subCategory)
    @php
    $serviceSubCategory  = App\Models\ServiceSubCategory::find($subCategory->id);
    $wbsCount            = \DB::table('users_services')->select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"))->leftJoin('users', 'users.id', '=', 'users_services.user_id')->where('users_services.service_id', $subCategory->id)->distinct()->get()->toArray();
    $totalWellbeingSp    = sizeof($wbsCount);
    @endphp
    <tr class="custom-subcategy-wrap" data-order="{{$index}}" data-id="{{$serviceSubCategory['id']}}" data-wbsassigned="{{$totalWellbeingSp}}">
        <td class="th-btn-4">
            <div class="form-group mb-0">
                <span class='subcategory_logo[{{$index}}]' id="span_logo_{{$index}}">
                    <img id="subcategory_src_{{$index}}" width="36" height="36" src="{{ $serviceSubCategory->getFirstMediaUrl('sub_category_logo') }}">
                </span>
                {{ Form::hidden("subcategory_logo[".$index."]", $serviceSubCategory->getFirstMediaUrl('sub_category_logo'), ['class' => 'form-control', 'id' => 'subcategory_logo_'.$index]) }}
                {{ Form::hidden("subcategory_logo_name[".$index."]", (!empty($serviceSubCategory->getFirstMedia('sub_category_logo'))) ? $serviceSubCategory->getFirstMedia('sub_category_logo')->file_name : '', ['class' => 'form-control', 'id' => 'subcategory_logo_name_'.$index]) }}

            </div>
        </td>
        <td class="th-btn-4">
            <div class="form-group mb-0">
                <span class="subcategory_name[1]" id="span_name_{{$index}}">{{$subCategory['name']}}</span>
                {{ Form::hidden("subcategory_name[".$index."]", '', ['class' => 'form-control', 'id' => 'subcategory_name_'.$index]) }}
                {{ Form::hidden("subcategory_id[".$index."]", $subCategory['id'], ['class' => 'form-control', 'id' => 'subcategory_id_'.$index]) }}
            </div>
        </td>
        <td class="th-btn-sm {{ $show_del }}">
            <a class="action-icon text-danger subcategory-remove" id="subcategory_remove_0"  href="javascript:void(0);" title="{{ trans('services.buttons.delete_subcategory') }}">
                <i class="far fa-archive">
                </i>
            </a>
            <a class="action-icon subcategory-edit" href="javascript:void(0);" title="{{ trans('services.buttons.edit_subcategory') }}">
                <i class="far fa-edit">
                </i>
            </a>
        </td>
    </tr>
@endforeach
</div>
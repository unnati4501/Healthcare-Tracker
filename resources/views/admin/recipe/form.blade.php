<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('chef', trans('recipe.form.labels.author')) }}
                @if(isset($chef_disabled_flag) && $chef_disabled_flag == true)
                {{ Form::text('chef', $recordData->chefData['name'], ['class' => "form-control", 'disabled' => true]) }}
                @else
                {{ Form::select('chef', $chefs, old('chef_id', (isset($recordData) ? $recordData->chef_id : '')), ['class' => "form-control select2", 'id' => 'chef', 'data-allow-clear' => 'true', 'data-placeholder' => trans('recipe.form.placeholder.author'), 'placeholder' => trans('recipe.form.placeholder.author'), 'disabled' => (isset($chef_disabled_flag) ? $chef_disabled_flag : false)]) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('title', trans('recipe.form.labels.recipe_name')) }}
                {{ Form::text('title', old('title', ($recordData->title ?? '')), ['class' => 'form-control', 'placeholder' => trans('recipe.form.placeholder.recipe_name'), 'id' => 'title']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('cooking_time', trans('recipe.form.labels.time')) }}
                {{ Form::text('cooking_time', old('cooking_time', (isset($recordData) ? timeToDecimal($recordData->cooking_time) : '')), ['class' => 'form-control numeric', 'placeholder' => trans('recipe.form.placeholder.time'), 'id' => 'cooking_time', 'maxlength' => 4]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('calories', trans('recipe.form.labels.calories')) }}
                {{ Form::text('calories', old('calories', (isset($recordData) ? $recordData->calories : '')), ['class' => 'form-control numeric-decimal', 'placeholder' => trans('recipe.form.placeholder.calories'), 'id' => 'calories', 'maxlength' => 10]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('servings', trans('recipe.form.labels.servings')) }}
                {{ Form::text('servings', old('servings', (isset($recordData) ? $recordData->servings : '')), ['class' => 'form-control numeric', 'placeholder' => trans('recipe.form.placeholder.servings'), 'id' => 'servings', 'maxlength' => 3]) }}
            </div>
        </div>
        @permission('manage-goal-tags')
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('servings', trans('recipe.form.labels.goal_tags')) }}
                {{ Form::select('goal_tag[]', $goalTags, ($goal_tags ?? null), ['class' => 'form-control select2', 'id' => 'goal_tag', 'multiple' => true, 'data-placeholder' => trans('recipe.form.placeholder.goal_tags')] ) }}
            </div>
        </div>
        @endauth
        @if($roleGroup == 'zevo')
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('tag', trans('recipe.form.labels.tag')) }}
                {{ Form::select('tag', $tags, ($recordData->tag_id ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('recipe.form.placeholder.tag'), 'data-placeholder' => trans('recipe.form.placeholder.tag'), 'data-allow-clear' => 'true']) }}
            </div>
        </div>
        @endif
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('type', trans('recipe.form.labels.type')) }}
                {{ Form::select('type', $recipeTypes, ($recordData->type_id ?? null), ['class' => 'form-control select2', 'id' => 'type', 'placeholder' => trans('recipe.form.placeholder.type'), 'data-placeholder' => trans('recipe.form.placeholder.type'), 'data-allow-clear' => 'true']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('Header Image', trans('recipe.form.labels.header_image')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('recipe.header_image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="header_image_preview" style="display: flex;">
                        <img id="header_image_preview" src="{{ ((!empty($recordData) && !empty($recordData->getFirstMediaUrl('header_image'))) ? $recordData->getFirstMediaUrl('header_image') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::file('header_image', ['class' => 'custom-file-input form-control', 'id' => 'header_image', 'data-width' => config('zevolifesettings.imageConversions.recipe.header_image.width'), 'data-height' => config('zevolifesettings.imageConversions.recipe.header_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.recipe.header_image'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#header_image_preview']) }}
                    {{ Form::label('header_image', ((!empty($recordData) && !empty($recordData->getFirstMediaUrl('header_image'))) ? $recordData->getFirstMedia('header_image')->name : trans('recipe.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('recipe.form.labels.sub_category') }}
    </h3>
    <div>
        @foreach ($recipeSubCategories as $subCategoryId => $subCategory)
        <label class="custom-checkbox">
            {{ $subCategory }}
            {{ Form::checkbox('recipesubcategory[]', $subCategoryId, (!empty($recordData) && in_array($subCategoryId, (array) $recordData->recipesubcategory)), ['class' => 'form-control', 'id' => "recipesubcategory_{$subCategoryId}", 'data-errplaceholder' => '#recipesubcategoryerr']) }}
            <span class="checkmark">
            </span>
            <span class="box-line">
            </span>
        </label>
        @endforeach
    </div>
    <div id="recipesubcategoryerr"></div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('recipe.form.labels.directions') }}
    </h3>
    {{ Form::textarea('description', old('description', (isset($recordData->description) ? $recordData->description : '')), ['class' => 'form-control', 'placeholder' => 'Directions', 'id' => 'description', 'rows' => 3, 'maxlength' => 1000]) }}
    <span id="description-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
        {{ trans('recipe.messages.directions_required') }}
    </span>
    <span id="description-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
        {{ trans('recipe.messages.directions_characters') }}
    </span>
</div>

<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('recipe.form.labels.recipe_images') }}
    </h3>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('', trans('recipe.messages.recipe_images')) }}
                <span class="ms-3 font-20 align-middle">
                    <i class="fal fa-info-circle c-p" data-bs-toggle="tooltip" data-bs-html="true" data-placement="bottom" title="{{ getHelpTooltipText('recipe.logo') }} <br /> {{ trans('recipe.messages.recipe_images_help_text') }}"></i>
                </span>
                <div class="custom-file">
                    {{ Form::file('image[]', ['class' => 'custom-file-input form-control', 'id' => 'image', 'data-width' => config('zevolifesettings.imageConversions.recipe.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.recipe.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.recipe.logo'), 'accept' => 'image/*', 'multiple' => true]) }}
                    {{ Form::label('image', trans('recipe.form.placeholder.choose_file'), ['class' => 'custom-file-label']) }}
                    @if($edit) {{ Form::hidden('deletedImages', '', ['id' => 'deletedImages']) }} @endif
                </div>
            </div>
        </div>
    </div>
    <div class="recipe-gallery" id="imagesPreview">
        @if($edit)
        @foreach($recordData->getAllMediaData('logo', ['w' => 512, 'h' => 512]) as $media)
        <div class="recipe-card-img">
            <div class="profile-thumnail elevation-1">
                <a class="remove-image btn btn-danger" href="javascript:void(0);" title="{{ trans('recipe.form.placeholder.delete_image') }}" data-id="{{ $media['id'] }}">
                    <i class="fal fa-trash-alt"></i>
                </a>
                <img class="profile-image-preview" src="{{ $media['url'] }}" />
            </div>
        </div>
        @endforeach
        @endif
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('recipe.form.labels.ingredients') }}
    </h3>
    <div class="row">
        <div class="col-md-9">
            <div class="table-responsive">
                <table class="table custom-table no-hover ingredient-table gap-adjust no-border ingriadiant-make-editable" id="ingriadiantTbl">
                    <tbody>
                        @forelse ($recordData->ingredients ?? [] as $key => $ingredient)
                        @include('admin.recipe.ingredients', [
                            'ingdCount' => $key,
                            'ingredient' => $ingredient,
                            'show_del' => '',
                        ])
                        @empty
                        @include('admin.recipe.ingredients', [
                            'ingdCount' => '0',
                            'ingredient' => null,
                            'show_del' => '',
                        ])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('recipe.form.labels.nutritions') }}
    </h3>
    <div class="row">
        @foreach ($nutritions as $nutritionId => $nutrition)
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('', $nutrition['display_name']) }}
                {{ Form::text($nutrition["name"], ($recordData->nutritions[$nutritionId - 1]->value ?? ''), ['class' => 'form-control input-sm numeric-decimal', 'placeholder' => $nutrition["display_name"], 'maxlength' => 10, 'id' => "nutrition{$nutritionId}"]) }}
            </div>
        </div>
        @endforeach
    </div>
</div>

@if($isSA)
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('recipe.form.labels.company_visibility') }}
    </h3>
    <div id="setPermissionList" class="tree-multiselect-box">
        <select id="recipe_company" name="recipe_company[]" multiple="multiple" class="form-control" >
            @foreach($company as $rolekey => $rolevalue)
                @foreach($rolevalue['companies'] as $key => $value)
                    @foreach($value['location'] as $locationKey => $locationValue)
                        @foreach($locationValue['department'] as $departmentKey => $departmentValue)
                            @foreach($departmentValue['team'] as $teamKey => $teamValue)
                                <option value="{{ $teamValue['id'] }}" data-section="{{ $rolevalue['roleType'] }}/{{$value['companyName']}}/{{$locationValue['locationName']}}/{{$departmentValue['departmentName']}}" {{ (!empty($recipe_companys) && in_array($teamValue['id'], $recipe_companys))? 'selected' : ''   }} >{{ $teamValue['name'] }}</option>
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        </select>
        <span id="recipe_company-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('labels.recipe.company_selection') }}</span>
    </div>
</div>
@endif

<script id="ingredientsTemplate" type="text/html">
@include('admin.recipe.ingredients', [
    'ingdCount' => ':ingdCount',
    'ingredient' => null,
    'show_del' => 'show_del',
])
</script>

<script id="recipeImagePreview" type="text/html">
    <div class="recipe-card-img {{ (($edit == true) ? "edit" : "") }}">
        <img src="##src##" />
    </div>
</script>
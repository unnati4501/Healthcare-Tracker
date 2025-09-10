<div class="row mb-4">
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            <label>
                Category :
            </label>
            @if(isset($question))
                @if($question->status == 1)
                {{ Form::select('', $categories, $question->category_id , ['class' => 'form-control select2 category', 'data-placeholder' => 'Select Category', 'placeholder' => 'Select Category', 'disabled' => true]) }}
                {{ Form::hidden('category', $question->category_id) }}
                @else
                {{ Form::select('category', $categories, $question->category_id , ['class' => 'form-control select2 category', 'data-placeholder' => 'Select Category', 'placeholder' => 'Select Category']) }}
                @endif
            @else
            {{ Form::select('category', $categories, request()->get('category') , ['class' => 'form-control select2 category', 'data-placeholder' => 'Select Category', 'placeholder' => 'Select Category']) }}
            @endif
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            <label>
                Sub categories :
            </label>
            @if(empty($subcategories))
                {{ Form::select('subcategories', [], request()->get('subcategories') , ['class' => 'form-control select2 subcategories', 'data-placeholder' => 'Select Subcategory', 'placeholder' => 'Select Subcategory', 'disabled'=>'disabled']) }}
            @else
                @if($question->status == 1)
                {{ Form::select('', $subcategories, $question->sub_category_id , ['class' => 'form-control select2 subcategories', 'data-placeholder' => 'Select Subcategory', 'placeholder' => 'Select Subcategory', 'disabled' => true]) }}
                {{ Form::hidden('subcategories', $question->sub_category_id) }}
                @else
                {{ Form::select('subcategories', $subcategories, $question->sub_category_id , ['class' => 'form-control select2 subcategories', 'data-placeholder' => 'Select Subcategory', 'placeholder' => 'Select Subcategory']) }}
                @endif
            @endif
        </div>
    </div>
</div>
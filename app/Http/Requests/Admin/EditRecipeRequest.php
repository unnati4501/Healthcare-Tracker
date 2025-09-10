<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Recipe;
use App\Models\User;
use App\Models\CategoryTags;

class EditRecipeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-recipe');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $role            = getUserRole();
        $companyParentId = (\Auth::user()->company->first()->parent_id ?? null);
        $logoMax         = config('zevolifesettings.fileSizeValidations.recipe.logo', (5 * 1024));
        $headerImageMax  = config('zevolifesettings.fileSizeValidations.recipe.header_image', 2048);

        $headerImageRules          = array();
        $headerImage = function () {
            $thisId             = $this->route('recipe')->id;
            $getRecipeData      = Recipe::find($thisId);
            if (!empty($getRecipeData)){
                return $getRecipeData->header_image_name;
            } 
        };
        $headerImageRules = [
            empty($headerImage()) ? "required" : "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$headerImageMax}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
        ];

        $rules                            = array();
        $rules['image']                   = "sometimes";
        $rules['image.*']                 = "nullable|image|mimes:jpg,jpeg,png|max:{$logoMax}";
        $rules['title']                   = 'required|max:50';
        $rules['calories']                = 'required|numeric|between:1,99999';
        $rules['cooking_time']            = 'required|numeric|between:1,1439';
        $rules['servings']                = 'required|numeric|between:1,999';
        $rules['recipesubcategory']       = 'required';
        $rules['recipesubcategory.*']     = 'integer';
        $rules['description']             = 'required|description';
        $rules['nutritions.energy']       = 'required|numeric|between:0,99999';
        $rules['nutritions.fat']          = 'required|numeric|between:0,99999';
        $rules['nutritions.carbohydrate'] = 'required|numeric|between:0,99999';
        $rules['nutritions.protein']      = 'required|numeric|between:0,99999';
        $rules['nutritions.salt']         = 'required|numeric|between:0,99999';
        $rules['nutritions.fiber']        = 'required|numeric|between:0,99999';
        $rules['goal_tag']                = 'array|max:3';
        $rules['chef']                    = 'nullable|integer|exists:' . User::class . ',id';
        $rules['type']                    = 'required|in:1,2,3';
        $rules['header_image']            = $headerImageRules;
        if ($role->group == 'zevo' || ($role->group == 'reseller' && $companyParentId == null)) {
            $rules['recipe_company']    = 'required';
            $rules['recipe_company.*']  = 'integer';
        }
        $rules['tag']               = 'nullable|integer|exists:' . CategoryTags::class . ',id';
        return $rules;
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        $messages                      = [];
        $messages['image.*.image']     = 'The image field must be an image.';
        $messages['image.*.mimes']     = 'The image feild must be a file of type: jpg, jpeg, png.';
        $messages['image.*.max']       = 'The image field may not be greater than 5MB.';
        $messages['image.*.filecount'] = 'The image field can not be more than 3.';
        return $messages;
    }

    public function attributes()
    {
        return [
            'title'                   => 'recipe name',
            'cooking_time'            => 'time',
            'description'             => 'directions',
            'recipesubcategory'       => 'category',
            'nutritions.energy'       => 'energy',
            'nutritions.fat'          => 'fat',
            'nutritions.carbohydrate' => 'carbohydrate',
            'nutritions.protein'      => 'protein',
            'nutritions.salt'         => 'salt',
            'nutritions.fiber'        => 'fiber',
            'recipe_company'          => 'company selection',
        ];
    }
}

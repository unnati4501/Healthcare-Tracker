<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V7;

use App\Http\Collections\V7\RecipeListCollection;
use App\Http\Controllers\API\V4\RecipeController as v4RecipeController;
use App\Http\Resources\V7\RecipeDetailResource;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends v4RecipeController
{
    /**
     * List all the recipes based on user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($subcategory, Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $recipeExploreData = Recipe::with('creator', 'chef')
                ->select('recipe.*')
                ->join('recipe_category', function ($join) {
                    $join->on('recipe_category.recipe_id', '=', 'recipe.id');
                })
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'recipe_category.sub_category_id')->where('sub_categories.status', 1);
                })
                ->where(function ($query) use ($company) {
                    $query->whereNull('recipe.company_id')->orWhere('recipe.company_id', $company->id);
                })
                ->whereRaw('recipe_category.sub_category_id = ? AND (recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?))', [$subcategory, 1, $user->getKey(), 0])
                ->groupBy("recipe.id")
                ->orderByRaw("`recipe`.`status` ASC, `recipe`.`updated_at` DESC")
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($recipeExploreData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new RecipeListCollection($recipeExploreData), 'Recipe listed successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * get saved recipe listing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function savedList(Request $request)
    {
        try {
            $user = $this->user();

            $recipeRecords = $user->recipeLogs()
                ->with('recipesubcategories')
                ->whereHas('recipesubcategories', function ($query) {
                    $query->where('status', 1);
                })
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('recipe_user.saved_at', 'DESC')
                ->orderBy('recipe_user.id', 'DESC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($recipeRecords->count() > 0) ? new RecipeListCollection($recipeRecords) : ['data' => []],
                ($recipeRecords->count() > 0) ? 'Saved recipe list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recipe details by id
     *
     * @param Request $request, Recipe $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request, Recipe $recipe)
    {
        try {
            $user          = $this->user();
            $role          = getUserRole();
            $subcategories = $recipe->recipesubcategories()->where('status', 1)->count();

            if ($subcategories == 0) {
                return $this->notFoundResponse('Recipe not found');
            }

            if ($recipe->status == 0) {
                if ($role->slug == 'user' && $role->default == '1' && $recipe->creator_id != $user->getKey()) {
                    return $this->notFoundResponse('Recipe is pending for approval by Zevo account manager');
                } elseif ($role->group == 'company' && $recipe->company_id != $user->company()->first()->id) {
                    return $this->notFoundResponse('Recipe is pending for approval by Zevo account manager');
                }
            }
            return $this->successResponse(['data' => new RecipeDetailResource($recipe)], 'Recipe detail retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

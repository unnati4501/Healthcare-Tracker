<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V11;

use App\Http\Collections\V11\RecipeListCollection;
use App\Http\Controllers\API\V7\RecipeController as v7RecipeController;
use App\Http\Resources\V7\RecipeDetailResource as v7RecipeDetailResource;
use App\Models\Company;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends v7RecipeController
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
            $user      = $this->user();
            $company   = $user->company()->first();
            $role      = getUserRole();

            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            $status = ($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null && $role->group == 'reseller') ? 1 : 0;

            $recipeExploreData = Recipe::with('creator', 'chef')
                ->select('recipe.*')
                ->join('recipe_category', function ($join) {
                    $join->on('recipe_category.recipe_id', '=', 'recipe.id');
                })
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'recipe_category.sub_category_id')->where('sub_categories.status', 1);
                })
                ->where(function ($query) use ($company, $role) {
                    if ($company->parent_id != null) {
                        $query->whereNull('recipe.company_id')->orWhere('recipe.company_id', $company->id)->orWhere('recipe.company_id', $company->parent_id);
                    } else {
                        if ($role->slug == 'user') {
                            $query->whereNull('recipe.company_id')->orWhere('recipe.company_id', $company->id);
                        } else {
                            $subcompany = company::where('parent_id', $company->id)->orWhere('id', $company->id)->pluck('id')->toArray();
                            $query->whereNull('recipe.company_id')->orwhereIn('recipe.company_id', $subcompany);
                        }
                    }
                })
                ->whereRaw('recipe_category.sub_category_id = ? AND (recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?))', [$subcategory, 1, $user->getKey(), $status])
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

            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $company   = $user->company()->first();
                $companyId = $company->id;

                if ($company->parent_id != null) {
                    // RCA ELSE RSA ( Permission to access ZEVO/RCA/RSA )
                    if (!is_null($recipe->company_id) && $recipe->company_id != $company->parent_id && $recipe->company_id != $companyId) {
                        return $this->notFoundResponse('Recipe not found');
                    }
                } else {
                    $subcompany = company::where('parent_id', $companyId)->orWhere('id', $companyId)->pluck('id')->toArray();
                    array_push($subcompany, null);

                    if (!in_array($recipe->company_id, $subcompany)) {
                        return $this->notFoundResponse('Recipe not found');
                    } elseif (!$recipe->status) {
                        return $this->notFoundResponse('Recipe not found');
                    }
                }
            }

            if ($recipe->status == 0) {
                if ($role->slug == 'user' && $role->default == '1' && $recipe->creator_id != $user->getKey()) {
                    return $this->notFoundResponse('Recipe is pending for approval by Zevo account manager');
                } elseif ($role->group == 'company' && $recipe->company_id != $user->company()->first()->id) {
                    return $this->notFoundResponse('Recipe is pending for approval by Zevo account manager');
                }
            }
            return $this->successResponse(['data' => new v7RecipeDetailResource($recipe)], 'Recipe detail retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

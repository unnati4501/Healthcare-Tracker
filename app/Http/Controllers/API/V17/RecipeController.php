<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V17;

use App\Http\Collections\V11\RecipeListCollection;
use App\Http\Controllers\API\V14\RecipeController as v14RecipeController;
use App\Http\Resources\V17\RecipeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Recipe;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends v14RecipeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * API to favorited unfavourited recipe
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function favouriteUnfavourite(Request $request, Recipe $recipe)
    {
        try {
            DB::beginTransaction();
            $user           = $this->user();
            $pivotExsisting = $recipe->recipeUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('recipe_id', $recipe->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $favourited                           = $pivotExsisting->pivot->favourited;
                $pivotExsisting->pivot->favourited    = ($favourited == 1) ? 0 : 1;
                $pivotExsisting->pivot->favourited_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();

                if ($favourited == 1) {
                    $message = trans('api_messages.recipe.unfavorited');
                } else {
                    $message = trans('api_messages.recipe.favorited');
                }
            } else {
                $recipe->recipeUserLogs()
                    ->attach($user, ['favourited' => true, 'favourited_at' => now()->toDateTimeString()]);

                $message = trans('api_messages.recipe.favorited');
            }

            DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

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
            $company   = $user->company()->select('companies.id', 'companies.parent_id')->first();
            $role      = getUserRole($user);
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $status    = ($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null && $role->group == 'reseller') ? 1 : 0;

            $recipeExploreData = Recipe::with('creator', 'chef')
                ->select('recipe.*', DB::raw("( SELECT count(id) FROM recipe_user WHERE recipe_id = `recipe`.`id` ) AS recipe_view_count"))
                ->join('recipe_category', function ($join) {
                    $join->on('recipe_category.recipe_id', '=', 'recipe.id');
                })
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'recipe_category.sub_category_id')->where('sub_categories.status', 1);
                })
                ->join('recipe_company', function ($join) use ($company) {
                    $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
                });

            if ($subcategory <= 0) {
                if ($subcategory == 0) {
                    $recipeExploreData->join('recipe_user', 'recipe_user.recipe_id', '=', 'recipe.id')
                        ->where('recipe_user.user_id', $user->getKey())
                        ->where('recipe_user.favourited', 1)
                        ->where('recipe.status', 1);
                } else {
                    $recipeExploreData->whereRaw('recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?)', [1, $user->getKey(), $status]);
                }
            } else {
                $recipeExploreData->whereRaw('recipe_category.sub_category_id = ? AND (recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?))', [$subcategory, 1, $user->getKey(), $status]);
            }

            $recipeExploreData->groupBy("recipe.id");
            if ($subcategory <= 0) {
                $recipeExploreData->orderByRaw("`recipe_view_count` DESC, `recipe`.`updated_at` DESC");
            } else {
                $recipeExploreData->orderByRaw("`recipe`.`status` ASC, `recipe`.`updated_at` DESC");
            }

            $recipeExploreData = $recipeExploreData->paginate(config('zevolifesettings.datatable.pagination.short'));

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
            $role          = getUserRole($user);
            $company       = $user->company()->first();
            $subcategories = $recipe->recipesubcategories()->where('status', 1)->count();

            if ($subcategories == 0) {
                return $this->notFoundResponse('Recipe not found');
            }

            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $companyId = $company->id;
                // Check recipe available with this company or not
                $checkRecipe = $recipe->recipecompany()->where('company_id', $company->id)->count();

                if ($checkRecipe <= 0) {
                    return $this->notFoundResponse('Recipe not found');
                }
            }

            if ($recipe->status == 0) {
                if ($role->slug == 'user' && $role->default == '1' && $recipe->creator_id != $user->getKey()) {
                    return $this->notFoundResponse('Recipe is pending for approval by Zevo account manager');
                } elseif ($role->group == 'company' && $recipe->company_id != $user->company()->first()->id) {
                    return $this->notFoundResponse('Recipe is pending for approval by Zevo account manager');
                }
            } else {
                if (!is_null($company)) {
                    $recipe->rewardPortalPointsToUser($user, $company, 'recipe');
                }
            }

            return $this->successResponse(['data' => new RecipeDetailResource($recipe)], 'Recipe detail retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get list of recipe static data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recipeStaticData(Request $request)
    {
        try {
            $recipeSubCategories = SubCategory::where(["category_id" => 5, "status" => 1])
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            $subcategoriesData = array_map(function ($id, $name) {
                return array(
                    'id'   => $id,
                    'name' => $name,
                );
            }, array_keys($recipeSubCategories), $recipeSubCategories);

            $nutritions = config('zevolifesettings.nutritions');

            $nutritionsData = [];
            foreach ($nutritions as $key => $value) {
                $nutritionsData[] = [
                    'id'    => $key,
                    'title' => $value['display_name'],
                ];
            }

            $data = [
                'subcategories' => $subcategoriesData,
                'nutritions'    => $nutritionsData,
            ];

            return $this->successResponse(['data' => $data], 'Recipe static data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

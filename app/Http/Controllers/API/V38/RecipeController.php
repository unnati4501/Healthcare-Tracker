<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V38;

use App\Http\Controllers\API\V33\RecipeController as v33RecipeController;
use App\Http\Collections\V38\RecipeListCollection;
use App\Http\Resources\V38\RecipeDetailResource;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\RecipeType;
use App\Http\Requests\Api\V23\SearchRecipeRequest;
use App\Models\SubCategory;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\PaginationTrait;

class RecipeController extends v33RecipeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait, PaginationTrait;
    
    /**
     * List all the recipes based on user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($subcategory, Request $request)
    {
        try {
            $data      = [];
            $user      = $this->user();
            $company   = $user->company()->select('companies.id', 'companies.parent_id')->first();
            $team      = $user->teams()->first();
            $role      = getUserRole($user);
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $status    = ($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null && $role->group == 'reseller') ? 1 : 0;

            $recipes = Recipe::with([
                'creator' => function ($query) {
                    $query->select('users.id', 'users.first_name', 'users.last_name');
                },
                'chef'    => function ($query) {
                    $query->select('users.id', 'users.first_name', 'users.last_name');
                },
            ])
                ->join('recipe_category', 'recipe_category.recipe_id', '=', 'recipe.id')
                ->join('sub_categories', 'sub_categories.id', '=', 'recipe_category.sub_category_id')
                ->join('recipe_team', function ($join) use ($team) {
                    $join
                        ->on('recipe_team.recipe_id', '=', 'recipe.id')
                        ->where('recipe_team.team_id', $team->id);
                })
                ->groupBy("recipe.id");

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $recipes->select(
                    'recipe.*',
                    DB::raw("( SELECT count(id) FROM recipe_user WHERE recipe_id = `recipe`.`id` ) AS recipe_view_count")
                );
                if ($subcategory <= 0) {
                    if ($subcategory == 0) {
                        $recipes->join('recipe_user', 'recipe_user.recipe_id', '=', 'recipe.id')
                            ->where('recipe_user.user_id', $user->getKey())
                            ->where('recipe_user.favourited', 1)
                            ->where('recipe.status', 1);
                    } else {
                        $recipes->whereRaw('recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?)', [1, $user->getKey(), $status]);
                    }
                } else {
                    $recipes->whereRaw('recipe_category.sub_category_id = ? AND (recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?))', [$subcategory, 1, $user->getKey(), $status]);
                }

                $recipes->groupBy("recipe.id");
                if ($subcategory <= 0) {
                    $recipes->orderByRaw("`recipe_view_count` DESC, `recipe`.`updated_at` DESC");
                } else {
                    $recipes->orderByRaw("`recipe`.`status` ASC, `recipe`.`updated_at` DESC");
                }

                $recipes = $recipes->paginate(config('zevolifesettings.datatable.pagination.short'));
                if ($recipes->count() > 0) {
                    // collect required data and return response
                    return $this->successResponse(new RecipeListCollection($recipes, true), 'Recipe listed successfully');
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            } else {
                $recipes
                    ->select(
                        'recipe.id',
                        'recipe.title',
                        'recipe.creator_id',
                        'recipe.chef_id',
                        'recipe.calories',
                        'recipe.caption',
                        \DB::raw("TIME_TO_SEC(recipe.cooking_time) AS cooking_time"),
                        \DB::raw("'listing' AS moduleFrom")
                    );
                    $recipes->addSelect(DB::raw("CASE
                        WHEN recipe.caption = 'New' then 0
                        WHEN recipe.caption = 'Popular' then 1
                        ELSE 2
                        END AS caption_order"
                    ));
                    if ($subcategory <= 0) {
                        if ($subcategory == 0) {
                            $recipes = $recipes->join('recipe_user', 'recipe_user.recipe_id', '=', 'recipe.id')
                                ->where('recipe_user.user_id', $user->getKey())
                                ->where('recipe_user.favourited', 1)
                                ->where('recipe.status', 1);
                        } else {
                            $recipes = $recipes->whereRaw('recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?)', [1, $user->id, $status]);
                        }
                    }
                    $recipes = $recipes->orderBy('caption_order', 'ASC')
                        ->orderBy('recipe.updated_at', 'DESC')
                        ->orderBy('recipe.id', 'DESC')
                        ->groupBy('recipe.id')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));

                    if ($recipes->count() > 0) {
                        return $this->successResponse(new RecipeListCollection($recipes, true), 'Recipe retrieved successfully.');
                    } else {
                        return $this->successResponse(['data' => []], 'No results');
                    }
                return $this->successResponse([
                    'data' => $data,
                ], 'Recipe listed successfully');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
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
            $team          = $user->teams()->first();
            $subcategories = $recipe->recipesubcategories()->where('status', 1)->count();

            if ($subcategories == 0) {
                return $this->notFoundResponse('Recipe not found');
            }

            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                // Check recipe available with this company or not
                $checkRecipe = $recipe->recipeteam()->where('team_id', $team->id)->count();

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
     * Search recipes respective filters
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(SearchRecipeRequest $request, Recipe $recipe)
    {
        try {
            $user      = $this->user();
            $role      = getUserRole($user);
            $company   = $user->company()->select('companies.id', 'companies.parent_id')->first();
            $team      = $user->teams()->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $status    = (($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null && $role->group == 'reseller') ? 1 : 0);

            $recipes = $recipe
                ->with([
                    'creator' => function ($query) {
                        $query->select('users.id', 'users.first_name', 'users.last_name');
                    },
                    'chef'    => function ($query) {
                        $query->select('users.id', 'users.first_name', 'users.last_name');
                    },
                ])
                ->select(
                    'recipe.id',
                    'recipe.title',
                    'recipe.creator_id',
                    'recipe.chef_id',
                    'recipe.calories',
                    'recipe.caption',
                    \DB::raw("TIME_TO_SEC(recipe.cooking_time) AS cooking_time"),
                    \DB::raw("'search' AS moduleFrom")
                )
                ->join('recipe_category', 'recipe_category.recipe_id', '=', 'recipe.id')
                ->join('sub_categories', 'sub_categories.id', '=', 'recipe_category.sub_category_id')
                ->join('recipe_team', function ($join) use ($team) {
                    $join
                        ->on('recipe_team.recipe_id', '=', 'recipe.id')
                        ->where('recipe_team.team_id', $team->id);
                })
                ->orderByRaw("recipe.status ASC, recipe.updated_at DESC");

            if (!empty($request->categories)) {
                $isFavouriteSelected = in_array(0, $request->categories);
                if ($isFavouriteSelected && sizeof($request->categories) == 1) {
                    $recipes
                        ->join('recipe_user', 'recipe_user.recipe_id', '=', 'recipe.id')
                        ->where('recipe_user.user_id', $user->id)
                        ->where('recipe_user.favourited', 1);
                } else {
                    if ($isFavouriteSelected) {
                        $recipes->leftJoin('recipe_user', 'recipe_user.recipe_id', '=', 'recipe.id');
                    }
                    $recipes->where(function ($where) use ($request, $user, $status, $isFavouriteSelected) {
                        $where->whereRaw('(recipe_category.sub_category_id IN (?) AND (recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?)))', [implode(',', $request->categories), 1, $user->id, $status]);
                        if ($isFavouriteSelected) {
                            $where
                                ->orWhere('recipe_user.user_id', $user->id)
                                ->orWhere('recipe_user.favourited', 1);
                        }
                    });
                }
            } else {
                $recipes->whereRaw('(recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?))', [1, $user->id, $status]);
            }

            if (!empty($request->type)) {
                $recipes->whereIn('recipe.type_id', $request->type);
            }

            if (!empty($request->calories)) {
                $recipes->whereBetween('recipe.calories', $request->calories);
            }

            if (!empty($request->cookingTime)) {
                $recipes->whereRaw("(TIME_TO_SEC(`recipe`.`cooking_time`) BETWEEN ? AND ?)", [$request->cookingTime[0], $request->cookingTime[1]]);
            }

            if (!empty($request->protein)) {
                $recipes->whereRaw("(CAST(JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(nutritions, '$[3]'), '$.value')) AS DECIMAL(10, 1)) BETWEEN ? AND ?)", [$request->protein[0], $request->protein[1]]);
            }

            $recipes->addSelect(DB::raw("CASE
                    WHEN recipe.caption = 'New' then 0
                    WHEN recipe.caption = 'Popular' then 1
                    ELSE 2
                    END AS caption_order"
                ));

            $recipes = $recipes
                ->orderBy('caption_order', 'ASC')
                ->groupBy('recipe.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($recipes->count() > 0) {
                return $this->successResponse(new RecipeListCollection($recipes, true), 'Recipe listed successfully');
            } else {
                return $this->successResponse(['data' => []], 'No recipes found as per search criteria');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
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
            $user    = $this->user();
            $team    = $user->teams()->first();

            $recipeRecords = Recipe::
                with('recipesubcategories')
                ->select(
                    'recipe.id',
                    'recipe.title',
                    'recipe.creator_id',
                    'recipe.chef_id',
                    'recipe.calories',
                    'recipe.caption',
                    \DB::raw("TIME_TO_SEC(recipe.cooking_time) AS cooking_time"),
                    \DB::raw("'savedList' AS moduleFrom")
                )
                ->join('recipe_user', 'recipe_user.recipe_id', '=', 'recipe.id')
                ->join('recipe_team', function ($join) use ($team) {
                    $join
                        ->on('recipe_team.recipe_id', '=', 'recipe.id')
                        ->where('recipe_team.team_id', $team->id);
                })
                ->whereHas('recipesubcategories', function ($query) {
                    $query->where('status', 1);
                })
                ->where('recipe_user.user_id', $user->getKey())
                ->where('recipe_user.saved', true);

                $recipeRecords->addSelect(DB::raw("CASE
                    WHEN recipe.caption = 'New' then 0
                    WHEN recipe.caption = 'Popular' then 1
                    ELSE 2
                    END AS caption_order"
                ));
                $recipeRecords = $recipeRecords->orderBy('caption_order', 'ASC')
                ->orderBy('recipe_user.saved_at', 'DESC')
                ->orderBy('recipe_user.id', 'DESC')
                ->groupBy('recipe.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            
            return $this->successResponse(
                ($recipeRecords->count() > 0) ? new RecipeListCollection($recipeRecords, true) : ['data' => []],
                ($recipeRecords->count() > 0) ? 'Saved recipe list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

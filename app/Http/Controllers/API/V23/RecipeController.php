<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V23;

use App\Http\Collections\V11\RecipeListCollection as v11RecipeListCollection;
use App\Http\Collections\V23\RecipeListCollection;
use App\Http\Collections\V23\RecipeSearchListCollection;
use App\Http\Controllers\API\V17\RecipeController as v17RecipeController;
use App\Http\Requests\Api\V23\SearchRecipeRequest;
use App\Models\Recipe;
use App\Models\RecipeType;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends v17RecipeController
{
    /**
     * Get recipe static data
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recipeStaticData(Request $request)
    {
        try {
            $user       = $this->user();
            $role       = getUserRole($user);
            $company    = $user->company()->select('companies.id', 'companies.parent_id')->first();
            $xDeviceOs  = strtolower($request->header('X-Device-Os', ""));
            $status     = (($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null && $role->group == 'reseller') ? 1 : 0);
            $nutritions = $subCategories = $recipeTypes = [];

            $favouritedCount = $user->recipeLogs()
                ->wherePivot('favourited', true)
                ->count('recipe.id');
            SubCategory::select('name', 'id')
                ->where('category_id', 5)
                ->where('status', 1)
                ->orderBy('is_excluded', 'DESC')
                ->get()
                ->each(function ($subCategory) use (&$subCategoriesRecords) {
                    if ($subCategory->recipes()->count('recipe_category.id') > 0) {
                        $subCategoriesRecords[] = [
                            'id'   => $subCategory->id,
                            'name' => $subCategory->name,
                        ];
                    }
                });

            if ($favouritedCount > 0) {
                array_unshift($subCategoriesRecords, [
                    'id'   => 0,
                    'name' => "My ❤️",
                ]);
            } else {
                $subCategoriesRecords[] = [
                    'id'   => 0,
                    'name' => "My ❤️",
                ];
            }

            RecipeType::select('type_name', 'id')
                ->where('status', '1')
                ->get()
                ->each(function ($type) use (&$recipeTypes) {
                    $recipeTypes[] = [
                        'id'   => $type->id,
                        'name' => $type->type_name,
                    ];
                });

            collect(config('zevolifesettings.nutritions'))
                ->each(function ($nutrition, $key) use (&$nutritions) {
                    $nutritions[] = [
                        'id'    => $key,
                        'title' => $nutrition['display_name'],
                    ];
                });

            $recipeFilterData = Recipe::select(
                \DB::raw("IFNULL(MAX(recipe.cooking_time), 0) AS max_cooking_time"),
                \DB::raw("IFNULL(MAX(recipe.calories), 0) AS max_calories"),
                \DB::raw("IFNULL(MAX(CAST(JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(nutritions, '$[3]'), '$.value')) AS DECIMAL(10, 1))), 0) AS max_protein")
            )
                ->join('recipe_company', function ($join) use ($company) {
                    $join
                        ->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
                })
                ->whereRaw('recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?)', [1, $user->id, $status])
                ->first();

            $data = [
                'subcategories' => $subCategoriesRecords,
                'nutritions'    => $nutritions,
                'types'         => $recipeTypes,
                'cookingTime'   => [
                    'min' => (int) 0,
                    'max' => timeToSec($recipeFilterData->max_cooking_time),
                ],
                'calories'      => [
                    'min' => (int) 0,
                    'max' => (int) floor($recipeFilterData->max_calories),
                ],
                'protein'       => [
                    'min' => (int) 0,
                    'max' => (int) (empty($recipeFilterData->max_protein) ? 1 : floor($recipeFilterData->max_protein)),
                ],

            ];

            return $this->successResponse([
                'data' => $data,
            ], 'Recipe static data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
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
                    \DB::raw("TIME_TO_SEC(recipe.cooking_time) AS cooking_time"),
                    \DB::raw("'search' AS moduleFrom")
                )
                ->join('recipe_category', 'recipe_category.recipe_id', '=', 'recipe.id')
                ->join('sub_categories', 'sub_categories.id', '=', 'recipe_category.sub_category_id')
                ->join('recipe_company', function ($join) use ($company) {
                    $join
                        ->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
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

            $recipes = $recipes
                ->groupBy('recipe.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($recipes->count() > 0) {
                return $this->successResponse(new RecipeSearchListCollection($recipes), 'Recipe listed successfully');
            } else {
                return $this->successResponse(['data' => []], 'No recipes found as per search criteria');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
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
            $data      = [];
            $user      = $this->user();
            $company   = $user->company()->select('companies.id', 'companies.parent_id')->first();
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
                ->join('recipe_company', function ($join) use ($company) {
                    $join
                        ->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
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
                    return $this->successResponse(new v11RecipeListCollection($recipes), 'Recipe listed successfully');
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
                        \DB::raw("TIME_TO_SEC(recipe.cooking_time) AS cooking_time"),
                        \DB::raw("'listing' AS moduleFrom")
                    )
                    ->whereRaw('(recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?))', [1, $user->id, $status]);

                // recently added
                $recent = with(clone $recipes)
                    ->orderByRaw("recipe.status ASC, recipe.updated_at DESC")
                    ->limit(15)
                    ->get()
                    ->shuffle();
                $data['recent'] = new RecipeListCollection($recent);

                // under 600 calories
                $calories = with(clone $recipes)
                    ->where('calories', '<=', 600)
                    ->limit(15)
                    ->get()
                    ->shuffle();
                $data['calories'] = new RecipeListCollection($calories);

                // favourite recipes block
                $liked = with(clone $recipes)
                    ->join('recipe_user', 'recipe_user.recipe_id', '=', 'recipe.id')
                    ->where('recipe_user.user_id', $user->id)
                    ->where('recipe_user.liked', 1)
                    ->limit(15)
                    ->get()
                    ->shuffle();
                $data['liked'] = ($liked->isNotEmpty() ? new RecipeListCollection($liked) : []);

                // quick bites logic, add extra +15 mins if no result found
                $quickBites = $this->quickBites($recipes, 900);
                if ($quickBites->isEmpty()) {
                    $quickBites = $this->quickBites($recipes, 1800);
                }
                $data['quickBites'] = ($quickBites->isNotEmpty() ? new RecipeListCollection($quickBites) : []);
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
     * to get quick bites recipe
     * @param QueryBuilder $query
     * @param int $duration
     *
     * @return mixed array|null
     */
    protected function quickBites($query, $duration = 900)
    {
        return with(clone $query)
            ->whereRaw("TIME_TO_SEC(recipe.cooking_time) <= ?", $duration)
            ->limit(15)
            ->get()
            ->shuffle();
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
            $company = $user->company()->select('companies.id')->first();

            $recipeRecords = Recipe::select(
                'recipe.id',
                'recipe.title',
                'recipe.creator_id',
                'recipe.chef_id',
                'recipe.calories',
                \DB::raw("TIME_TO_SEC(recipe.cooking_time) AS cooking_time"),
                \DB::raw("'savedList' AS moduleFrom")
            )
                ->join('recipe_user', 'recipe.id', '=', 'recipe_user.recipe_id')
                ->join('recipe_company', function ($join) use ($company) {
                    $join
                        ->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
                })
                ->where('recipe_user.user_id', $user->id)
                ->where('recipe_user.saved', true)
                ->orderByDesc('recipe_user.saved_at')
                ->orderByDesc('recipe_user.id')
                ->groupBy('recipe_user.recipe_id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            return $this->successResponse(
                ($recipeRecords->count() > 0) ? new RecipeListCollection($recipeRecords, true) : ['data' => []],
                ($recipeRecords->count() > 0) ? 'Saved recipe list retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }
}

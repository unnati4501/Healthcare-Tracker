<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V32;

use App\Http\Collections\V11\RecipeListCollection as v11RecipeListCollection;
use App\Http\Collections\V32\RecipeListCollection;
use App\Http\Controllers\API\V31\RecipeController as v31RecipeController;
use App\Models\Recipe;
use App\Models\RecipeType;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends v31RecipeController
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
}

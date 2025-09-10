<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V13;

use App\Http\Collections\V11\RecipeListCollection;
use App\Http\Controllers\API\V11\RecipeController as v11RecipeController;
use App\Http\Requests\Api\V1\RecipeCreateRequest;
use App\Http\Resources\V7\RecipeDetailResource as v7RecipeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Company;
use App\Models\Recipe;
use App\Models\SubCategory;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecipeController extends v11RecipeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
    /**
     * Get list of recipe categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recipeStaticData(Request $request)
    {
        try {
            $user = $this->user();

            $recipe_subcategories = SubCategory::where(["category_id" => 5, "status" => 1])->get();

            $subcategoriesData = $recipe_subcategories->map(function ($item, $key) {
                return [
                    'id'   => $item->id,
                    'name' => $item->name,
                ];
            });

            $nutritions = config('zevolifesettings.nutritions');

            $nutritionsData = [];
            foreach ($nutritions as $key => $value) {
                $nutritionsData[] = [
                    'id'    => $key,
                    'title' => $value['display_name'],
                ];
            }

            $records = array(-1 => "All") + $subcategoriesData->pluck('name', 'id')->toArray();

            $new_array = array_map(function ($id, $name) {
                return array(
                    'id'   => $id,
                    'name' => $name,
                );
            }, array_keys($records), $records);

            $subcategoriesData = SubCategory::hydrate($new_array);

            $data = [
                'subcategories' => $subcategoriesData,
                'nutritions'    => $nutritionsData,
            ];

            return $this->successResponse(['data' => $data], 'Categories retrieved successfully.');
        } catch (\Exception $e) {
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
            $user    = $this->user();
            $company = $user->company()->first();
            $role    = getUserRole();

            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            $status = ($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null && $role->group == 'reseller') ? 1 : 0;

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
                $recipeExploreData->whereRaw('recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?)', [1, $user->getKey(), $status]);
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
     * get saved recipe listing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function savedList(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $recipeRecords = $user->recipeLogs()
                ->with('recipesubcategories')
                ->join('recipe_company', function ($join) use ($company) {
                    $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
                })
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

            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $company   = $user->company()->first();
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
            }
            return $this->successResponse(['data' => new v7RecipeDetailResource($recipe)], 'Recipe detail retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Store the recipe in db
     *
     * @param RecipeCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(RecipeCreateRequest $request)
    {
        try {
            \DB::beginTransaction();

            $payload    = $request->all();
            $user       = $this->user();
            $company_id = !is_null($user->company->first()) ? $user->company->first()->id : null;

            if (!empty($payload)) {
                $recipeInput = [
                    'creator_id'   => $user->id,
                    'chef_id'      => $user->id,
                    'company_id'   => $company_id,
                    'title'        => $payload['title'],
                    'description'  => $payload['directions'],
                    'calories'     => $payload['calories'],
                    'cooking_time' => gmdate('H:i:s', (int) $payload['cooking_time']),
                    'servings'     => $payload['serves'],
                    'ingredients'  => json_encode((object) json_decode($payload['ingredients'])),
                    'nutritions'   => $payload['nutritions'],
                    'status'       => 0,
                ];

                $recipe = Recipe::create($recipeInput);

                $recipe_companyInput[] = [
                    'recipe_id'  => $recipe->id,
                    'company_id' => $company_id,
                    'created_at' => Carbon::now(),
                ];

                $recipe->recipecompany()->sync($recipe_companyInput);

                if ($request->hasFile('image')) {
                    foreach (($request->file('image')) as $key => $file) {
                        $name = $recipe->getKey() . '_' . Str::random() . \time();
                        $recipe->addMedia($file)
                            ->usingName($file->getClientOriginalName())
                            ->usingFileName($name . '.' . $file->extension())
                            ->toMediaCollection('logo', config('medialibrary.disk_name'));
                    }
                }

                $recipesubcategories = json_decode($payload['subcategories']);
                $recipe->recipesubcategories()->sync($recipesubcategories);
            }

            \DB::commit();

            return $this->successResponse([], trans('api_messages.recipe.add'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

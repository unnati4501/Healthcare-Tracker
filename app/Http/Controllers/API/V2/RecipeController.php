<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V2;

use App\Http\Collections\V2\RecipeListCollection;
use App\Http\Controllers\API\V1\RecipeController as v1RecipeController;
use App\Http\Requests\Api\V1\RecipeCreateRequest;
use App\Http\Requests\Api\V1\RecipeUpdateRequest;
use App\Http\Resources\V2\RecipeDetailResource;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends v1RecipeController
{
    /**
     * api to delete created recipe.
     *
     * @param Request $request, Recipe $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Recipe $recipe)
    {
        try {
            // logged-in user
            $loggedInUser = $this->user();

            // check user is creater of recipe or not if user is not creater of recipe then not allow to perform operation
            if ($recipe->creator_id != $loggedInUser->getKey()) {
                return $this->notFoundResponse("You are not authorized to delete this recipe");
            }

            \DB::beginTransaction();
            $recipe->clearMediaCollection('logo');
            $recipe->delete();
            \DB::commit();

            return $this->successResponse([], 'Recipe deleted successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
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
    public function index(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

            $recipeExploreData = Recipe::with('recipeCategories', 'creator', 'chef')
                ->where(function ($query) use ($company) {
                    $query->where('recipe.company_id', null)
                        ->orWhere('recipe.company_id', $company->id);
                })
                ->whereRaw('(recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?))', [1, $user->getKey(), 0]);

            if (!empty($request->categories)) {
                $recipeExploreData = $recipeExploreData->whereHas('recipeCategories', function ($query) use ($request) {
                    $query->whereIn('recipe_category_id', $request->categories);
                });
            }

            $recipeExploreData = $recipeExploreData
            // ->orderByRaw('recipe_liked DESC')
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
                    'image'        => $payload['image']->getClientOriginalName(),
                    'calories'     => $payload['calories'],
                    'cooking_time' => gmdate('H:i:s', (int) $payload['cooking_time']),
                    'servings'     => $payload['serves'],
                    'ingredients'  => json_encode((object) json_decode($payload['ingredients'])),
                    'nutritions'   => $payload['nutritions'],
                    'status'       => 0,
                ];

                $recipe = Recipe::create($recipeInput);

                if ($request->hasFile('image')) {
                    $name = $recipe->getKey() . '_' . \time();
                    $recipe->clearMediaCollection('logo')
                        ->addMediaFromRequest('image')
                        ->usingName($request->file('image')->getClientOriginalName())
                        ->usingFileName($name . '.' . $request->file('image')->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                }

                $recipeCategories = json_decode($payload['categories']);
                $recipe->recipeCategories()->sync($recipeCategories);
            }

            \DB::commit();

            return $this->successResponse([], 'Recipe added successfully');
            // dispatch job to awarg badge to user for running challenge
            // $this->dispatch(new SendGroupPushNotification($group, 'new-group'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Update recipe based on id.
     *
     * @param RecipeUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(RecipeUpdateRequest $request, Recipe $recipe)
    {
        try {
            \DB::beginTransaction();

            $user = $this->user();

            if ($recipe->creator_id != $user->getKey()) {
                return $this->notFoundResponse("You are not authorized to update this recipe");
            }

            $payload = $request->all();

            if (!empty($payload)) {
                $recipeInput = [
                    'title'        => $payload['title'],
                    'description'  => $payload['directions'],
                    'calories'     => $payload['calories'],
                    'cooking_time' => gmdate('H:i:s', (int) $payload['cooking_time']),
                    'servings'     => $payload['serves'],
                    'ingredients'  => json_encode((object) json_decode($payload['ingredients'])),
                    'nutritions'   => $payload['nutritions'],
                    'status'       => 0,
                ];

                if (isset($payload['image'])) {
                    $recipeInput['image'] = $payload['image']->getClientOriginalName();
                }

                $recipe->update($recipeInput);

                if ($request->hasFile('image')) {
                    $name = $recipe->getKey() . '_' . \time();
                    $recipe->clearMediaCollection('logo')
                        ->addMediaFromRequest('image')
                        ->usingName($request->file('image')->getClientOriginalName())
                        ->usingFileName($name . '.' . $request->file('image')->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                }

                $recipeCategories = json_decode($payload['categories']);
                $recipe->recipeCategories()->sync($recipeCategories);
            }

            \DB::commit();
            return $this->successResponse([], 'Recipe updated successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
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
            $user = $this->user();
            $role = getUserRole();
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

    /**
     * get saved recipe listing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function savedList(Request $request)
    {
        return $this->underMaintenanceResponse();
        try {
            $user = $this->user();

            $recipeRecords = $user->recipeLogs()
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
}

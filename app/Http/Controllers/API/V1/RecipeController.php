<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\RecipeListCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecipeCreateRequest;
use App\Http\Requests\Api\V1\RecipeUpdateRequest;
use App\Http\Resources\V1\RecipeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendRecipePushNotification;
use App\Models\Recipe;
use App\Models\RecipeCategories;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
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
        return $this->underMaintenanceResponse();
        try {
            $user = $this->user();

            $recipe_categories = RecipeCategories::where("status", 1)->get();

            $categoriesData = $recipe_categories->map(function ($item, $key) {
                return [
                    'id'    => $item->id,
                    'title' => $item->display_name,
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

            $data = [
                'categories' => $categoriesData,
                'nutritions' => $nutritionsData,
            ];

            return $this->successResponse(['data' => $data], 'Categories retrieved successfully.');
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
            // ->withCount([
            //     'recipeUserLogs as recipe_liked' => function ($query) {
            //         return $query->where('liked', true);
            //     },
            // ])
                ->where(function ($query) use ($company) {
                    $query->where('recipe.company_id', null)
                        ->orWhere('recipe.company_id', $company->id);
                })
                ->where('status', 1);

            if (!empty($request->categories)) {
                $recipeExploreData = $recipeExploreData->whereHas('recipeCategories', function ($query) use ($request) {
                    $query->whereIn('recipe_category_id', $request->categories);
                });
            }

            $recipeExploreData = $recipeExploreData
            // ->orderByRaw('recipe_liked DESC')
            ->orderBy('recipe.updated_at', 'DESC')
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
     * like-un-like recipe
     *
     * @param Request $request, Recipe $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlike(Request $request, Recipe $recipe)
    {
        try {
            $user = $this->user();

            $pivotExsisting = $recipe->recipeUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('recipe_id', $recipe->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $liked = $pivotExsisting->pivot->liked;

                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;

                $pivotExsisting->pivot->save();

                if ($liked == 1) {
                    return $this->successResponse(['data' => ['totalLikes' => $recipe->getTotalLikes()]], 'Recipe unliked successfully');
                } else {
                    $membersData = User::where("users.id", $recipe->creator_id)
                        ->leftJoin('user_notification_settings', function ($join) {
                            $join->on('user_notification_settings.user_id', '=', 'users.id')
                                ->where('user_notification_settings.flag', '=', 1)
                                ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['recipes', 'all']);
                        })
                        ->select('users.*', 'user_notification_settings.flag AS notification_flag')
                        ->groupBy('users.id')
                        ->get()
                        ->toArray();

                    if ($recipe->creator_id != $user->id) {
                        // dispatch job to recipe publisher notified that user reacted.
                        $this->dispatch(new SendRecipePushNotification($recipe, 'community-recipe-reaction', $membersData, $user->full_name));
                    }

                    return $this->successResponse(['data' => ['totalLikes' => $recipe->getTotalLikes()]], 'Recipe liked successfully');
                }
            } else {
                $recipe->recipeUserLogs()->attach($user, ['liked' => true]);

                $membersData = User::where("id", $recipe->creator_id)->get()->toArray();

                if ($recipe->creator_id != $user->id) {
                    // dispatch job to recipe publisher notified that user reacted.
                    $this->dispatch(new SendRecipePushNotification($recipe, 'community-recipe-reaction', $membersData, $user->full_name));
                }

                return $this->successResponse(['data' => ['totalLikes' => $recipe->getTotalLikes()]], 'Recipe liked successfully');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * save-un-save recipe
     *
     * @param Request $request, Recipe $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUnsave(Request $request, Recipe $recipe)
    {
        try {
            $user = $this->user();

            $pivotExsisting = $recipe->recipeUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('recipe_id', $recipe->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $saved = $pivotExsisting->pivot->saved;

                $pivotExsisting->pivot->saved = ($saved == 1) ? 0 : 1;

                $pivotExsisting->pivot->saved_at = now()->toDateTimeString();

                $pivotExsisting->pivot->save();

                if ($saved == 1) {
                    return $this->successResponse([], 'Recipe unsaved successfully');
                } else {
                    return $this->successResponse([], 'Recipe saved successfully');
                }
            } else {
                $recipe->recipeUserLogs()->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);

                return $this->successResponse([], 'Recipe saved successfully');
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

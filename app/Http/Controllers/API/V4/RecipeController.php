<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V4;

use App\Http\Collections\V4\RecipeListCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecipeCreateRequest;
use App\Http\Requests\Api\V1\RecipeUpdateRequest;
use App\Http\Resources\V4\RecipeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendRecipePushNotification;
use App\Models\Recipe;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

                $recipe->update($recipeInput);

                if ($request->hasFile('image')) {
                    foreach (($request->file('image')) as $key => $file) {
                        $name = $recipe->getKey() . '_' . Str::random() . \time();
                        $recipe->addMedia($file)
                            ->usingName($file->getClientOriginalName())
                            ->usingFileName($name . '.' . $file->extension())
                            ->toMediaCollection('logo', config('medialibrary.disk_name'));
                    }
                }

                if (!empty($request->deletedImages)) {
                    $deletedImages = json_decode($request->deletedImages);
                    foreach ($deletedImages as $mediaId) {
                        $media = $recipe->media->find($mediaId);
                        if (!empty($media)) {
                            $recipe->deleteMedia($media->id);
                        }
                    }
                }

                $recipesubcategories = json_decode($payload['subcategories']);
                $recipe->recipesubcategories()->sync($recipesubcategories);
            }

            \DB::commit();
            return $this->successResponse([], trans('api_messages.recipe.update'));
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

            return $this->successResponse([], trans('api_messages.recipe.delete'));
        } catch (\Exception $e) {
            \DB::rollback();
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
                    return $this->successResponse(['data' => ['totalLikes' => $recipe->getTotalLikes()]], trans('api_messages.recipe.unliked'));
                } else {
                    $membersData = User::where("users.id", $recipe->creator_id)
                        ->select('users.*', \DB::raw('1 AS notification_flag'))
                        ->groupBy('users.id')
                        ->get()
                        ->toArray();

                    if ($recipe->creator_id != $user->id) {
                        // dispatch job to recipe publisher notified that user reacted.
                        $this->dispatch(new SendRecipePushNotification($recipe, 'community-recipe-reaction', $membersData, $user->full_name));
                    }

                    return $this->successResponse(['data' => ['totalLikes' => $recipe->getTotalLikes()]], trans('api_messages.recipe.liked'));
                }
            } else {
                $recipe->recipeUserLogs()->attach($user, ['liked' => true]);

                $membersData = User::where("id", $recipe->creator_id)->get()->toArray();

                if ($recipe->creator_id != $user->id) {
                    // dispatch job to recipe publisher notified that user reacted.
                    $this->dispatch(new SendRecipePushNotification($recipe, 'community-recipe-reaction', $membersData, $user->full_name));
                }

                return $this->successResponse(['data' => ['totalLikes' => $recipe->getTotalLikes()]], trans('api_messages.recipe.liked'));
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
                    return $this->successResponse([], trans('api_messages.recipe.unsaved'));
                } else {
                    return $this->successResponse([], trans('api_messages.recipe.saved'));
                }
            } else {
                $recipe->recipeUserLogs()->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);

                return $this->successResponse([], trans('api_messages.recipe.saved'));
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

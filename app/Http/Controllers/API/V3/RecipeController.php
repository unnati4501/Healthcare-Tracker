<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\API\V2\RecipeController as v2RecipeController;
use App\Http\Requests\Api\V1\RecipeCreateRequest;
use App\Http\Requests\Api\V1\RecipeUpdateRequest;
use App\Http\Resources\V3\RecipeDetailResource;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecipeController extends v2RecipeController
{
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
                    'image'        => '',
                    // 'image'        => $payload['image']->getClientOriginalName(),
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

                $recipeCategories = json_decode($payload['categories']);
                $recipe->recipeCategories()->sync($recipeCategories);
            }

            \DB::commit();

            // dispatch job to awarg badge to user for running challenge
            // $this->dispatch(new SendGroupPushNotification($group, 'new-group'));

            return $this->successResponse([], 'Recipe added successfully');
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

                /*if (isset($payload['image'])) {
                $recipeInput['image'] = $payload['image']->getClientOriginalName();
                }*/

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
}

<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V33;

use App\Http\Controllers\API\V32\RecipeController as v32RecipeController;
use App\Jobs\SendRecipePushNotification;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends v32RecipeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
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
                	RemovePointContentActivities('recipe', $recipe->id, $user->id, 'like');
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

                    UpdatePointContentActivities('recipe', $recipe->id, $user->id, 'like');

                    return $this->successResponse(['data' => ['totalLikes' => $recipe->getTotalLikes()]], trans('api_messages.recipe.liked'));
                }
            } else {
            	UpdatePointContentActivities('recipe', $recipe->id, $user->id, 'like');

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
}

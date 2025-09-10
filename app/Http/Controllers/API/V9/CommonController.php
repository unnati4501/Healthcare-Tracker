<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V9;

use App\Http\Collections\V4\SubCategoryCollection;
use App\Http\Controllers\API\V8\CommonController as v8CommonController;
use App\Http\Requests\Api\V1\ShareContentRequest;
use App\Http\Resources\V9\GroupMessagesResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendContentSharePushNotification;
use App\Models\Category;
use App\Models\Course;
use App\Models\EAP;
use App\Models\Feed;
use App\Models\Group;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\SubCategory;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonController extends v8CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get list of master categories
     *
     * @param Request $request, Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubCategories(Request $request, Category $category)
    {
        try {
            $records = SubCategory::where('category_id', $category->id)
                ->orderBy('is_excluded', 'DESC')
                ->get();

            $records = $records->filter(function ($item, $key) use ($category) {
                if ($category->short_name == 'group') {
                    if ($item->short_name == 'public') {
                        return true;
                    }
                    return $item->groups()
                        ->where('is_archived', 0)
                        ->where('is_visible', 1)
                        ->count() > 0;
                } else {
                    return true;
                }
            });

            return $this->successResponse(($records->count() > 0) ? new SubCategoryCollection($records) : ['data' => []], 'Sub Categories Received Successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Set view count
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setViewCount(Request $request, $id, $modelType)
    {
        try {
            // logged-in user
            $user      = $this->user();
            $tableName = "";
            if ($modelType == 'feed') {
                $modelData = Feed::find($id);
                $tableName = "feeds";
            } elseif ($modelType == 'meditation') {
                $modelData = MeditationTrack::find($id);
                $tableName = "meditation_tracks";
            } elseif ($modelType == 'recipe') {
                $modelData = Recipe::find($id);
                $tableName = "recipe";
            } elseif ($modelType == 'eap') {
                $modelData = EAP::find($id);
                $tableName = "recipe";
            } else {
                return $this->notFoundResponse("Requested data not found");
            }

            if (!empty($modelData)) {
                if ($modelType == 'feed') {
                    $pivotExsisting = $modelData->feedUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('feed_id', $modelData->getKey())->first();
                } elseif ($modelType == 'meditation') {
                    $pivotExsisting = $modelData->trackUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('meditation_track_id', $modelData->getKey())->first();
                } elseif ($modelType == 'recipe') {
                    $pivotExsisting = $modelData->recipeUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('recipe_id', $modelData->getKey())->first();
                } elseif ($modelType == 'eap') {
                    $pivotExsisting = $modelData->eapUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('eap_id', $modelData->getKey())->first();
                }

                $updateCount = false;
                if (!empty($pivotExsisting)) {
                    if ($pivotExsisting->pivot->view_count < 2) {
                        $pivotExsisting->pivot->view_count = $pivotExsisting->pivot->view_count + 1;
                        $pivotExsisting->pivot->save();
                        $updateCount = true;
                    }
                } else {
                    if ($modelType == 'feed') {
                        $modelData->feedUserLogs()->attach($user, ['view_count' => 1]);
                    } elseif ($modelType == 'meditation') {
                        $modelData->trackUserLogs()->attach($user, ['view_count' => 1]);
                    } elseif ($modelType == 'recipe') {
                        $modelData->recipeUserLogs()->attach($user, ['view_count' => 1]);
                    } elseif ($modelType == 'eap') {
                        $modelData->eapUserLogs()->attach($user, ['view_count' => 1]);
                    }
                    $updateCount = true;
                }

                $view_count = $modelData->view_count;
                if ($updateCount) {
                    $view_count = $modelData->view_count + 1;

                    DB::table($tableName)
                        ->where("id", $modelData->id)
                        ->increment('view_count');
                }

                return $this->successResponse(['data' => ['viewCount' => $view_count]], 'View Count updated successfully.');
            } else {
                return $this->notFoundResponse("Requested data not found");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Share content as group message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function shareContent(ShareContentRequest $request)
    {
        try {
            $user                  = $this->user();
            $groupIds              = isset($request->groupIds) ? $request->groupIds : [];
            $notificationModelData = array();
            $tag                   = '';
            $moduleName            = ucfirst($request->modelType);
            $isMobile              = config('notification.home.story_shared.is_mobile');
            $isPortal              = config('notification.home.story_shared.is_portal');

            if ($request->modelType == 'feed') {
                $model      = Feed::find($request->modelId);
                $moduleName = 'Story';
                $tag        = 'feed';
                $isMobile   = config('notification.home.story_shared.is_mobile');
                $isPortal   = config('notification.home.story_shared.is_portal');
            } elseif ($request->modelType == 'masterclass') {
                $model    = Course::find($request->modelId);
                $tag      = 'masterclass';
                $isMobile = config('notification.academy.masterclass_shared.is_mobile');
                $isPortal = config('notification.academy.masterclass_shared.is_portal');
            } elseif ($request->modelType == 'meditation') {
                $model    = MeditationTrack::find($request->modelId);
                $tag      = 'meditation';
                $isMobile = config('notification.meditation.shared.is_mobile');
                $isPortal = config('notification.meditation.shared.is_portal');
            } elseif ($request->modelType == 'recipe') {
                $model    = Recipe::find($request->modelId);
                $tag      = 'recipe';
                $isMobile = config('notification.recipe.shared.is_mobile');
                $isPortal = config('notification.recipe.shared.is_portal');
            }

            if (!empty($groupIds)) {
                if (!empty($model)) {
                    $messageData = [];
                    foreach ($groupIds as $key => $value) {
                        $group = Group::find($value);

                        if (!empty($group)) {
                            $group->groupMessages()
                                ->attach($user, ['model_id' => $request->modelId, 'model_name' => $request->modelType]);

                            $group->update(['updated_at' => now()->toDateTimeString()]);

                            $title = trans('notifications.share.title');
                            $title = str_replace(['#module_name#'], [$moduleName], $title);

                            $notificationModelData['title']         = $title;
                            $notificationModelData['name']          = $model->title;
                            $notificationModelData['deep_link_uri'] = (!empty($model->deep_link_uri)) ? $model->deep_link_uri : "";

                            // dispatch job to send shared content notification to specified group members
                            \dispatch(new SendContentSharePushNotification($group, $notificationModelData, $user, ['tag' => $tag, 'is_mobile' => $isMobile, 'is_portal' => $isPortal]));

                            $groupMessagesData = $group->groupMessages()
                                ->wherePivot('user_id', '=', $user->getKey())
                                ->wherePivot('group_id', '=', $group->getKey())
                                ->orderBy('group_messages.created_at', 'DESC')
                                ->limit(1)
                                ->first();

                            $messageData[$group->id] = new GroupMessagesResource($groupMessagesData);
                        }
                    }

                    return $this->successResponse(['data' => $messageData], "{$moduleName} shared successfully.");
                } else {
                    return $this->notFoundResponse("Sorry! Unable to find {$moduleName}");
                }
            }

            \DB::rollback();
            return $this->successResponse(['data' => []], "Unable to share {$moduleName}");
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

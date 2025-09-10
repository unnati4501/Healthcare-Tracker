<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V34;

use App\Http\Controllers\API\V33\CommonController as v33CommonController;
use App\Http\Requests\Api\V1\ShareContentRequest;
use App\Http\Resources\V17\GroupMessagesResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendContentSharePushNotification;
use App\Models\Badge;
use App\Models\Course;
use App\Models\EAP;
use App\Models\Feed;
use App\Models\Group;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\User;
use App\Models\Webinar;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonController extends v33CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Set view count
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setViewCount(Request $request, $id, $modelType)
    {
        try {
            // logged-in user
            $user       = $this->user();
            $tableName  = "";
            $extraPoint = false;
            switch ($modelType) {
                case 'feed':
                    $modelData = Feed::find($id);
                    $tableName = "feeds";
                    break;
                case 'meditation':
                    $modelData = MeditationTrack::find($id);
                    $tableName = "meditation_tracks";
                    break;
                case 'recipe':
                    $modelData = Recipe::find($id);
                    $tableName = "recipe";
                    break;
                case 'eap':
                    $modelData = EAP::find($id);
                    $tableName = "eap_logs";
                    break;
                case 'webinar':
                    $modelData = Webinar::find($id);
                    $tableName = "webinar";
                    break;
                default:
                    return $this->notFoundResponse("Requested data not found");
                    break;
            }

            if (!empty($modelData)) {
                if ($modelType == 'feed') {
                    $extraPoint     = !is_null($modelData->tag_id) ;
                    $pivotExsisting = $modelData->feedUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('feed_id', $modelData->getKey())->first();
                } elseif ($modelType == 'meditation') {
                    $pivotExsisting = $modelData->trackUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('meditation_track_id', $modelData->getKey())->first();
                } elseif ($modelType == 'recipe') {
                    $pivotExsisting = $modelData->recipeUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('recipe_id', $modelData->getKey())->first();
                } elseif ($modelType == 'eap') {
                    $pivotExsisting = $modelData->eapUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('eap_id', $modelData->getKey())->first();
                } elseif ($modelType == 'webinar') {
                    $pivotExsisting = $modelData->webinarUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('webinar_id', $modelData->getKey())->first();
                }

                $updateCount      = false;
                $view_count       = "";
                $displayViewCount = "";
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
                    } elseif ($modelType == 'webinar') {
                        $modelData->webinarUserLogs()->attach($user, ['view_count' => 1]);
                    }
                    $updateCount      = false;
                    $view_count       = $modelData->view_count;
                    $displayViewCount = 1;
                }

                if ($updateCount) {
                    $view_count = $modelData->view_count + 1;

                    $result = DB::table($tableName)
                        ->where("id", $modelData->id)
                        ->increment('view_count');

                    $displayViewCount = $result + 1;
                }

                if (in_array($modelType, ['feed', 'recipe'])) {
                    UpdatePointContentActivities($modelType, $id, $user->id, 'open', false, $extraPoint);
                }

                return $this->successResponse(['data' => ['viewCount' => $displayViewCount]], 'View Count updated successfully.');
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
            $extraPoint            = false;
            $feedModelType         = '';
            if ($request->modelType == 'feed') {
                $model         = Feed::find($request->modelId);
                $extraPoint    = !is_null($model->tag_id) ;
                $moduleName    = 'Story';
                $tag           = 'feed';
                $feedModelType = (!empty($model->type) ? $model->type : '1');
                $isMobile      = config('notification.home.story_shared.is_mobile');
                $isPortal      = config('notification.home.story_shared.is_portal');
            } elseif ($request->modelType == 'masterclass') {
                $model      = Course::find($request->modelId);
                $extraPoint = !is_null($model->tag_id) ;
                $tag        = 'masterclass';
                $isMobile   = config('notification.academy.masterclass_shared.is_mobile');
                $isPortal   = config('notification.academy.masterclass_shared.is_portal');
            } elseif ($request->modelType == 'meditation') {
                $model      = MeditationTrack::find($request->modelId);
                $extraPoint = !is_null($model->tag_id) ;
                $tag        = 'meditation';
                $isMobile   = config('notification.meditation.shared.is_mobile');
                $isPortal   = config('notification.meditation.shared.is_portal');
            } elseif ($request->modelType == 'recipe') {
                $model    = Recipe::find($request->modelId);
                $tag      = 'recipe';
                $isMobile = config('notification.recipe.shared.is_mobile');
                $isPortal = config('notification.recipe.shared.is_portal');
            } elseif ($request->modelType == 'webinar') {
                $model      = Webinar::find($request->modelId);
                $extraPoint = !is_null($model->tag_id) ;
                $tag        = 'webinar';
                $isMobile   = config('notification.workshop.shared.is_mobile');
                $isPortal   = config('notification.workshop.shared.is_portal');
            } elseif ($request->modelType == 'badge') {
                $model = Badge::leftJoin('badge_user', 'badge_user.badge_id', '=', 'badges.id')
                    ->where('badge_user.id', $request->modelId)
                    ->select('badges.id', 'badge_user.id as badgeUserId', 'badges.title')
                    ->first();
                $tag      = 'badge';
                $isMobile = config('notification.general_badges.shared.is_mobile');
                $isPortal = config('notification.general_badges.shared.is_portal');
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

                            $deeplinkURI = $model instanceof Badge ? 'zevolife://zevo/badge/' . $model->badgeUserId : $model->deep_link_uri;

                            $notificationModelData['title']         = $title;
                            $notificationModelData['name']          = $model->title;
                            $notificationModelData['deep_link_uri'] = (!empty($deeplinkURI)) ? $deeplinkURI : "";

                            // dispatch job to send shared content notification to specified group members

                            \dispatch(new SendContentSharePushNotification($group, $notificationModelData, $user, ['tag' => $tag, 'is_mobile' => $isMobile, 'is_portal' => $isPortal, 'module_name' => ucfirst($moduleName), 'feedModelType' => $feedModelType]));

                            $groupMessagesData = $group->groupMessages()
                                ->wherePivot('user_id', '=', $user->getKey())
                                ->wherePivot('group_id', '=', $group->getKey())
                                ->orderBy('group_messages.created_at', 'DESC')
                                ->limit(1)
                                ->first();

                            $messageData[$group->id] = new GroupMessagesResource($groupMessagesData);
                        }
                    }

                    if (in_array($request->modelType, ['feed', 'masterclass', 'meditation', 'webinar', 'recipe'])) {
                        UpdatePointContentActivities($request->modelType, $model->id, $user->id, 'share', false, $extraPoint);
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

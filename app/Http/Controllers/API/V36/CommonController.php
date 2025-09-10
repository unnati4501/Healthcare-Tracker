<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V36;

use App\Http\Controllers\API\V34\CommonController as v34CommonController;
use App\Http\Collections\V36\SubCategoryCollection as v36subcategorycollection;
use App\Http\Collections\V36\SavedContentImagesCollection;
use App\Http\Collections\V26\HomeLeaderboardCollection;
use App\Http\Collections\V8\RecommendationCollection;
use App\Http\Collections\V20\FeedListCollection;
use App\Http\Collections\V36\RecentPodcastCollection;
use App\Http\Collections\V6\HomeCourseCollection;
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
use App\Models\SubCategory;
use App\Models\Podcast;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\MoodUser;
use App\Models\UserGoal;
use App\Models\ZcSurveyLog;
use App\Models\ZcSurveyUserLog;
use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommonController extends v34CommonController
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
            $user      = $this->user();
            $company   = $user->company()->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $records   = SubCategory::where('category_id', $category->id)
                ->orderBy('is_excluded', 'DESC')
                ->get();
            $team = $user->teams()->first();
            if ($category->short_name == 'meditation') {
                $favouritedCount = $user->userTrackrLogs()->wherePivot('favourited', true)->count();

                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $subcategoryRecords = $records->filter(function ($item, $key) use ($category, $company) {
                        return $item->meditations()->join('meditation_tracks_company', function ($join) use ($company) {
                            $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                                ->where('meditation_tracks_company.company_id', $company->id);
                        })->count() > 0;
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();
                    $checkFavCount      = MeditationTrack::join('user_meditation_track_logs', 'user_meditation_track_logs.meditation_track_id', 'meditation_tracks.id')
                        ->join('meditation_tracks_team', function ($join) use ($team) {
                            $join->on('meditation_tracks_team.meditation_track_id', '=', 'meditation_tracks.id')
                                ->where('meditation_tracks_team.team_id', $team->id);
                        })
                        ->select('meditation_tracks.id')
                        ->where("user_meditation_track_logs.user_id", $user->id)
                        ->where(["user_meditation_track_logs.favourited" => 1])
                        ->count();
                    $records = ($checkFavCount <= 0) ? $subcategoryRecords : $subcategoryRecords + array(0 => "My Fav");
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();
                    $records            = $subcategoryRecords + array(0 => "My Fav") + array(-1 => "View all");
                }

                $new_array = array_map(function ($id, $name) {
                    return array(
                        'id'         => $id,
                        'name'       => $name,
                        'short_name' => str_replace(' ', '_', strtolower($name)),
                    );
                }, array_keys($records), $records);

                $records = SubCategory::hydrate($new_array);
            }

            if ($category->short_name == 'recipe') {
                $favouritedCount = $user->recipeLogs()
                    ->wherePivot('favourited', true)
                    ->count();

                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $subcategoryRecords = $records->filter(function ($item, $key) {
                        return $item->recipes()->count() > 0;
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();
                    $checkFavCount      = Recipe::join('recipe_user', 'recipe_user.recipe_id', 'recipe.id')
                        ->join('recipe_team', function ($join) use ($team) {
                            $join->on('recipe_team.recipe_id', '=', 'recipe.id')
                                ->where('recipe_team.team_id', $team->id);
                        })
                        ->select('recipe.id')
                        ->where("recipe_user.user_id", $user->id)
                        ->where(["recipe_user.favourited" => 1])
                        ->count();
                    $records = ($checkFavCount <= 0) ? $subcategoryRecords : $subcategoryRecords + array(0 => "My Fav");
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();
                    $records            = $subcategoryRecords + array(0 => "My Fav") + array(-1 => "All");
                }

                $new_array = array_map(function ($id, $name) {
                    return array(
                        'id'         => $id,
                        'name'       => $name,
                        'short_name' => str_replace(' ', '_', strtolower($name)),
                    );
                }, array_keys($records), $records);

                $records = SubCategory::hydrate($new_array);
            }

            if ($category->short_name == 'course') {
                $favouritedCount = $user->courseLogs()->wherePivot('favourited', true)->count();
                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $companyId          = $company->id;
                    $subcategoryRecords = $records->filter(function ($item, $key) use ($companyId, $team) {
                        $categoryCount = Course::where("sub_category_id", $item->id)
                            ->join('masterclass_team', function ($join) use ($team) {
                                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                                    ->where('masterclass_team.team_id', $team->id);
                            })
                            ->where("courses.status", true)
                            ->count();
                        return ($categoryCount > 0) ? $item : [];
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();
                    $checkFavCount      = Course::join('user_course', 'user_course.course_id', 'courses.id')
                        ->join('masterclass_team', function ($join) use ($team) {
                            $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                                ->where('masterclass_team.team_id', $team->id);
                        })
                        ->select('courses.id')
                        ->where("user_course.user_id", $user->id)
                        ->where(["user_course.favourited" => 1])
                        ->count();
                    $records = ($checkFavCount <= 0) ? $subcategoryRecords : $subcategoryRecords + array(0 => "My Fav");
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();
                    $records            = $subcategoryRecords + array(0 => "My Fav") + array(-1 => "View all");
                }
                $new_array = array_map(function ($id, $name) {
                    return array(
                        'id'         => $id,
                        'name'       => $name,
                        'short_name' => str_replace(' ', '_', strtolower($name)),
                    );
                }, array_keys($records), $records);

                $records = SubCategory::hydrate($new_array);
            }

            if ($category->short_name == 'feed') {
                $favouritedCount = $user->feedLogs()->wherePivot('favourited', true)->count();
                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $timezone           = $user->timezone ?? config('app.timezone');
                    $companyId          = $company->id;
                    $subcategoryRecords = $records->filter(function ($item, $key) use ($timezone, $companyId, $team) {
                        $feedCount = Feed::where("sub_category_id", $item->id)
                            ->join('feed_team', function ($join) use ($team) {
                                $join->on('feeds.id', '=', 'feed_team.feed_id')
                                    ->where('feed_team.team_id', '=', $team->getKey());
                            })
                            ->join('sub_categories', function ($join) {
                                $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                            })
                            ->where(function (Builder $query) use ($timezone) {
                                return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                            })
                            ->where(function (Builder $query) use ($timezone) {
                                return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                            })
                            ->where("feeds.sub_category_id", $item->id)
                            ->count();

                        return ($feedCount > 0) ? $item : [];
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();
                    $checkFavCount      = Feed::join('feed_user', 'feed_user.feed_id', 'feeds.id')
                        ->join('feed_team', function ($join) use ($team) {
                            $join->on('feeds.id', '=', 'feed_team.feed_id')
                                ->where('feed_team.team_id', '=', $team->getKey());
                        })
                        ->where(function (Builder $query) use ($timezone) {
                            return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                        })
                        ->where(function (Builder $query) use ($timezone) {
                            return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                        })
                        ->select('feeds.id')
                        ->where("feed_user.user_id", $user->id)
                        ->where(["feed_user.favourited" => 1])
                        ->count();

                    $records = ($checkFavCount <= 0) ? $subcategoryRecords : $subcategoryRecords + array(0 => "My Fav");
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();
                    $records            = $subcategoryRecords + array(0 => "My Fav") + array(-1 => "View all");
                }
                $new_array = array_map(function ($id, $name) {
                    return array(
                        'id'         => $id,
                        'name'       => $name,
                        'short_name' => str_replace(' ', '_', strtolower($name)),
                    );
                }, array_keys($records), $records);

                $records = SubCategory::hydrate($new_array);
            }

            if ($category->short_name == 'webinar') {
                $favouritedCount = $user->webinarLogs()->wherePivot('favourited', true)->count();
                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $subcategoryRecords = $records->filter(function ($item, $key) use ($company, $team) {
                        $categoryCount = Webinar::where("sub_category_id", $item->id)
                            ->join('webinar_team', function ($join) use ($team) {
                                $join->on('webinar_team.webinar_id', '=', 'webinar.id')
                                    ->where('webinar_team.team_id', $team->id);
                            })
                            ->count();
                        return ($categoryCount > 0) ? $item : [];
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();
                    $checkFavCount      = Webinar::join('webinar_user', 'webinar_user.webinar_id', 'webinar.id')
                        ->join('webinar_team', function ($join) use ($team) {
                            $join->on('webinar_team.webinar_id', '=', 'webinar.id')
                                ->where('webinar_team.team_id', $team->id);
                        })
                        ->select('webinar.id')
                        ->where("webinar_user.user_id", $user->id)
                        ->where(["webinar_user.favourited" => 1])
                        ->count();
                    $records = ($checkFavCount <= 0) ? $subcategoryRecords : $subcategoryRecords + array(0 => "My Fav");
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();
                    $records            = $subcategoryRecords + array(0 => "My Fav") + array(-1 => "View all");
                }
                $new_array = array_map(function ($id, $name) {
                    return array(
                        'id'         => $id,
                        'name'       => $name,
                        'short_name' => str_replace(' ', '_', strtolower($name)),
                    );
                }, array_keys($records), $records);

                $records = SubCategory::hydrate($new_array);
            }

            if ($category->short_name == 'podcast') {
                $favouritedCount = $user->userPodcastLogs()->wherePivot('favourited', true)->count();

                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $subcategoryRecords = $records->filter(function ($item, $key) use ($category, $company) {
                        return $item->podcasts()->join('podcast_company', function ($join) use ($company) {
                            $join->on('podcast_company.podcast_id', '=', 'podcasts.id')
                                ->where('podcast_company.company_id', $company->id);
                        })->count() > 0;
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();
                    $checkFavCount      = Podcast::join('user_podcast_logs', 'user_podcast_logs.podcast_id', 'podcasts.id')
                        ->join('podcast_team', function ($join) use ($team) {
                            $join->on('podcast_team.podcast_id', '=', 'podcasts.id')
                                ->where('podcast_team.team_id', $team->id);
                        })
                        ->select('podcasts.id')
                        ->where("user_podcast_logs.user_id", $user->id)
                        ->where(["user_podcast_logs.favourited" => 1])
                        ->count();
                    $records = ($checkFavCount <= 0) ? $subcategoryRecords : $subcategoryRecords + array(0 => "My Fav");
                } else {
                    $subcategoryRecords = $records->pluck("name", "id")->toArray();
                    $records            = $subcategoryRecords + array(0 => "My Fav") + array(-1 => "View all");
                }

                $new_array = array_map(function ($id, $name) {
                    return array(
                        'id'         => $id,
                        'name'       => $name,
                        'short_name' => str_replace(' ', '_', strtolower($name)),
                    );
                }, array_keys($records), $records);

                $records = SubCategory::hydrate($new_array);
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $records = $records->filter(function ($item, $key) use ($category) {
                    switch ($category->short_name) {
                        case 'group':
                            if ($item->short_name == 'public') {
                                return true;
                            }
                            return $item->groups()
                                ->where('is_archived', 0)
                                ->where('is_visible', 1)
                                ->count() > 0;
                            break;
                        default:
                            return true;
                            break;
                    }
                });
            } else {
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

                if ($category->short_name == 'course' || $category->short_name == 'webinar') {
                    $records   = $records->pluck("name", "id")->toArray();
                    $new_array = array_map(function ($id, $name) {
                        return array(
                            'id'         => $id,
                            'name'       => $name,
                            'short_name' => str_replace(' ', '_', strtolower($name)),
                        );
                    }, array_keys($records), $records);

                    $records = SubCategory::hydrate($new_array);
                }
            }

            return $this->successResponse(($records->count() > 0) ? new v36subcategorycollection($records) : ['data' => []], 'Sub Categories Received Successfully.');
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
                case 'podcast':
                    $modelData = Podcast::find($id);
                    $tableName = "podcasts";
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
                } elseif ($modelType == 'podcast') {
                    $pivotExsisting = $modelData->podcastUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('podcast_id', $modelData->getKey())->first();
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
                    } elseif ($modelType == 'podcast') {
                        $modelData->podcastUserLogs()->attach($user, ['view_count' => 1]);
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
            } elseif ($request->modelType == 'podcast') {
                $model      = Podcast::find($request->modelId);
                $extraPoint = !is_null($model->tag_id) ;
                $tag        = 'podcast';
                $isMobile   = config('notification.podcast.shared.is_mobile');
                $isPortal   = config('notification.podcast.shared.is_portal');
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

    /**
     * Display max 3 images of all the content like webinar, feed, meditation, masterclass and recipes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavedContentImages(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();
            $team    = $user->teams()->first();

            $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                ->join('sub_categories', 'sub_categories.id', '=', 'meditation_tracks.sub_category_id')
                ->join('meditation_tracks_team', function ($join) use ($team) {
                    $join->on('meditation_tracks_team.meditation_track_id', '=', 'meditation_tracks.id')
                        ->where('meditation_tracks_team.team_id', $team->id);
                })
                ->where("user_meditation_track_logs.user_id", $user->id)
                ->where(["user_meditation_track_logs.saved" => 1, "sub_categories.status" => 1])
                ->pluck("meditation_tracks.id")
                ->toArray();
            if (!empty($trackIds)) {
                $meditationRecords = MeditationTrack::whereIn("meditation_tracks.id", $trackIds)
                    ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                            ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                    })
                    ->leftJoin('user_meditation_track_logs', function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                            ->where('user_meditation_track_logs.user_id', '=', $user->getKey());
                    })
                    ->select("meditation_tracks.*")
                    ->orderBy('user_meditation_track_logs.saved_at', 'DESC')
                    ->orderBy('meditation_tracks.id', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->get();
            }

            $feedRecords = $user->feedLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                })
                ->join('feed_team', function ($join) use ($team) {
                    $join->on('feeds.id', '=', 'feed_team.feed_id')
                        ->where('feed_team.team_id', '=', $team->getKey());
                })
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('feed_user.saved_at', 'DESC')
                ->orderBy('feed_user.id', 'DESC')
                ->groupBy('feeds.id')
                ->get();

            $recipeRecords = Recipe::
                with('recipesubcategories')
                ->select(
                    'recipe.id'
                )
                ->join('recipe_user', 'recipe_user.recipe_id', '=', 'recipe.id')
                ->join('recipe_team', function ($join) use ($team) {
                    $join
                        ->on('recipe_team.recipe_id', '=', 'recipe.id')
                        ->where('recipe_team.team_id', $team->id);
                })
                ->whereHas('recipesubcategories', function ($query) {
                    $query->where('status', 1);
                })
                ->where('recipe_user.user_id', $user->getKey())
                ->where('recipe_user.saved', true)
                ->orderBy('recipe_user.saved_at', 'DESC')
                ->orderBy('recipe_user.id', 'DESC')
                ->groupBy('recipe.id')
                ->get();

            $masterclassRecords = Course::select("courses.id", "courses.title", "courses.creator_id")
                ->leftJoin('user_course', function ($join) use ($user) {
                    $join->on('courses.id', '=', 'user_course.course_id')
                        ->where('user_course.user_id', '=', $user->getKey());
                })
                ->join('masterclass_team', function ($join) use ($team) {
                    $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                        ->where('masterclass_team.team_id', $team->id);
                })
                ->where("courses.status", true)
                ->where("user_course.saved", true)
                ->orderBy('courses.created_at', 'DESC')
                ->groupBy('courses.id')->get();

            $webinarRecords = $user->webinarLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'webinar.sub_category_id');
                })
                ->join('webinar_team', function ($join) use ($team) {
                    $join->on('webinar.id', '=', 'webinar_team.webinar_id')
                        ->where('webinar_team.team_id', '=', $team->getKey());
                })
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('webinar_user.saved_at', 'DESC')
                ->orderBy('webinar_user.id', 'DESC')
                ->groupBy('webinar.id')
                ->get();

            $podcastIds = Podcast::join('user_podcast_logs', 'podcasts.id', '=', 'user_podcast_logs.podcast_id')
                ->join('sub_categories', 'sub_categories.id', '=', 'podcasts.sub_category_id')
                ->join('podcast_team', function ($join) use ($team) {
                    $join->on('podcast_team.podcast_id', '=', 'podcasts.id')
                        ->where('podcast_team.team_id', $team->id);
                })
                ->where("user_podcast_logs.user_id", $user->id)
                ->where(["user_podcast_logs.saved" => 1, "sub_categories.status" => 1])
                ->pluck("podcasts.id")
                ->toArray();

            if (!empty($podcastIds)) {
                $podcastRecords = Podcast::whereIn("podcasts.id", $podcastIds)
                    ->leftJoin("user_incompleted_podcasts", function ($join) use ($user) {
                        $join->on('podcasts.id', '=', 'user_incompleted_podcasts.podcast_id')
                            ->where('user_incompleted_podcasts.user_id', '=', $user->getKey());
                    })
                    ->leftJoin('user_podcast_logs', function ($join) use ($user) {
                        $join->on('podcasts.id', '=', 'user_podcast_logs.podcast_id')
                            ->where('user_podcast_logs.user_id', '=', $user->getKey());
                    })
                    ->select("podcasts.*")
                    ->orderBy('user_podcast_logs.saved_at', 'DESC')
                    ->orderBy('podcasts.id', 'DESC')
                    ->groupBy('podcasts.id')
                    ->get();
            }

            $contentData['meditation']  = $meditationRecords ?? [];
            $contentData['feed']        = $feedRecords ?? [];
            $contentData['recipe']      = $recipeRecords ?? [];
            $contentData['masterclass'] = $masterclassRecords ?? [];
            $contentData['webinar']     = $webinarRecords ?? [];
            $contentData['podcast']     = $podcastRecords ?? [];

            if (!empty($meditationRecords) || $feedRecords->count() > 0 || $recipeRecords->count() > 0 || $masterclassRecords->count() > 0 || $webinarRecords->count() > 0 || (!empty($podcastRecords) && $podcastRecords->count() > 0)) {
                return $this->successResponse((!empty($contentData)) ? new SavedContentImagesCollection($contentData) : ['data' => []], 'Content retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Home leaderboard screen
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function homeLeaderboard(Request $request)
    {
        try {
            $user       = $this->user();
            $company    = $user->company()->first();
            $timezone   = $user->timezone ?? config('app.timezone');
            $days = isset($request->days) ? (int) $request->days : 7;

            if ($days == 0) {
                $challenges = Challenge::select(
                    'challenges.id',
                    'challenges.title',
                    'challenges.challenge_type',
                    'challenges.start_date',
                    'challenges.end_date',
                    'challenge_participants.user_id',
                    'challenge_participants.team_id'
                )->leftJoin('challenge_participants', 'challenge_participants.challenge_id', '=', 'challenges.id')
                    ->where(function ($query) use ($user) {
                        $query->where('challenge_participants.user_id', $user->id)
                            ->orWhere('challenge_participants.team_id', $user->teams()->first()->id);
                    })
                    ->where(function ($query) use ($timezone) {
                        $query->where('challenges.start_date', '<=', now()->toDateTimeString())->where('challenges.end_date', '>=', now($timezone)->toDateTimeString())
                            ->orWhere(function ($q1) use ($timezone) {
                                $q1->where('challenges.start_date', '>', now()->toDateTimeString());
                            });
                    })
                    ->where("cancelled", false)
                    ->orderBy('challenges.end_date', 'ASC')
                    ->orderByRaw("FIELD(challenge_type, 'individual', 'team', 'company_goal', 'inter_company')")
                    ->get();

                $challengeLeaderboard = [];
                foreach ($challenges as $key => $value) {
                    $startDate  = Carbon::parse($value->start_date)->setTimezone($timezone)->toDateTimeString();
                    $endDate    = Carbon::parse($value->end_date)->setTimezone($timezone)->toDateTimeString();
                    $now        = now($timezone)->toDateTimeString();
                    
                    // Hide Rank if challenge is ongoing
                    if($startDate > $now){
                        $status = 'upcoming';
                    }else if($startDate <= $now && $endDate >= $now){
                        $status = 'ongoing';
                    }

                    if ($value->challenge_type == 'individual') {
                        $rank = $value->challengeWiseUserPoints()
                            ->where('challenge_wise_user_ponits.challenge_id', $value->id)
                            ->where('challenge_wise_user_ponits.user_id', $user->id)
                            ->pluck('challenge_wise_user_ponits.rank')
                            ->first();
                    } else {
                        $rank = $value->challengeWiseTeamPoints()
                            ->where('challenge_wise_team_ponits.challenge_id', $value->id)
                            ->where('challenge_wise_team_ponits.team_id', $user->teams()->first()->id)
                            ->pluck('challenge_wise_team_ponits.rank')
                            ->first();
                    }
                    if (!empty($rank)) {
                        $challengeLeaderboard[] = [
                            'id'        => $value->id,
                            'name'      => $value->title,
                            'rank'      => $rank,
                            'steps'     => 0,
                            'image'     => $value->getMediaData('logo', ['w' => 640, 'h' => 640, 'zc' => 3]),
                            'status'    => $status
                        ];
                    }
                }

                return $this->successResponse(['data' => $challengeLeaderboard], 'Challenge leaderboard data retrieved successfully.');
            }

            $end   = Carbon::today()->subDay()->endOfDay()->toDateTimeString();
            $start = Carbon::today()->subDays($days)->toDateTimeString();

            $companyUsersList = $company->members()
                ->where('users.is_blocked', 0)
                ->where('users.can_access_app', 1)
                ->where('users.step_last_sync_date_time', '>', $start)
                ->pluck('users.id')
                ->toArray();

            $companyUsersList = (count($companyUsersList) > 0) ? $companyUsersList : [0];

            $companyUserList = implode(',', $companyUsersList);

            $results = \DB::select("SELECT temp.*, @rownum:=@rownum+1 AS rank_no FROM (SELECT @rownum := 0) AS dummy CROSS JOIN (SELECT users.id, CONCAT(users.first_name,' ',users.last_name) AS name, SUM(user_step.steps) AS steps, user_step.created_at FROM users LEFT JOIN user_step ON user_step.user_id = users.id WHERE user_step.user_id IN (" . $companyUserList . ") AND user_step.log_date BETWEEN '" . $start . "' AND '" . $end . "' AND steps > 0 GROUP BY user_step.user_id ORDER BY steps DESC, user_step.created_at ASC LIMIT 5) AS temp");

            $records = user::hydrate($results);

            // Check current user in result or not.
            $recordsArray = $records->toArray();

            $loginUserName = $user->first_name . ' ' . $user->last_name;
            $isUsers       = in_array($loginUserName, array_column($recordsArray, 'name'));
            if (!$isUsers) {
                // Get rank no from list of user step
                $getCurrentUserNumber = \DB::select("SELECT zcs.user_id, @rownum:=@rownum+1 AS rank_no
                                            FROM (SELECT @rownum := 0) AS dummy
                                            CROSS JOIN (
                                            SELECT `user_step`.`user_id`, sum(`user_step`.`steps`) as steps from user_step INNER JOIN user_team ON `user_team`.`user_id` = `user_step`.`user_id` WHERE `user_team`.`company_id` = '" . $company->id . "' AND `user_step`.`log_date` BETWEEN '" . $start . "' AND '" . $end . "' AND `user_step`.`steps` > 0 GROUP BY `user_step`.`user_id` ORDER BY steps DESC, user_step.created_at ASC
                                            ) AS zcs");

                $getResults = user::hydrate($getCurrentUserNumber)->pluck('rank_no', 'user_id')->toArray();

                $userRank = (array_key_exists($user->id, $getResults)) ? $getResults[$user->id] : null;

                // Get current user records with user step.
                $recordsUser = User::leftJoin('user_step', 'user_step.user_id', '=', 'users.id')
                    ->where('user_step.user_id', $user->id)
                    ->select('users.id', \DB::raw("CONCAT(first_name,' ',last_name) AS name"), \DB::raw("SUM(user_step.steps) as steps"))
                    ->whereBetween('user_step.log_date', [$start, $end])
                    ->first();

                if ($recordsUser && $userRank) {
                    // Bind with original records.
                    $loginUserArray = array([
                        'id'      => $recordsUser->id,
                        'name'    => $recordsUser->name,
                        'rank_no' => $userRank,
                        'steps'   => $recordsUser->steps,
                    ]);

                    $finalUsersArray = user::hydrate($loginUserArray);

                    $records->push($finalUsersArray[0]);
                }
            }

            $data = new HomeLeaderboardCollection($records);

            return $this->successResponse(['data' => $data], 'Home Leaderboard data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Home statistics for move nourish inspire sliders
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHomeStatistics(Request $request)
    {
        try {
            $user             = $this->user();
            $company          = $user->company()->first();
            $appTimezone      = config('app.timezone');
            $timezone         = !empty($user->timezone) ? $user->timezone : $appTimezone;
            $todayDateInUTC   = now($user->timezone)->setTimezone($appTimezone)->toDateTimeString();
            $data             = [];
            $userSelectedGoal = $user->userGoalTags()->pluck("goals.id")->toArray();
            $role             = getUserRole();
            $team             = $user->teams()->first();
            // User daily steps
            $userDailySteps = UserGoal::select('steps')->where('user_id', $user->id)->first();

            // first check does user has submitted any survey till date
            $hasSubmittedSurvey = ZcSurveyResponse::select('id')
                ->where('zc_survey_responses.user_id', $user->id)->limit(1)->first();
            $checkForPrevoiusSurveyScoreIfAny = false;

            // if submitted survey previously then show score and isSubmitted to true
            if (!empty($hasSubmittedSurvey)) {
                // check does he has any active survey
                $zcsurveylog = ZcSurveyLog::select('id', 'survey_to_all')
                    ->where('company_id', $company->id)
                    ->where('roll_out_date', '<=', $todayDateInUTC)
                    ->where('expire_date', '>=', $todayDateInUTC)
                    ->first();

                if (!empty($zcsurveylog) && $user->start_date <= $todayDateInUTC) {
                    $zcSurveyUserLog = ZcSurveyUserLog::select('id', 'survey_submitted_at')
                        ->where('user_id', $user->id)
                        ->where('survey_log_id', $zcsurveylog->id)
                        ->first();

                    // if user log is present then avail survey or if survey_to_all is set to false and user isn't present in survey user logs then prevent user to avail the survey as user isn't selected in survey config
                    if (!empty($zcSurveyUserLog) || (is_null($zcSurveyUserLog) && $zcsurveylog->survey_to_all)) {
                        if (!empty($zcSurveyUserLog) && !is_null($zcSurveyUserLog->survey_submitted_at)) {
                            $checkForPrevoiusSurveyScoreIfAny = true;
                        } else {
                            $data['surveyinfo'] = [
                                'surveyId'         => $zcsurveylog->id,
                                'alreadySubmitted' => false,
                                'score'            => 0.0,
                            ];
                        }
                    } else {
                        $checkForPrevoiusSurveyScoreIfAny = true;
                    }
                } else {
                    $checkForPrevoiusSurveyScoreIfAny = true;
                }

                if ($checkForPrevoiusSurveyScoreIfAny) {
                    // get previously submitted survey and calculate score accordingly
                    $zcSurveyUserLog = ZcSurveyUserLog::select('id', 'survey_log_id')
                        ->where('user_id', $user->id)
                        ->whereNotNull('survey_submitted_at')
                        ->orderByDesc('id')
                        ->first();

                    if (!empty($zcSurveyUserLog)) {
                        $allOverSurveyResponse = ZcSurveyResponse::select(\DB::raw("FORMAT(IFNULL(((IFNULL(SUM(zc_survey_responses.score), 0) * 100) / IFNULL(SUM(zc_survey_responses.max_score), 0)), 0), 1) AS percentage"))
                            ->where('zc_survey_responses.user_id', $user->id)
                            ->where('zc_survey_responses.survey_log_id', $zcSurveyUserLog->survey_log_id)
                            ->groupBy('zc_survey_responses.user_id')
                            ->first();

                        $data['surveyinfo'] = [
                            'surveyId'         => 0,
                            'alreadySubmitted' => true,
                            'score'            => ((!empty($allOverSurveyResponse) && !empty($allOverSurveyResponse->percentage)) ? (float) $allOverSurveyResponse->percentage : 0.0),
                        ];
                    }
                }
            } else {
                // if not submitted any survey then find present if exist
                $zcsurveylog = ZcSurveyLog::select('id', 'survey_to_all')
                    ->where('company_id', $company->id)
                    ->where('roll_out_date', '<=', $todayDateInUTC)
                    ->where('expire_date', '>=', $todayDateInUTC)
                    ->first();

                if (!empty($zcsurveylog) && $user->start_date <= $todayDateInUTC) {
                    $zcSurveyUserLog = ZcSurveyUserLog::select('id', 'survey_submitted_at')
                        ->where('user_id', $user->id)
                        ->where('survey_log_id', $zcsurveylog->id)
                        ->first();

                    // if user log is present then avail survey or if survey_to_all is set to false and user isn't present in survey user logs then prevent user to avail the survey as user isn't selected in survey config
                    if (!empty($zcSurveyUserLog) || (is_null($zcSurveyUserLog) && $zcsurveylog->survey_to_all)) {
                        $data['surveyinfo'] = [
                            'surveyId'         => $zcsurveylog->id,
                            'alreadySubmitted' => false,
                            'score'            => 0.0,
                        ];
                    }
                }
            }

            // User statistics data for current day
            $userCalorieHistory = $user->steps()->select(\DB::raw("SUM(user_step.calories) as calories"), \DB::raw("SUM(user_step.steps) as steps"), \DB::raw("SUM(user_step.distance) as distances"))
                ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->first();

            $companyUsersList = $company->members()->pluck('users.id')->toArray();

            $endDate = Carbon::today()->subDay()->toDateTimeString();
            $start   = Carbon::parse($endDate)->subDays(6)->toDateTimeString();
            $end     = Carbon::today()->subDay()->endOfDay()->toDateTimeString();

            $companyLeader = User::leftJoin('user_step', 'user_step.user_id', '=', 'users.id')
                ->whereIn('user_step.user_id', $companyUsersList)
                ->whereBetween('user_step.log_date', array($start, $end))
                ->select('users.id', \DB::raw("SUM(user_step.steps) as steps"))
                ->groupBy('user_step.user_id')
                ->orderBy('steps', 'DESC')
                ->orderBy('user_step.created_at', 'ASC')
                ->first();

            $latestRecipe = Recipe::select('recipe.id', 'recipe.title')
                ->join('recipe_company', function ($join) use ($company) {
                    $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
                })
                ->where('recipe.status', 1)
                ->orderBy('recipe.id', 'DESC')
                ->first();

            $data['userstatistics'] = [
                'dailySteps'    => (!empty($userCalorieHistory) && !empty($userCalorieHistory['steps'])) ? (int) $userCalorieHistory['steps'] : 0,
                'dailyDistance' => (!empty($userCalorieHistory) && !empty($userCalorieHistory['distances'])) ? (int) $userCalorieHistory['distances'] : 0,
                'dailyCalories' => (!empty($userCalorieHistory) && !empty($userCalorieHistory['calories'])) ? (double) $userCalorieHistory['calories'] : 0.0,
                'goalSteps'     => (!empty($userDailySteps) || $userDailySteps != null) ? $userDailySteps->steps : 0,
            ];

            if (!empty($companyLeader) && $companyLeader->steps > 0) {
                $companyLeaderArray = [
                    'id'    => $companyLeader->id,
                    'steps' => (int) $companyLeader->steps,
                    'image' => $companyLeader->getMediaData('logo', ['w' => 320, 'h' => 320, 'zc' => 0]),
                ];
                $data['userstatistics']['companyLeader'] = $companyLeaderArray;
            }

            // get user's running lessions with course data
            $runningCourseRecords = $user->courseLogs()
                ->join('masterclass_company', function ($join) use ($company) {
                    $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                        ->where('masterclass_company.company_id', $company->id);
                })
                ->where("courses.status", true)
                ->wherePivot("completed", false)
                ->orderByDesc('user_course.joined_on');

            // use count based on receieved data from course API total data count must be 5 max.
            $runningCourseRecordsData = $runningCourseRecords->limit(5)->get();

            // collect required course data
            $data['masterclasses'] = [
                'totalCount' => $runningCourseRecords->count(),
                'data'       => new HomeCourseCollection($runningCourseRecordsData),
            ];

            // Feed List get max 5 feed for home statistics
            $feedRecords = Feed::join('feed_company', function ($join) use ($company) {
                $join->on('feeds.id', '=', 'feed_company.feed_id')
                    ->where('feed_company.company_id', '=', $company->getKey());
            });

            $feedRecords->join('sub_categories', function ($join) {
                $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
            })
                ->leftJoin('companies', 'companies.id', '=', 'feeds.company_id')
                ->select('feeds.*', 'sub_categories.name AS sub_category_name');
            $feedRecords->selectRaw("(CASE feeds.type WHEN 1 THEN 'feed_audio' WHEN 2 THEN 'feed_video' WHEN 3 THEN 'feed_youtube' WHEN 4 THEN 'feed' WHEN 5 THEN 'feed_vimeo' ELSE 'feed' END) as 'goalContentType'");

            if ($role->group == 'company' && is_null($company->parent_id) && !$company->is_reseller) {
                $feedRecords->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                            ELSE 2
                            END AS is_stick_count"));
            } else {
                if ($company->parent_id == null && $company->is_reseller) {
                    $feedRecords->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                            ELSE 2
                            END AS is_stick_count"));
                } elseif (!is_null($company->parent_id)) {
                    $feedRecords->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN companies.parent_id IS NULL AND feeds.company_id IS NOT NULL AND feeds.is_stick != 0 then 1
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != 0 then 2
                            ELSE 3
                            END AS is_stick_count"));
                } else {
                    $feedRecords->addSelect(DB::raw("CASE
                            WHEN feeds.company_id = " . $company->id . " AND feeds.is_stick != 0 then 0
                            WHEN feeds.company_id IS NULL AND feeds.is_stick != '' then 1
                            ELSE 2
                            END AS is_stick_count"));
                }
            }

            $feedRecords->where(function (Builder $query) use ($timezone) {
                return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
            })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            $userGoalFeed = $feedRecords;
            $feedRecords  = $feedRecords
                ->groupBy('feeds.id')
                ->orderBy('is_stick_count', 'ASC')
                ->orderBy('feeds.id', 'DESC')
                ->limit(5)
                ->get();
            // merge upcomming events with story
            $storycollection  = new Collection();
            $checkEventAccess = getCompanyPlanAccess($user, 'event');

            if ($checkEventAccess) {
                // get upcomming non-registered 3 events
                $from   = now(config('app.timezone'))->setTime(0, 0, 0);
                $to     = $from->copy()->addDays(6)->setTime(23, 59, 59, 999999);
                $events = $company
                    ->evnetBookings()
                    ->select(
                        'events.id',
                        'event_booking_logs.id AS booking_log_id',
                        'event_booking_logs.event_id',
                        'events.name',
                        'events.description',
                        'events.capacity',
                        \DB::raw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) AS eventStartTime"),
                        \DB::raw("event_registered_users_logs.is_cancelled AS isRegistered"),
                        \DB::raw("0 AS type"),
                        \DB::raw("(SELECT COUNT(id) FROM event_registered_users_logs WHERE event_registered_users_logs.event_booking_log_id = event_booking_logs.id AND is_cancelled = 0) AS users_count")
                    )
                    ->leftJoin('event_registered_users_logs', function ($join) use ($user) {
                        $join
                            ->on('event_registered_users_logs.event_booking_log_id', '=', 'event_booking_logs.id')
                            ->where('event_registered_users_logs.user_id', $user->id);
                    })
                    ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN '{$from}' AND '{$to}'")
                    ->where('event_booking_logs.add_to_story', true)
                    ->where('event_booking_logs.status', '4')
                    ->where('events.status', '2')
                    ->havingRaw("isRegistered IS NULL")
                    ->havingRaw("(events.capacity IS NULL OR events.capacity > users_count)")
                    ->groupBy('event_booking_logs.id')
                    ->orderBy('eventStartTime')
                    ->limit(3)
                    ->get();
                $storycollection = $storycollection->merge($events);
            }

            $storycollection = $storycollection->merge($feedRecords);
            $data['feeds']   = new FeedListCollection($storycollection, true);

            $recomendedSection = [];

            // Get Mood Survey Fill or not
            $moodUserSubmitted = MoodUser::select('id')->where('user_id', $user->id)->where(\DB::raw("DATE(CONVERT_TZ(date, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())->first();

            $data['moodSubmitted'] = (!empty($moodUserSubmitted) );
            if (!empty($userSelectedGoal)) {
                $userGoalFeed = $userGoalFeed
                    ->join('feed_tag', function ($join) {
                        $join->on('feed_tag.feed_id', '=', 'feeds.id');
                    })
                    ->whereIn("feed_tag.goal_id", $userSelectedGoal)
                    ->groupBy('feeds.id')
                    ->get();
                if ($userGoalFeed->isNotEmpty() && $userGoalFeed->count() > 1) {
                    $userGoalFeed = $userGoalFeed->random(1);
                }

                $userGoalRecipe = Recipe::select("recipe.*", DB::raw("'recipe' goalContentType"))
                    ->join('recipe_tag', function ($join) {
                        $join->on('recipe_tag.recipe_id', '=', 'recipe.id');
                    })
                    ->join('recipe_company', function ($join) use ($company) {
                        $join->on('recipe_company.recipe_id', '=', 'recipe.id')
                            ->where('recipe_company.company_id', $company->id);
                    })
                    ->where("recipe.status", true)
                    ->whereIn("recipe_tag.goal_id", $userSelectedGoal)
                    ->groupBy('recipe.id')
                    ->get();
                if ($userGoalRecipe->isNotEmpty() && $userGoalRecipe->count() > 1) {
                    $userGoalRecipe = $userGoalRecipe->random(1);
                }

                $userGoalMeditation = MeditationTrack::select("meditation_tracks.*", DB::raw("'meditation' goalContentType"))
                    ->join('meditation_tracks_tag', function ($join) {
                        $join->on('meditation_tracks_tag.meditation_track_id', '=', 'meditation_tracks.id');
                    })
                    ->join('meditation_tracks_company', function ($join) use ($company) {
                        $join->on('meditation_tracks_company.meditation_track_id', '=', 'meditation_tracks.id')
                            ->where('meditation_tracks_company.company_id', $company->id);
                    })
                    ->whereIn("meditation_tracks_tag.goal_id", $userSelectedGoal)
                    ->groupBy('meditation_tracks.id')
                    ->get();
                if ($userGoalMeditation->isNotEmpty() && $userGoalMeditation->count() > 1) {
                    $userGoalMeditation = $userGoalMeditation->random(1);
                }

                $userGoalCourse = Course::select("courses.*", DB::raw("'masterclass' goalContentType"))
                    ->join('course_tag', function ($join) {
                        $join->on('course_tag.course_id', '=', 'courses.id');
                    })
                    ->join('masterclass_company', function ($join) use ($company) {
                        $join->on('masterclass_company.masterclass_id', '=', 'courses.id')
                            ->where('masterclass_company.company_id', $company->id);
                    })
                    ->where("courses.status", true)
                    ->whereIn("course_tag.goal_id", $userSelectedGoal)
                    ->groupBy('courses.id')
                    ->get();
                if ($userGoalCourse->isNotEmpty() && $userGoalCourse->count() > 1) {
                    $userGoalCourse = $userGoalCourse->random(1);
                }

                // Get Webinar Records
                $userGoalWebinar = Webinar::select("webinar.*", DB::raw("'webinar' goalContentType"))
                    ->join('webinar_tag', function ($join) {
                        $join->on('webinar_tag.webinar_id', '=', 'webinar.id');
                    })
                    ->join('webinar_company', function ($join) use ($company) {
                        $join->on('webinar_company.webinar_id', '=', 'webinar.id')
                            ->where('webinar_company.company_id', $company->id);
                    })
                    ->whereIn("webinar_tag.goal_id", $userSelectedGoal)
                    ->groupBy('webinar.id')
                    ->get();
                if ($userGoalWebinar->isNotEmpty() && $userGoalWebinar->count() > 1) {
                    $userGoalWebinar = $userGoalWebinar->random(1);
                }

                $recomendedCollection = new Collection();
                $recomendedCollection = $recomendedCollection->merge($userGoalCourse);
                $recomendedCollection = $recomendedCollection->merge($userGoalFeed);
                $recomendedCollection = $recomendedCollection->merge($userGoalMeditation);
                $recomendedCollection = $recomendedCollection->merge($userGoalRecipe);
                $recomendedCollection = $recomendedCollection->merge($userGoalWebinar);

                if ($recomendedCollection->isNotEmpty()) {
                    $recomendedSection = new RecommendationCollection($recomendedCollection);
                }
            }

            $data['recommendation'] = $recomendedSection;

            // for custom labels
            $companyLabelString       = $company->companyWiseLabelString()->pluck('label_name', 'field_name')->toArray();
            $defaultLabelString       = config('zevolifesettings.company_label_string', []);
            $finalCompanyLabelStrings = [];

            // iterate default labels loop and check is label's custom value is set then user custom value else default value
            foreach ($defaultLabelString as $groupKey => $groups) {
                foreach ($groups as $labelKey => $labelValue) {
                    $label = ($companyLabelString[$labelKey] ?? $labelValue['default_value']);
                    if (in_array($labelKey, ['location_logo', 'department_logo'])) {
                        $label = $company->getMediaData($labelKey, ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                    }
                    $finalCompanyLabelStrings[$labelKey] = $label;
                }
            }
            $data['companyLabelString'] = $finalCompanyLabelStrings;

            // Display 10 podcasts as a slider
            $allPodcasts = Podcast::select(
                "podcasts.id",
                "podcasts.title"
            )
            ->join('podcast_company', function ($join) use ($company) {
                $join->on('podcast_company.podcast_id', '=', 'podcasts.id')
                    ->where('podcast_company.company_id', $company->id);
            })
            ->join('podcast_team', function ($join) use ($team) {
                $join->on('podcast_team.podcast_id', '=', 'podcasts.id')
                    ->where('podcast_team.team_id', $team->id);
            })
            ->orderBy('podcasts.updated_at', 'DESC')
            ->orderBy('podcasts.id', 'DESC')
            ->groupBy('podcasts.id');

            $allPodcastCount = $allPodcasts->get()->count();
            $podcasts        = $allPodcasts->limit(10)->get()->toArray();

            $podcastData        = Podcast::hydrate($podcasts);
            if($allPodcastCount > 0 && !empty($podcasts)){
                $data['podcasts']   = [
                    'totalCount' => $allPodcastCount,
                    'data'       => new RecentPodcastCollection($podcastData, false)
                ];
            }

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

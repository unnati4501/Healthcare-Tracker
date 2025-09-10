<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V41;

use App\Http\Collections\V26\HomeLeaderboardCollection;
use App\Http\Controllers\API\V40\CommonController as v40CommonController;
use App\Http\Collections\V41\SubCategoryCollection as v41subcategorycollection;
use App\Http\Collections\V41\SavedContentImagesCollection;
use App\Http\Requests\Api\V1\ShareContentRequest;
use App\Jobs\SendContentSharePushNotification;
use App\Http\Resources\V17\GroupMessagesResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Badge;
use App\Models\Challenge;
use App\Models\User;
use App\Models\Category;
use App\Models\Shorts;
use App\Models\Course;
use App\Models\EAP;
use App\Models\Feed;
use App\Models\Group;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\Webinar;
use App\Models\SubCategory;
use App\Models\Podcast;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CommonController extends v40CommonController
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
                    $subcategoryRecords = $records->filter(function ($item) use ($company) {
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
                    $subcategoryRecords = $records->filter(function ($item) {
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
                    $subcategoryRecords = $records->filter(function ($item) use ($team) {
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
                    $companyId          = $company->id;
                    $subcategoryRecords = $records->filter(function ($item) use ($team) {
                        $feedCount = Feed::where("sub_category_id", $item->id)
                            ->join('feed_team', function ($join) use ($team) {
                                $join->on('feeds.id', '=', 'feed_team.feed_id')
                                    ->where('feed_team.team_id', '=', $team->getKey());
                            })
                            ->join('sub_categories', function ($join) {
                                $join->on('sub_categories.id', '=', 'feeds.sub_category_id');
                            })
                            ->where(function (Builder $query) {
                                return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                            })
                            ->where(function (Builder $query) {
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
                        ->where(function (Builder $query) {
                            return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                        })
                        ->where(function (Builder $query) {
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
                    $subcategoryRecords = $records->filter(function ($item) use ($team) {
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
                    $subcategoryRecords = $records->filter(function ($item) use ($company) {
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

            if ($category->short_name == 'shorts') {
                $favouritedCount = $user->userShortsLogs()->wherePivot('favourited', true)->count();
                if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                    $subcategoryRecords = $records->filter(function ($item) use ($team) {
                        $categoryCount = Shorts::where("sub_category_id", $item->id)
                            ->join('shorts_team', function ($join) use ($team) {
                                $join->on('shorts_team.webinar_id', '=', 'shorts.id')
                                    ->where('shorts_team.team_id', $team->id);
                            })
                            ->count();
                        return ($categoryCount > 0) ? $item : [];
                    });
                    $subcategoryRecords = $subcategoryRecords->pluck("name", "id")->toArray();
                    $checkFavCount      = shorts::join('shorts_user', 'shorts_user.short_id', 'shorts.id')
                        ->join('shorts_team', function ($join) use ($team) {
                            $join->on('shorts_team.webinar_id', '=', 'shorts.id')
                                ->where('shorts_team.team_id', $team->id);
                        })
                        ->select('shorts.id')
                        ->where("shorts_user.user_id", $user->id)
                        ->where(["shorts_user.favourited" => 1])
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
                $records = $records->filter(function ($item) use ($category) {
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

            return $this->successResponse(($records->count() > 0) ? new v41subcategorycollection($records) : ['data' => []], 'Sub Categories Received Successfully.');
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
                case 'short':
                    $modelData = Shorts::find($id);
                    $tableName = "shorts";
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
                } elseif ($modelType == 'short') {
                    $pivotExsisting = $modelData->shortsUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('short_id', $modelData->getKey())->first();
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
                    } elseif ($modelType == 'short') {
                        $modelData->shortsUserLogs()->attach($user, ['view_count' => 1]);
                    }
                    $updateCount    = false;
                    if ($modelType == 'short') {
                        $updateCount    = true;
                    }
                    
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
     * Display max 3 images of all the content like webinar, feed, meditation, masterclass and recipes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSavedContentImages(Request $request)
    {
        try {
            $user    = $this->user();
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

            $shortsRecords = $user->userShortsLogs()
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'shorts.sub_category_id');
                })
                ->join('shorts_team', function ($join) use ($team) {
                    $join->on('shorts.id', '=', 'shorts_team.short_id')
                        ->where('shorts_team.team_id', '=', $team->getKey());
                })
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('saved', true)
                ->orderBy('shorts_user.saved_at', 'DESC')
                ->orderBy('shorts_user.id', 'DESC')
                ->groupBy('shorts.id')
                ->get();

            $contentData['meditation']  = $meditationRecords ?? [];
            $contentData['feed']        = $feedRecords ?? [];
            $contentData['recipe']      = $recipeRecords ?? [];
            $contentData['masterclass'] = $masterclassRecords ?? [];
            $contentData['webinar']     = $webinarRecords ?? [];
            $contentData['podcast']     = $podcastRecords ?? [];
            $contentData['short']       = $shortsRecords ?? [];

            if (!empty($meditationRecords) || $feedRecords->count() > 0 || $recipeRecords->count() > 0 || $masterclassRecords->count() > 0 || $webinarRecords->count() > 0 || (!empty($podcastRecords) && $podcastRecords->count() > 0) || (!empty($shortsRecords) && $shortsRecords->count() > 0)) {
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
            } elseif ($request->modelType == 'short') {
                $model      = Shorts::find($request->modelId);
                $extraPoint = !is_null($model->tag_id) ;
                $tag        = 'short';
                $isMobile   = config('notification.shorts.shared.is_mobile');
                $isPortal   = config('notification.shorts.shared.is_portal');
            }

            if (!empty($groupIds)) {
                if (!empty($model)) {
                    $messageData = [];
                    foreach ($groupIds as $value) {
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
                            dispatch(new SendContentSharePushNotification($group, $notificationModelData, $user, ['tag' => $tag, 'is_mobile' => $isMobile, 'is_portal' => $isPortal, 'module_name' => ucfirst($moduleName), 'feedModelType' => $feedModelType]));

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

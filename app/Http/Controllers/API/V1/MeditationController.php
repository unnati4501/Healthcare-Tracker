<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Collections\V1\CategoryWiseTrackCollection;
use App\Http\Collections\V1\MeditationCoachCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MeditationDurationRequest;
use App\Http\Resources\V1\CategoryWiseTrackResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\AwardGeneralBadgeToUser;
use App\Models\Company;
use App\Models\MeditationCategory;
use App\Models\MeditationTrack;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeditationController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * API to fetch saved meditation data by user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saved(Request $request)
    {
        return $this->underMaintenanceResponse();
        try {
            // logged-in user
            $user = $this->user();

            $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                ->where("user_meditation_track_logs.user_id", $user->id)
                ->where("user_meditation_track_logs.saved", 1)
                ->pluck("meditation_tracks.id")
                ->toArray();

            if (!empty($trackIds)) {
                $categoryWiseTrackData = MeditationTrack::whereIn("meditation_tracks.id", $trackIds)
                    ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                            ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                    })
                    ->leftJoin('user_meditation_track_logs', function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                            ->where('user_meditation_track_logs.user_id', '=', $user->getKey());
                    })
                    ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"))
                    ->orderBy('user_meditation_track_logs.saved_at', 'DESC')
                    ->orderBy('meditation_tracks.id', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if ($categoryWiseTrackData->count() > 0) {
                    // collect required data and return response
                    return $this->successResponse(new CategoryWiseTrackCollection($categoryWiseTrackData), 'Meditations Retrieved Successfully');
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch meditation category list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            $favouritedCount = $user->userTrackrLogs()->wherePivot('favourited', true)->count();

            if ($favouritedCount > 0) {
                $records = array(0 => "Favourite") + MeditationCategory::get()->pluck("title", "id")->toArray();
            } else {
                $records = MeditationCategory::get()->pluck("title", "id")->toArray() + array(0 => "Favourite");
            }

            $new_array = array_map(function ($id, $name) {
                return array(
                    'id'   => $id,
                    'name' => ucfirst($name),
                );
            }, array_keys($records), $records);

            return $this->successResponse(['data' => $new_array], 'Meditation Categories Retrieved Successfully.');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     *  API to fetch track data by Meditation category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackList(Request $request)
    {
        return $this->underMaintenanceResponse();
        try {
            // logged-in user
            $user = $this->user();

            if ($request->meditationCategory != 0) {
                $catData = MeditationCategory::find($request->meditationCategory);

                if (empty($catData)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }
            if ($request->meditationCategory == 0) {
                $trackIds = MeditationTrack::join('user_meditation_track_logs', 'meditation_tracks.id', '=', 'user_meditation_track_logs.meditation_track_id')
                    ->where("user_meditation_track_logs.user_id", $user->id)
                    ->where("favourited", 1)
                    ->pluck("meditation_tracks.id")
                    ->toArray();

                if (empty($trackIds)) {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }

                $categoryWiseTrackData = MeditationTrack::whereIn("meditation_tracks.id", $trackIds)
                    ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                            ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                    })
                    ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                    ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"));

                if (!empty($request->insights)) {
                    $categoryWiseTrackData->whereIn("meditation_tracks.tag", $request->insights);
                }

                $categoryWiseTrackData = $categoryWiseTrackData->orderBy('meditation_tracks.updated_at', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            } else {
                $categoryWiseTrackData = MeditationTrack::where("meditation_category_id", $request->meditationCategory)
                    ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                        $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                            ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                    })
                    ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                    ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"));

                if (!empty($request->insights)) {
                    $categoryWiseTrackData->whereIn("meditation_tracks.tag", $request->insights);
                }

                $categoryWiseTrackData = $categoryWiseTrackData->orderBy('meditation_tracks.updated_at', 'DESC')
                    ->groupBy('meditation_tracks.id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            }

            if ($categoryWiseTrackData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new CategoryWiseTrackCollection($categoryWiseTrackData), 'Meditations Retrieved Successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch meditation coach data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function coachList(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            // get paginated Meditation coach data
            $meditationCoachList = MeditationTrack::join("users", "users.id", "=", "meditation_tracks.coach_id")
                ->leftJoin(DB::raw("(SELECT coach_id , AVG(NULLIF(ratings ,0)) as Avgratings , COUNT(NULLIF(ratings ,0)) as Totalreview from user_coach_log group by coach_id) as userCoach"), "meditation_tracks.coach_id", "=", "userCoach.coach_id")
                ->select('meditation_tracks.id', 'meditation_tracks.updated_at', 'meditation_tracks.coach_id', DB::raw("count(meditation_tracks.id) as totalMeditation"), DB::raw("CONCAT(users.first_name,' ',users.last_name) as name"), 'userCoach.Avgratings', 'userCoach.Totalreview')
                ->orderBy('userCoach.Avgratings', 'DESC')
                ->orderBy('meditation_tracks.updated_at', 'DESC')
                ->groupBy('meditation_tracks.coach_id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($meditationCoachList->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new MeditationCoachCollection($meditationCoachList), 'Coach List retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to favorited unfavourited MeditationTrack by MeditationTrack
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function favouriteUnfavouriteTrack(Request $request, MeditationTrack $meditation)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.mediation.favorited');
            $pivotExsisting = $meditation
                ->trackUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('meditation_track_id', $meditation->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $favourited                        = $pivotExsisting->pivot->favourited;
                $pivotExsisting->pivot->favourited = ($favourited == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();
                if ($favourited == 1) {
                    $message = trans('api_messages.mediation.unfavorited');
                }
            } else {
                $meditation
                    ->trackUserLogs()
                    ->attach($user, ['favourited' => true]);
            }

            \DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to liked unliked MeditationTrack by MeditationTrack
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlikeTrack(Request $request, MeditationTrack $meditation)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.mediation.liked');
            $pivotExsisting = $meditation
                ->trackUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('meditation_track_id', $meditation->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();
                if ($liked == 1) {
                    $message = trans('api_messages.mediation.unliked');
                }
            } else {
                $meditation
                    ->trackUserLogs()
                    ->attach($user, ['liked' => true]);
            }

            \DB::commit();
            return $this->successResponse(['data' => ['totalLikes' => $meditation->getTotalLikes()]], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to saved unsaved MeditationTrack by MeditationTrack
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUnsaveTrack(Request $request, MeditationTrack $meditation)
    {
        try {
            \DB::beginTransaction();

            $user           = $this->user();
            $message        = trans('api_messages.mediation.saved');
            $pivotExsisting = $meditation
                ->trackUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('meditation_track_id', $meditation->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $saved                           = $pivotExsisting->pivot->saved;
                $pivotExsisting->pivot->saved    = (($saved == 1) ? 0 : 1);
                $pivotExsisting->pivot->saved_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();

                if ($saved == 1) {
                    $message = trans('api_messages.mediation.unsaved');
                }
            } else {
                $meditation->trackUserLogs()->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);
            }

            \DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDuration(MeditationDurationRequest $request, MeditationTrack $meditation)
    {

        try {
            \DB::beginTransaction();
            // logged-in user
            $user = $this->user();

            // fetch user MeditationTrack data
            $pivotExsisting = $meditation->trackIncompletedUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('meditation_track_id', $meditation->getKey())->first();

            if (!empty($pivotExsisting)) {
                $pivotExsisting->pivot->duration_listened = $request->duration;

                $pivotExsisting->pivot->save();
            } else {
                $meditation->trackIncompletedUserLogs()->attach($user, ['duration_listened' => $request->duration]);
            }

            \DB::commit();
            return $this->successResponse([], 'Meditation duration(s) saved successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsCompleted(Request $request, MeditationTrack $meditation)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user                    = $this->user();
            $company                 = $this->user()->company()->first();
            $companyLimits           = $company->companyWiseChallengeSett()->pluck('value', 'type')->toArray();
            $timezone                = $user->timezone ?? config('app.timezone');
            $appTimezone             = config('app.timezone');
            $now                     = now($timezone)->toDateString();
            $daily_meditation_limits = ($companyLimits['daily_meditation_limit'] ?? config("zevolifesettings.default_limits.daily_meditation_limit", 10));
            $daily_track_limit       = ($companyLimits['daily_track_limit'] ?? config("zevolifesettings.default_limits.daily_track_limit", 5));
            // $daily_meditation_limits = config('zevolifesettings.daily_meditation_limits');

            // fetch user MeditationTrack data
            $meditation->trackIncompletedUserLogs()->wherePivot('user_id', $user->getKey())->detach();

            // get count of total listened meditation for current day by user
            $totalListenedMeditationCount = $user
                ->completedMeditationTracks()
                ->whereDate(\DB::raw("CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$timezone}')"), '=', $now)
                ->count('user_listened_tracks.meditation_track_id');

            // check user reach maxmimum meditation limit for the day
            if ($totalListenedMeditationCount < $daily_meditation_limits) {
                // get count of perticular meditation for current day listen by the user
                $meditationListenCount = $meditation
                    ->trackListenedUserLogs()
                    ->wherePivot('user_id', $user->getKey())
                    ->whereDate(\DB::raw("CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$timezone}')"), '=', $now)
                    ->count('user_listened_tracks.meditation_track_id');

                // check user reach maxmimum limit for the day for same meditation
                if ($meditationListenCount < $daily_track_limit) {
                    $meditation->trackListenedUserLogs()->attach($user, ['duration_listened' => $meditation->duration]);
                }
            }

            \DB::commit();

            // dispatch job to awarg badge to user for running challenge
            //$this->dispatch(new AwardChallengeBadgeToUser($user));

            // dispatch job to award general badge to user
            $this->dispatch(new AwardGeneralBadgeToUser($user, 'meditations'));

            return $this->successResponse([], 'Meditation marked as completed.');
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function meditationCoachList(Request $request)
    {
        $jsonString = '{"code":200,"message":"Coach List retrieved successfully.","result":{"data":[{"id":1,"name":"Varun Patel","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":3,"reviews":100,"meditations":10},{"id":2,"name":"Patrick Keefe","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":5,"reviews":50,"meditations":2},{"id":3,"name":"Avani Solanki","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":1,"reviews":0,"meditations":1},{"id":4,"name":"Avani Solanki","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":1,"reviews":0,"meditations":1},{"id":5,"name":"Urvish Shah","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":1,"reviews":0,"meditations":1},{"id":6,"name":"Avani Solanki","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":3,"reviews":0,"meditations":1},{"id":7,"name":"Avani Solanki","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":1,"reviews":0,"meditations":1},{"id":8,"name":"Avani Solanki","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":2,"reviews":0,"meditations":1},{"id":9,"name":"Avani Solanki","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":1,"reviews":0,"meditations":1},{"id":10,"name":"Avani Solanki","image":{"url":"' . asset('app_assets/image1.jpeg') . '","width":194,"height":259},"rating":1,"reviews":0,"meditations":1}],"pagination":{"total":10,"count":10,"per_page":10,"current_page":1,"total_pages":1}}}';

        return json_decode($jsonString, true);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function coachMeditations(Request $request, User $user)
    {
        try {
            // logged-in user
            $logginUser = $this->user();

            $categoryWiseTrackData = MeditationTrack::where("meditation_tracks.coach_id", $user->id)
                ->leftJoin("user_incompleted_tracks", function ($join) use ($user) {
                    $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                        ->where('user_incompleted_tracks.user_id', '=', $user->getKey());
                })
                ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"));

            if (!empty($request->insights)) {
                $categoryWiseTrackData->whereIn("meditation_tracks.tag", $request->insights);
            }

            $categoryWiseTrackData = $categoryWiseTrackData->orderBy('meditation_tracks.updated_at', 'DESC')
                ->groupBy('meditation_tracks.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseTrackData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new CategoryWiseTrackCollection($categoryWiseTrackData), 'Meditations Retrieved Successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackDetails(Request $request, MeditationTrack $meditation)
    {
        try {
            // logged-in user
            $logginUser = $this->user();

            $categoryWiseTrackData = MeditationTrack::where("meditation_tracks.id", $meditation->id)
                ->leftJoin("user_incompleted_tracks", function ($join) use ($logginUser) {
                    $join->on('meditation_tracks.id', '=', 'user_incompleted_tracks.meditation_track_id')
                        ->where('user_incompleted_tracks.user_id', '=', $logginUser->getKey());
                })
                ->leftJoin("user_meditation_track_logs", "meditation_tracks.id", "=", "user_meditation_track_logs.meditation_track_id")
                ->select("meditation_tracks.*", "user_incompleted_tracks.duration_listened", DB::raw("COUNT(NULLIF(liked ,0)) as totalLikes"))
                ->first();

            // get coach course details data with json response
            $data = array("data" => new CategoryWiseTrackResource($categoryWiseTrackData));
            return $this->successResponse($data, 'Meditation Retrieved Successfully');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

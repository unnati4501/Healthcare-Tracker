<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V21;

use App\Http\Collections\V11\HomeLeaderboardCollection;
use App\Http\Controllers\API\V20\CommonController as v20CommonController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Challenge;
use App\Models\Company;
use App\Models\EAP;
use App\Models\Feed;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\User;
use App\Models\Webinar;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonController extends v20CommonController
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
            $user      = $this->user();
            $tableName = "";

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
     * Home leaderboard screen
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function homeLeaderboard(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();

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
                    ->orderBy('challenges.end_date', 'ASC')
                    ->orderByRaw("FIELD(challenge_type, 'individual', 'team', 'company_goal', 'inter_company')")
                    ->get()
                    ->filter(function ($item) {
                        if (Carbon::now()->between($item->start_date, $item->end_date)) {
                            return $item;
                        }
                    });

                $challengeLeaderboard = [];
                foreach ($challenges as $key => $value) {
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
                            'id'    => $value->id,
                            'name'  => $value->title,
                            'rank'  => $rank,
                            'steps' => 0,
                            'image' => $value->getMediaData('logo', ['w' => 640, 'h' => 640]),
                        ];
                    }
                }

                return $this->successResponse(['data' => $challengeLeaderboard], 'Challenge leaderboard data retrieved successfully.');
            }

            $end   = Carbon::today()->subDay()->toDateTimeString();
            $start = Carbon::parse($end)->subDays($days)->toDateTimeString();

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
}

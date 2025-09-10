<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V20;

use App\Http\Collections\V20\ChallengeCompanyTeamDetailsCollection;
use App\Http\Collections\V20\ChallengeTeamMembersDetailsCollection;
use App\Http\Collections\V20\ChallengeUserListCollection;
use App\Http\Controllers\API\V19\ChallengeController as v19ChallengeController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Challenge;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeController extends v19ChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get users active in the challenge.
     *
     * @param Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChallengeUsers(Challenge $challenge)
    {
        try {
            // logged-in user
            $user       = $this->user();
            $company    = $user->company()->first();
            $userTeam   = $user->teams()->first();
            $start_date = Carbon::parse($challenge->start_date)->timezone($challenge->timezone);
            $end_date   = Carbon::parse($challenge->end_date)->timezone($challenge->timezone);

            $challengeType = 'individual';
            if ($challenge->challenge_type == 'individual') {
                if ($end_date > Carbon::now()->timezone($challenge->timezone)) {
                    $users = $challenge->members()
                        ->where('challenge_participants.status', 'Accepted')
                        ->orderBy('users.first_name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                } else {
                    $users = $challenge->challengeHistoryParticipants()
                        ->orderBy('participant_name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                }
            } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                $challengeType = 'team';
                if ($end_date > Carbon::now()->timezone($challenge->timezone)) {
                    $users = $challenge->memberTeams()
                        ->addSelect(DB::raw("IF(challenge_participants.team_id = '{$userTeam->id}', 0, 1) AS user_team_flag"))
                        ->where('challenge_participants.status', 'Accepted')
                        ->orderBy('user_team_flag', 'ASC')
                        ->orderBy('teams.name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                } else {
                    $users = $challenge->challengeHistoryParticipants()
                        ->select('freezed_challenge_participents.*', DB::raw("IF(freezed_challenge_participents.team_id = '{$userTeam->id}', 0, 1) AS user_team_flag"))
                        ->orderBy('user_team_flag', 'ASC')
                        ->orderBy('participant_name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                }
            } elseif ($challenge->challenge_type == 'inter_company') {
                $challengeType = 'company';
                if ($end_date > Carbon::now()->timezone($challenge->timezone)) {
                    $users = $challenge->memberCompanies()
                        ->addSelect(DB::raw("IF(challenge_participants.company_id = '{$company->id}', 0, 1) AS user_company_flag"))
                        ->where('challenge_participants.status', 'Accepted')
                        ->groupBy('company_id')
                        ->orderBy('user_company_flag', 'ASC')
                        ->orderBy('companies.name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                } else {
                    $users = $challenge->challengeHistoryParticipants()
                        ->select('freezed_challenge_participents.*', DB::raw("IF(freezed_challenge_participents.company_id = '{$company->id}', 0, 1) AS user_company_flag"))
                        ->groupBy('company_id')
                        ->orderBy('user_company_flag', 'ASC')
                        ->orderBy('participant_name', 'ASC')
                        ->paginate(config('zevolifesettings.datatable.pagination.short'));
                }
            }

            if ($users->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new ChallengeUserListCollection($users, $challengeType), 'Challenge participants listed successfully.');
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
     * Get Company teams
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyTeams(Request $request, Challenge $challenge)
    {
        if ($challenge->challenge_type != 'inter_company') {
            abort(403);
        }

        try {
            // logged-in user
            $user     = $this->user();
            $company  = $user->company()->first();
            $userTeam = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            if (empty($company)) {
                abort(403);
            }

            $challengeHistory = $challenge->challengeHistory;

            if (!empty($challengeHistory)) {
                // completed challenge detail resource
                $challengeTeamIds = $challenge
                    ->leftJoin("freezed_challenge_participents", function ($join) use ($company) {
                        $join->on("challenges.id", "=", "freezed_challenge_participents.challenge_id")
                            ->where('freezed_challenge_participents.company_id', $company->id);
                    })
                    ->select('freezed_challenge_participents.team_id', DB::raw("IF(freezed_challenge_participents.team_id = '{$userTeam->id}', 0, 1) AS user_team_flag"))
                    ->where('challenges.id', $challenge->getKey())
                    ->where("challenges.cancelled", false)
                    ->orderBy('user_team_flag', 'ASC')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if (!empty($challengeTeamIds)) {
                    return $this->successResponse(
                        ($challengeTeamIds->count() > 0) ? new ChallengeCompanyTeamDetailsCollection($challengeTeamIds) : ['data' => []],
                        ($challengeTeamIds->count() > 0) ? 'Company teams list retrieved successfully.' : 'No company teams found'
                    );
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            } else {
                // completed challenge detail resource
                $challengeTeamIds = $challenge
                    ->leftJoin("challenge_participants", function ($join) use ($company) {
                        $join->on("challenges.id", "=", "challenge_participants.challenge_id")
                            ->where('challenge_participants.company_id', $company->id);
                    })
                    ->select('challenge_participants.team_id', DB::raw("IF(challenge_participants.team_id = '{$userTeam->id}', 0, 1) AS user_team_flag"))
                    ->where('challenges.id', $challenge->getKey())
                    ->where("challenges.cancelled", false)
                    ->orderBy('user_team_flag', 'ASC')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                if (!empty($challengeTeamIds)) {
                    return $this->successResponse(
                        ($challengeTeamIds->count() > 0) ? new ChallengeCompanyTeamDetailsCollection($challengeTeamIds) : ['data' => []],
                        ($challengeTeamIds->count() > 0) ? 'Company teams list retrieved successfully.' : 'No company teams found'
                    );
                } else {
                    // return empty response
                    return $this->successResponse(['data' => []], 'No results');
                }
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get Team members of a challenge
     *
     * @param Request $request, Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeamMembers(Request $request, Challenge $challenge)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $company  = $user->company()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            if (empty($company)) {
                abort(403);
            }

            $challengeHistory = $challenge->challengeHistory;

            if (!empty($challengeHistory)) {
                $challengeUsers = $challenge
                    ->leftJoin('challenge_wise_user_ponits', 'challenge_wise_user_ponits.challenge_id', '=', 'challenges.id')
                    ->select('challenge_wise_user_ponits.user_id', 'challenge_wise_user_ponits.points')
                    ->where("challenges.id", $challenge->getKey())
                    ->where("challenges.cancelled", false)
                    ->where('challenge_wise_user_ponits.team_id', $user->teams()->first()->id)
                    ->orderBy('challenge_wise_user_ponits.points', 'DESC')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            } else {
                $challengeUsers = \DB::table('user_team')
                    ->where('user_team.team_id', $user->teams()->first()->id)
                    ->select('user_team.user_id')
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));
            }

            if (!empty($challengeUsers)) {
                return $this->successResponse(
                    ($challengeUsers->count() > 0) ? new ChallengeTeamMembersDetailsCollection($challengeUsers) : ['data' => []],
                    ($challengeUsers->count() > 0) ? 'Team members list retrieved successfully.' : 'No team members found'
                );
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
     * Get Leaderboard for challenge.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function leaderboard(Request $request, Challenge $challenge)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $company  = $user->company()->first();
            $userTeam = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $challengeHistory = $challenge->challengeHistory;

            if (!empty($challengeHistory)) {
                $challengeParticipantCount = $challenge->challengeHistoryParticipants()->count();

                if ($challenge->challenge_type == 'individual') {
                    $challengeParticipantsWithPoints = $challenge->challengeWiseUserPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.user_id', '=', 'challenge_wise_user_ponits.user_id')
                        ->select('freezed_challenge_participents.user_id', 'freezed_challenge_participents.participant_name', 'challenge_wise_user_ponits.challenge_id', 'challenge_wise_user_ponits.points', 'challenge_wise_user_ponits.rank')->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_user_ponits.challenge_id', $challenge->id)
                        ->where(DB::raw("round(challenge_wise_user_ponits.points,1)"), '>', 0)
                        ->orderBy('challenge_wise_user_ponits.rank', 'ASC')
                        ->orderBy('challenge_wise_user_ponits.user_id', 'ASC')
                        ->groupBy('challenge_wise_user_ponits.user_id');
                } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                    $challengeParticipantsWithPoints = $challenge->challengeWiseTeamPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.team_id', '=', 'challenge_wise_team_ponits.team_id')
                        ->select(
                            'freezed_challenge_participents.team_id',
                            'freezed_challenge_participents.participant_name',
                            'challenge_wise_team_ponits.challenge_id',
                            'challenge_wise_team_ponits.points',
                            'challenge_wise_team_ponits.rank'
                        )
                        ->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_team_ponits.challenge_id', $challenge->id)
                        ->where(DB::raw("round(challenge_wise_team_ponits.points,1)"), '>', 0)
                        ->orderBy('challenge_wise_team_ponits.rank', 'ASC')
                        ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
                        ->groupBy('challenge_wise_team_ponits.team_id');
                } elseif ($challenge->challenge_type == 'inter_company') {
                    $challengeParticipantCount = $challenge->challengeHistoryParticipants()->distinct('company_id')->pluck('company_id')->count();

                    $challengeParticipantsWithPoints = $challenge->challengeWiseCompanyPoints()
                        ->join('freezed_challenge_participents', 'freezed_challenge_participents.company_id', '=', 'challenge_wise_company_points.company_id')
                        ->select(
                            'freezed_challenge_participents.company_id',
                            'freezed_challenge_participents.participant_name',
                            'challenge_wise_company_points.challenge_id',
                            'challenge_wise_company_points.points',
                            'challenge_wise_company_points.rank'
                        )
                        ->where('freezed_challenge_participents.challenge_id', $challenge->id)
                        ->where('challenge_wise_company_points.challenge_id', $challenge->id)
                        ->where(DB::raw("round(challenge_wise_company_points.points,1)"), '>', 0)
                        ->orderBy('challenge_wise_company_points.rank', 'ASC')
                        ->orderBy('challenge_wise_company_points.company_id', 'ASC')
                        ->groupBy('challenge_wise_company_points.company_id');
                }

                if ($challenge->challenge_type == 'individual') {
                    if ($challengeParticipantCount > 25) {
                        $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->limit(10)->get();
                    } elseif ($challengeParticipantCount >= 10) {
                        $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->limit(5)->get();
                    } elseif ($challengeParticipantCount < 10 && $challengeParticipantCount >= 2) {
                        $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->limit(3)->get();
                    }
                } else {
                    $challengeParticipantsWithPoints = $challengeParticipantsWithPoints->get();
                }

                $challengeInfo = [
                    'id'      => $challenge->id,
                    'title'   => $challenge->title,
                    'members' => $challengeParticipantCount,
                    'type'    => $challenge->challenge_type,
                ];

                if ($challengeParticipantsWithPoints->count() > 0) {
                    $participantsData = [];
                    foreach ($challengeParticipantsWithPoints as $key => $record) {
                        $participant = [];

                        if ($challenge->challenge_type == 'individual') {
                            $participantId = $record->user_id;
                            $userRecord    = \App\Models\User::find($record->user_id);
                        } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                            $participantId = $record->team_id;
                            $userRecord    = \App\Models\Team::find($record->team_id);
                        } elseif ($challenge->challenge_type == 'inter_company') {
                            $participantId = $record->company_id;
                            $userRecord    = \App\Models\Company::find($record->company_id);
                        }

                        $participant['id']   = $participantId;
                        $participant['name'] = $record->participant_name;

                        $participant['deleted'] = false;
                        if (!empty($userRecord)) {
                            if ($challenge->challenge_type == 'inter_company') {
                                $participant['name'] = $userRecord->name;
                            }

                            $participant['image'] = $userRecord->getMediaData('logo', ['w' => 320, 'h' => 320]);
                        } else {
                            $img           = [];
                            $img['url']    = getDefaultFallbackImageURL("user", "user-none1");
                            $img['width']  = 0;
                            $img['height'] = 0;

                            if ($challenge->challenge_type == 'individual') {
                                $deletedText = 'Deleted User';
                            } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                                $deletedText = 'Deleted Team';
                            } elseif ($challenge->challenge_type == 'inter_company') {
                                $deletedText = 'Deleted Company';
                            }

                            $participant['name']    = $deletedText;
                            $participant['image']   = (object) $img;
                            $participant['deleted'] = true;
                        }

                        array_push($participantsData, [
                            'user'   => $participant,
                            'points' => (float) number_format((float) $record->points, 1, '.', ''),
                            'rank'   => $record->rank,
                        ]);
                    }

                    // collect required data and return response
                    return $this->successResponse(['data' => $participantsData, 'challenge' => $challengeInfo], 'Leaderboard data retrieved successfully.');
                } else {
                    // return empty response
                    return $this->successResponse(['data' => [], 'challenge' => $challengeInfo], 'No results');
                }
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
     * Get teams leaderboard for challenge
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myTeamsLeaderboard(Request $request, Challenge $challenge)
    {
        if ($challenge->challenge_type != 'inter_company') {
            abort(403);
        }
        try {
            // logged-in user
            $user     = $this->user();
            $userTeam = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $challengeHistory = $challenge->challengeHistory;

            if (!empty($challengeHistory)) {
                $challengeParticipantCount = $challenge->challengeHistoryParticipants()->distinct('company_id')->pluck('company_id')->count();

                $challengeParticipantsWithPoints = $challenge->challengeWiseTeamPoints()
                    ->join('freezed_challenge_participents', 'freezed_challenge_participents.team_id', '=', 'challenge_wise_team_ponits.team_id')
                    ->select(
                        'freezed_challenge_participents.team_id',
                        'freezed_challenge_participents.participant_name',
                        'challenge_wise_team_ponits.challenge_id',
                        'challenge_wise_team_ponits.points',
                        'challenge_wise_team_ponits.rank'
                    )
                    ->where('freezed_challenge_participents.challenge_id', $challenge->id)
                    ->where('challenge_wise_team_ponits.challenge_id', $challenge->id)
                    ->where('freezed_challenge_participents.company_id', $user->company()->first()->id)
                    ->where(DB::raw("round(challenge_wise_team_ponits.points,1)"), '>', 0)
                    ->orderBy('challenge_wise_team_ponits.rank', 'ASC')
                    ->orderBy('challenge_wise_team_ponits.team_id', 'ASC')
                    ->groupBy('challenge_wise_team_ponits.team_id')
                    ->get();

                $challengeInfo = [
                    'id'      => $challenge->id,
                    'title'   => $challenge->title,
                    'members' => $challengeParticipantCount,
                    'type'    => $challenge->challenge_type,
                ];

                if ($challengeParticipantsWithPoints->count() > 0) {
                    $participantsData = [];
                    foreach ($challengeParticipantsWithPoints as $key => $record) {
                        $participant = [];

                        $participantId = $record->team_id;
                        $companyRecord = \App\Models\Team::find($record->team_id);

                        $participant['id'] = $participantId;

                        $participant['deleted'] = false;
                        if (!empty($companyRecord)) {
                            $participant['name']  = $companyRecord->name;
                            $participant['image'] = $companyRecord->getMediaData('logo', ['w' => 320, 'h' => 320]);
                        } else {
                            $img           = [];
                            $img['url']    = getDefaultFallbackImageURL("user", "user-none1");
                            $img['width']  = 0;
                            $img['height'] = 0;

                            $participant['name']    = 'Deleted Team';
                            $participant['image']   = (object) $img;
                            $participant['deleted'] = true;
                        }

                        array_push($participantsData, [
                            'user'   => $participant,
                            'points' => (float) number_format((float) $record->points, 1, '.', ''),
                            'rank'   => $record->rank,
                        ]);
                    }

                    // collect required data and return response
                    return $this->successResponse(['data' => $participantsData, 'challenge' => $challengeInfo], 'Leaderboard data retrieved successfully.');
                } else {
                    // return empty response
                    return $this->successResponse(['data' => [], 'challenge' => $challengeInfo], 'No results');
                }
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
     * Get winners list for the challenge.
     *
     * @param Challenge $challenge
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWinnersList(Challenge $challenge)
    {
        try {
            // logged-in user
            $user     = $this->user();
            $timezone = $user->timezone ?? config('app.timezone');

            if ($challenge->challenge_type == 'individual') {
                $winners = \App\Models\ChallengeWiseUserLogData::with('user')
                    ->where('challenge_wise_user_log.challenge_id', $challenge->id)
                    ->where('challenge_wise_user_log.is_winner', 1)
                    ->get();
            } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                $winners = \App\Models\ChallengeWiseUserLogData::with('team')
                    ->where('challenge_wise_user_log.challenge_id', $challenge->id)
                    ->where('challenge_wise_user_log.is_winner', 1)
                    ->groupBy('challenge_wise_user_log.team_id')
                    ->distinct('challenge_wise_user_log.team_id')
                    ->get();
            } elseif ($challenge->challenge_type == 'inter_company') {
                $winners = \App\Models\ChallengeWiseUserLogData::with('company')
                    ->where('challenge_wise_user_log.challenge_id', $challenge->id)
                    ->where('challenge_wise_user_log.is_winner', 1)
                    ->groupBy('challenge_wise_user_log.company_id')
                    ->distinct('challenge_wise_user_log.company_id')
                    ->get();
            }

            if ($winners->count() > 0) {
                $winnerData = $winners->map(function ($query) use ($challenge) {

                    if ($challenge->challenge_type == 'individual') {
                        $winnerData['id'] = $query->user_id;
                        $userRecord       = \App\Models\User::find($query->user_id);
                        if (!empty($userRecord)) {
                            $winnerData['name'] = $userRecord->first_name . ' ' . $userRecord->last_name;
                        }
                    } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                        $winnerData['id'] = $query->team_id;
                        $userRecord       = \App\Models\Team::find($query->team_id);
                        if (!empty($userRecord)) {
                            $winnerData['name'] = $userRecord->name;
                        }
                    } elseif ($challenge->challenge_type == 'inter_company') {
                        $winnerData['id'] = $query->company_id;
                        $userRecord       = \App\Models\Company::find($query->company_id);
                        if (!empty($userRecord)) {
                            $winnerData['name'] = $userRecord->name;
                        }
                    }

                    $winnerData['deleted'] = false;
                    if (!empty($userRecord)) {
                        $winnerData['image'] = $userRecord->getMediaData('logo', ['w' => 320, 'h' => 320]);
                    } else {
                        $image           = [];
                        $image['url']    = getDefaultFallbackImageURL("user", "user-none1");
                        $image['width']  = 0;
                        $image['height'] = 0;

                        if ($challenge->challenge_type == 'individual') {
                            $deletedText = 'Deleted User';
                        } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
                            $deletedText = 'Deleted Team';
                        } elseif ($challenge->challenge_type == 'inter_company') {
                            $deletedText = 'Deleted Company';
                        }

                        $winnerData['name']    = $deletedText;
                        $winnerData['image']   = (object) $image;
                        $winnerData['deleted'] = true;
                    }

                    return $winnerData;
                });

                // collect required data and return response
                return $this->successResponse(['data' => $winnerData], 'Winners list retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'There was no winner of this challenge');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

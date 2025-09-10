<?php

namespace App\Http\Resources\V30;

use App\Http\Collections\V27\ChallengeBadgeCollection;
use App\Http\Collections\V30\ChallengeMapCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Badge;
use App\Models\Group;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeDetailsResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user                       = $this->user();
        $challenge_rule_description = config('zevolifesettings.challenge_rule_description');

        if ($this->challenge_type == 'individual') {
            $members       = $this->members()->wherePivot("status", "Accepted")->count();
            $loginUserData = $this->members()->wherePivot("challenge_id", $this->id)->wherePivot("user_id", $user->getKey())->first();
        } elseif ($this->challenge_type == 'team' || $this->challenge_type == 'company_goal') {
            $members       = $this->memberTeams()->count();
            $loginUserData = $this->memberTeams()->wherePivot("challenge_id", $this->id)->wherePivot("team_id", $user->teams()->first()->id)->first();
        } elseif ($this->challenge_type == 'inter_company') {
            $members       = $this->memberCompanies()->distinct('company_id')->pluck('company_id')->count();
            $loginUserData = $this->memberCompanies()
                ->wherePivot("challenge_id", $this->id)
                ->wherePivot("company_id", $user->company()->first()->id)
                ->first();
        }

        $challengeType = $this->challenge_type;

        $isStarted       = false;
        $isCompleted     = false;
        $timerData       = array();
        $currentDateTime = now($user->timezone)->toDateTimeString();
        $startDate       = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
        $endDate         = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();

        if ($currentDateTime >= $startDate && $currentDateTime <= $endDate) {
            $isStarted = true;
        }

        if ($currentDateTime > $endDate) {
            $isCompleted = true;
        }
        $currentAppDateTime = now(config('app.timezone'))->toDateTimeString();
        // Get challenge Badge Records with user achivement count
        $badgeData = Badge::select(
            'badges.id',
            'badges.title',
            'badges.type',
            'badges.description',
            'challenge_ongoing_badges.target as targetCount',
            'challenge_rules.uom as targetUnit',
            'challenge_ongoing_badges.in_days as challengeTargetDays',
            'challenges.end_date as challengeEndDate',
            'challenges.start_date as challengeStartDate',
            DB::raw('"' . $user->timezone . '" as userTimezone'),
            DB::raw("(SELECT id FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND model_id = " . $this->id . " AND STATUS = 'Active') AS badgeUserId"),
            DB::raw("(SELECT count(id) FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND model_id = " . $this->id . " AND STATUS = 'Active') AS assignCount")
        )
            ->leftJoin('challenge_ongoing_badges', 'challenge_ongoing_badges.badge_id', '=', 'badges.id')
            ->leftJoin('challenges', 'challenges.id', '=', 'challenge_ongoing_badges.challenge_id')
            ->leftJoin('challenge_rules', 'challenge_rules.challenge_id', '=', 'challenges.id')
            ->where(function ($query) use ($challengeType) {
                $query->where('badges.challenge_type_slug', $challengeType)
                    ->where('badges.is_default', 1)
                    ->where('badges.type', 'challenge');
            })->orWhere('challenge_ongoing_badges.challenge_id', $this->id)
            ->whereraw("IF ((badges.type !=  'challenge' and (SELECT count(id) FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND model_id = " . $this->id . " AND STATUS = 'Active') = 0), DATE_ADD(challenges.start_date, INTERVAL challenge_ongoing_badges.in_days DAY) > '$currentAppDateTime', challenges.start_date IS NOT NULL) ");
        //If challenge is completed then show the achived badges only
        if ($isCompleted ) {
            $badgeData = $badgeData->having("assignCount", '>', 0);
        }
        $badgeData = $badgeData->get();

        if ($currentDateTime < $startDate) {
            $timerData = calculatDayHrMin($currentDateTime, $startDate);
        } else {
            $timerData = calculatDayHrMin($currentDateTime, $endDate);
        }

        // unset($timerData['hour']);
        // unset($timerData['minute']);
        // $timerData['day'] = $timerData['day'] + 1;

        $challengeRulesData = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();

        $checkChallengeTargetType = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_targets.short_name')->first();

        if ($checkChallengeTargetType->short_name == 'meditations') {
            $challenge_rule_description = config('zevolifesettings.challenge_rule_description_meditation');
        }

        $ruleText = "";

        if (!empty($this->challenge_category_id) && !empty($challenge_rule_description[$this->challenge_type]) && $challenge_rule_description[$this->challenge_type][$this->challenge_category_id]) {
            $ruleText .= $challenge_rule_description[$this->challenge_type][$this->challenge_category_id];
        }

        $targetArray = [];
        foreach ($challengeRulesData as $outer => $rule) {
            $challengeRulesArray = [];
            $exercisesName       = "";
            if ($rule->challenge_target_id == 4 && !empty($rule->model_id)) {
                $exerciseObj = \App\Models\Exercise::where("id", $rule->model_id)->first();
                if (!empty($exerciseObj)) {
                    $exercisesName = $exerciseObj->title;
                }
            }

            $challengeRulesArray['targetId'] = $rule->challenge_target_id;
            if (!empty($exerciseObj) && $rule->challenge_target_id == 4) {
                $challengeRulesArray['name'] = $exercisesName;
            } else {
                $challengeRulesArray['name'] = $rule->name;
            }
            $challengeRulesArray['targetUOM'] = $rule->uom;

            $ruleType = $rule->short_name;
            if ($rule->short_name == 'exercises') {
                if ($rule->uom == 'meter') {
                    $ruleType = 'exercises_distance';
                } else {
                    $ruleType = 'exercises_duration';
                }
            }

            $challengeSettingValue = $this->leftJoin('companies', 'challenges.company_id', '=', 'companies.id')
                ->leftJoin('company_wise_challenge_settings', 'company_wise_challenge_settings.company_id', '=', 'companies.id')
                ->where('company_wise_challenge_settings.type', $ruleType)
                ->where('company_wise_challenge_settings.company_id', $this->company_id)
                ->pluck('company_wise_challenge_settings.value')
                ->first();

            if (empty($challengeSettingValue)) {
                $challengeSettingValues = config('zevolifesettings.default_limits');
                $challengeSettingValue  = $challengeSettingValues[$ruleType];
            }

            $challengeSettingTargetUOM = $challengeRulesArray['targetUOM'] == 'count' ? '' : ucfirst($challengeRulesArray['targetUOM']) . " of ";

            $challengeRulesArray['ruleText'] = $challengeSettingValue . " " . $challengeSettingTargetUOM . $challengeRulesArray['name'] . ' = 1 Point';

            $targetArray[] = $challengeRulesArray;
        }

        $modelId     = !empty($this->parent_id) ? $this->parent_id : $this->id;
        $mappedGroup = Group::where('model_name', 'challenge')
            ->where('model_id', $modelId)
            ->where('is_visible', 1)
            ->where('is_archived', 0)
            ->first();

        $memberImages = $this->getMemberImages($this, $isStarted, $isCompleted);
        // Map challenge Code
        $mapProperty     = [];
        $mapChalengeFlag = false;
        if ($this->map_id != null) {
            $mapChallenge    = $this->mapLibrary()->first();
            $mapProperty     = $mapChallenge->mapProperties()->get();
            $mapChalengeFlag = true;
        }
        $return = [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'description'           => (!empty($this->description)) ? $this->description : "",
            'ruleText'              => $ruleText,
            'image'                 => $this->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'startDateTime'         => Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'endDateTime'           => Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'members'               => (!empty($members)) ? $members : 0,
            'isMember'              => (!empty($loginUserData) && $loginUserData->pivot->status == 'Accepted') ? true : false,
            'isStarted'             => $isStarted,
            'isCompleted'           => $isCompleted,
            'isOpen'                => (!$this->close) ? true : false,
            'timerData'             => $timerData,
            'creator'               => $this->getCreatorData(),
            'badges'                => ($badgeData->count() > 0) ? new ChallengeBadgeCollection($badgeData) : [],
            'cancelled'             => ($this->cancelled) ? true : false,
            'invitationStatus'      => (!empty($loginUserData)) ? $loginUserData->pivot->status : "",
            'type'                  => $this->challenge_type,
            'challengeCategoryName' => (!empty($this->challengeCatName)) ? $this->challengeCatName : "",
            'targets'               => $targetArray,
            'memberImages'          => $memberImages,
            'isMapChallenge'        => $mapChalengeFlag,
            'mapLocations'          => $this->when(!empty($mapChallenge), [
                'totalSteps'    => (!empty($mapChallenge)) ? (int) $mapChallenge->total_steps : 0,
                'totalDistance' => (!empty($mapChallenge)) ? (int) $mapChallenge->total_distance * 1000 : 0,
                'locations'     => (!empty($mapProperty)) ? new ChallengeMapCollection($mapProperty) : [],
            ]),
        ];

        if (isset($mappedGroup)) {
            $isMember = $mappedGroup->members()
                ->wherePivot('group_id', $mappedGroup->getKey())
                ->wherePivot('user_id', $user->getKey())
                ->first();

            $isReported = $mappedGroup->groupReports()
                ->wherePivot('group_id', $mappedGroup->getKey())
                ->wherePivot('user_id', $user->getKey())
                ->first();

            if (!$isReported) {
                $return['groupInfo'] = [
                    'groupId'  => $mappedGroup->id,
                    'isMember' => !empty($isMember),
                ];
            }
        }

        return $return;
    }

    /**
     * Get Member images depand on challenge type and challenge started or ongoing status
     *
     * @param  $challenge
     * @return array
     */
    private function getMemberImages($challenge, $isStarted, $isCompleted)
    {
        $memberImages = [];
        if ($challenge->challenge_type == 'individual') {
            if (!$isStarted && !$isCompleted) {
                //Get image of members
                $participants = $challenge->members()->wherePivot("status", "Accepted")->get();
                if (!empty($participants)) {
                    foreach ($participants as $key => $value) {
                        if (!empty($value)) {
                            $participantImage = $value->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1, 'mI' => 1]);
                            array_push($memberImages, $participantImage);
                        }
                    }
                }
            } else {
                $topScorer = $challenge->challengeWiseUserPoints()
                    ->with(['user'])->where('challenge_wise_user_ponits.challenge_id', $challenge->id)->orderby('points', 'Desc')->get();
                if (!empty($topScorer)) {
                    foreach ($topScorer as $key => $value) {
                        $user = $value->user;
                        if (!empty($user)) {
                            $topScrorerImage = $user->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1, 'mI' => 1]);
                            array_push($memberImages, $topScrorerImage);
                        }
                    }
                }
            }
        } elseif ($challenge->challenge_type == 'team' || $challenge->challenge_type == 'company_goal') {
            if (!$isStarted && !$isCompleted) {
                //Get image of teams
                $participantTeams = $challenge->memberTeams()->get();
                if (!empty($participantTeams)) {
                    foreach ($participantTeams as $key => $value) {
                        if (!empty($value)) {
                            $participantTeamImage = $value->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                            array_push($memberImages, $participantTeamImage);
                        }
                    }
                }
            } else {
                $topScorer = $challenge->challengeWiseTeamPoints()
                    ->with(['team'])->where('challenge_wise_team_ponits.challenge_id', $challenge->id)->orderby('points', 'Desc')->get();
                if (!empty($topScorer)) {
                    foreach ($topScorer as $key => $value) {
                        $team = $value->team;
                        if (!empty($team)) {
                            $topScrorerImage = $team->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                            array_push($memberImages, $topScrorerImage);
                        }
                    }
                }
            }
        } elseif ($challenge->challenge_type == 'inter_company') {
            if (!$isStarted && !$isCompleted) {
                //Get image of teams
                $participantsCompanies = $challenge->memberCompanies()->distinct('company_id')->groupBy('company_id')->get();
                if (!empty($participantsCompanies)) {
                    foreach ($participantsCompanies as $key => $value) {
                        if (!empty($value)) {
                            $participantsCompanyImage = $value->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                            array_push($memberImages, $participantsCompanyImage);
                        }
                    }
                }
            } else {
                $topScorer = $challenge->challengeWiseCompanyPoints()
                    ->with(['company'])->where('challenge_wise_company_points.challenge_id', $challenge->id)->orderBy('points', 'Desc')->get();
                if (!empty($topScorer)) {
                    foreach ($topScorer as $key => $value) {
                        $company = $value->company;
                        if (!empty($company)) {
                            $topScrorerImage = $company->getMediaData('logo', ['w' => 60, 'h' => 60, 'zc' => 3, 'ct' => 1]);
                            array_push($memberImages, $topScrorerImage);
                        }
                    }
                }
            }
        }
        $topFourMembers = array_slice($memberImages, 0, 4);
        return $topFourMembers;
    }
}

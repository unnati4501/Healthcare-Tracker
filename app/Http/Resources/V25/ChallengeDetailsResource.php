<?php

namespace App\Http\Resources\V25;

use App\Http\Collections\V25\ChallengeBadgeCollection;
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

        // Get challenge Badge Records with user achivement count
        $badgeData = Badge::select(
            'badges.id',
            'badges.title',
            'badges.type',
            'badges.description',
            DB::raw("(SELECT id FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND model_id = " . $this->id . " AND STATUS = 'Active') AS badgeUserId"),
            DB::raw("(SELECT count(id) FROM badge_user WHERE badge_id = badges.id AND user_id = " . $user->id . " AND model_id = " . $this->id . " AND STATUS = 'Active') AS assignCount")
        )->leftJoin('challenge_ongoing_badges', 'challenge_ongoing_badges.badge_id', '=', 'badges.id')
            ->where(function ($query) use ($challengeType) {
                $query->where('badges.challenge_type_slug', $challengeType)
                    ->where('badges.is_default', 1)
                    ->where('badges.type', 'challenge');
            })
            ->orWhere('challenge_ongoing_badges.challenge_id', $this->id)
            ->get();

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

        if ($currentDateTime < $startDate) {
            $timerData = calculatDayHrMin($currentDateTime, $startDate);
        } else {
            $timerData = calculatDayHrMin($currentDateTime, $endDate);
        }

        unset($timerData['hour']);
        unset($timerData['minute']);
        $timerData['day'] = $timerData['day'] + 1;

        $challengeRulesData = $this->challengeRules()->join('challenge_targets', 'challenge_targets.id', '=', 'challenge_rules.challenge_target_id')->select('challenge_rules.*', 'challenge_targets.short_name', 'challenge_targets.name')->get();

        $ruleText = "";

        if (!empty($this->challenge_category_id) && !empty($challenge_rule_description[$this->challenge_type]) && $challenge_rule_description[$this->challenge_type][$this->challenge_category_id]) {
            // $description .= "<br/><br/>Rule : " . $challenge_rule_description[$this->challenge_type][$this->challenge_category_id];
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
}

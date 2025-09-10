<?php

namespace App\Http\Resources\V26;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Badge;
use App\Models\Challenge;
use App\Models\Course;
use App\Models\UserGoal;
use App\Models\PersonalChallenge;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeDetailResource extends JsonResource
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
        $user    = $this->user();
        $company = $user->company()->select('companies.id')->first();

        $title           = $this->title;
        $challengeName   = '';
        $masterclassName = '';

        if (!empty($this->badgeModelId) && !empty($this->badgeModelName) && $this->type == 'challenge') {
            if ($this->badgeModelName == 'challenge') {
                $model = Challenge::find($this->badgeModelId);
            } elseif ($this->badgeModelName == 'personal_challenge') {
                $model = PersonalChallenge::find($this->badgeModelId);
            }

            if (!empty($model)) {
                $title .= " (" . $model->title . ")";
                $challengeName = $model->title;
            }
        }

        if (!empty($this->modelId) && !empty($this->modelName) && $this->type == 'masterclass') {
            if ($this->modelName == 'masterclass') {
                $model = Course::find($this->modelId);
            }

            if (!empty($model)) {
                $masterclassName = $model->title;
            }
        }

        if ($this->type == 'masterclass') {
            $defaultMasterclass = Badge::where('type', 'masterclass')->where('is_default', true)->first();
            $image              = $defaultMasterclass->getMediaData('logo', ['w' => 320, 'h' => 320]);
        } else {
            $image = $this->getMediaData('logo', ['w' => 320, 'h' => 320]);
        }

        // get all group ids which is joined and created by me
        $hasGroup = $user->myGroups()
            ->where(function ($query) use ($company) {
                $query->where('groups.company_id', $company->id)
                    ->orWhere('groups.company_id', null);
            })
            ->where('groups.is_visible', 1)
            ->where('groups.is_archived', 0)
            ->groupBy('group_members.group_id')
            ->count('groups.id');

        $steps = $this->steps;
        if ($this->steps == 0 || $this->steps == null) {
            $userDailySteps = UserGoal::select('steps')->where('user_id', $user->id)->first();
            $steps          = config('zevolifesettings.goalSteps');
            if (!empty($userDailySteps)) {
                $steps = $userDailySteps->steps;
            }
        }

        return [
            'id'              => $this->id,
            'userId'          => $this->user_id,
            'title'           => $title,
            'type'            => $this->type,
            'masterclassName' => $this->when($this->type == 'masterclass' && !empty($masterclassName), $masterclassName),
            'challengeName'   => $this->when($this->type == 'challenge' && !empty($challengeName), $challengeName),
            'challengeType'   => $this->when($this->type == 'challenge', ucfirst(str_replace('_', ' ', $this->challenge_type_slug))),
            'description'     => $this->description,
            'badgeUserId'     => $this->badgeUserId,
            'status'          => $this->status,
            'level'           => $this->level,
            'achieverName'    => $this->achieverName,
            'awardedOn'       => Carbon::parse($this->badgeAwardedOn, config('app.timezone'))->setTimezone($user->timezone)->toAtomString(),
            'image'           => $image,
            'hasGroup'        => ($hasGroup > 0),
            'steps'           => $steps,
        ];
    }
}

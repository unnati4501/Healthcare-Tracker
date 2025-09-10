<?php

namespace App\Http\Resources\V27;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ChallengeBadgeResource extends JsonResource
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
        /**
         * @var User
         * $user
         **/
        $image = $this->getMediaData('logo', ['w' => 320, 'h' => 320]);
        if ($this->type == 'challenge') {
            if ($this->assignCount == 1) {
                $badgeMessage = "Congratulations! You have received a '$this->title Badge' in this challenge";
            } else {
                $badgeMessage = "Earn a '$this->title Badge' by winning this challenge.";
            }
        } else {
            $challengeEndDate = Carbon::parse($this->challengeStartDate, config('app.timezone'))->addDays($this->challengeTargetDays)->setTimezone($this->timezone)->format('d/m/Y');
            //$challengeEndDate = Carbon::parse($this->challengeEndDate, config('app.timezone'))->setTimezone($this->timezone)->format('d/m/Y');
            if ($this->assignCount == 1) {
                $badgeMessage = "Congratulations! You have received a '$this->title Badge' in this challenge";
            } else {
                if ($this->targetUnit == strtolower('count')) {
                    $target      = "Steps";
                    $targetCount = $this->targetCount;
                } else {
                    $target      = "Kms";
                    $targetCount = ($this->targetCount)/1000;
                }
                $badgeMessage = "Earn the '$this->title Badge' by completing $targetCount $target till $challengeEndDate";
            }
        }
        
        return [
            'id'                  => $this->id,
            'image'               => $image,
            'name'                => $this->title,
            'description'         => ((!empty($this->description)) ? $this->description : ""),
            'badgeUserId'         => $this->when($this->badgeUserId != null, $this->badgeUserId),
            'achievementCount'    => $this->assignCount,
            'badgeMessage'        => $badgeMessage,
        ];
    }
}

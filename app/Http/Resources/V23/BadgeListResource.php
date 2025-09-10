<?php

namespace App\Http\Resources\V23;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Badge;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeListResource extends JsonResource
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
        $user = $this->user();

        if ($this->type == 'masterclass') {
            $defaultMasterclass = Badge::where('type', 'masterclass')->where('is_default', true)->first();
            $image              = $defaultMasterclass->getMediaData('logo', ['w' => 320, 'h' => 320]);
            $achievementCount   = Badge::leftJoin("badge_user", "badge_user.badge_id", "=", "badges.id")
                ->select(
                    "badges.id"
                )
                ->where("badge_user.status", "Active")
                ->where("badge_user.user_id", $user->id)
                ->where('badges.type', 'masterclass')
                ->whereNull("badge_user.expired_at")
                ->orderBy("badge_user.created_at", "DESC")
                ->count();
        } else {
            $image            = $this->getMediaData('logo', ['w' => 320, 'h' => 320]);
            $achievementCount = $this->badgeusers()->where("badge_user.status", "Active")
                ->where("badge_user.user_id", $user->id)
                ->whereNull("badge_user.expired_at")->count();
        }

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'type'             => $this->type,
            'image'            => $image,
            'achievementCount' => $achievementCount,
        ];
    }
}

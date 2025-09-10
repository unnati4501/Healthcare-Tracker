<?php

namespace App\Http\Resources\V40;

use App\Http\Traits\ProvidesAuthGuardTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterClassLessonResource extends JsonResource
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
        $user        = $this->user();
        $hasMedia    = false;

        $typeArray = array(1 => "AUDIO", 2 => "VIDEO", 3 => "YOUTUBE", 4 => "CONTENT", 5 => "VIMEO");
        $mediaData = array();
        if (!empty($this->type) && $this->type != 4) {
            $mediaData = $this->getLessonMediaData();
            $hasMedia  = true;
        }
        $isNextAllow = true;
    
        if (!$this->auto_progress) {
            $isNextAllow = false;
        } else {
            if ($this->userLessonStatus == "completed") {
                $completedDate = Carbon::parse($this->completed_at, config('app.timezone'))->setTimezone($user->timezone)->toDateString();
                $currentDate   = Carbon::now()->setTimezone($user->timezone)->toDateString();

                if ($completedDate < $currentDate) {
                    $isNextAllow = true;
                } else {
                    $isNextAllow = false;
                }
            } else {
                $isNextAllow = false;
            }
        }
        if ($this->order_priority == 1) {
            $isNextAllow = true;
        }
        if (!empty($this->getFirstMedia('logo'))) {
            $logo = $this->getMediaData('logo', ['w' => 800, 'h' => 800]);
        } else {
            $logo['width']   = config('zevolifesettings.imageConversions.course_lession.logo.width');
            $logo['height']  = config('zevolifesettings.imageConversions.course_lession.logo.height');
            $logo['url']     = config('zevolifesettings.fallback_image_url.course_lession.default');
        }
        return [
            'lessonId'    => $this->id,
            'title'       => $this->title,
            'type'        => $typeArray[$this->type],
            'isCompleted' => (!empty($this->userLessonStatus) && $this->userLessonStatus == "completed"),
            'isRunning'   => (!empty($this->userLessonStatus) && $this->userLessonStatus == "started"),
            'isNextAllow' => $isNextAllow,
            'duration'    => (!empty($this->courseDuration)) ? convertSecondToMinute($this->courseDuration) : 0,
            'content'     => (!empty($this->type) && $this->type == 4) ? $this->description : "",
            'media'       => $this->when($hasMedia, $mediaData),
            'logo'        => $logo,
        ];
    }
}

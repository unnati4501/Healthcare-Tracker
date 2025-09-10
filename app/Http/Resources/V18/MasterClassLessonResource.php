<?php

namespace App\Http\Resources\V18;

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
        $trailerData = array();

        $typeArray = array(1 => "AUDIO", 2 => "VIDEO", 3 => "YOUTUBE", 4 => "CONTENT", 5 => "VIMEO");
        $mediaData = array();
        if (!empty($this->type) && $this->type != 4) {
            $mediaData = $this->getLessonMediaData();
            $hasMedia  = true;
        }
        $isNextAllow = true;

        if (!$this->auto_progress) {
            if (!empty($this->userLessonStatus) && $this->userLessonStatus == "completed") {
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

        return [
            'lessonId'    => $this->id,
            'title'       => $this->title,
            'type'        => $typeArray[$this->type],
            'isCompleted' => (!empty($this->userLessonStatus) && $this->userLessonStatus == "completed") ,
            'isRunning'   => (!empty($this->userLessonStatus) && $this->userLessonStatus == "started") ,
            'isNextAllow' => $isNextAllow,
            'duration'    => (!empty($this->courseDuration)) ? convertSecondToMinute($this->courseDuration) : 0,
            'content'     => (!empty($this->type) && $this->type == 4) ? $this->description : "",
            'media'       => $this->when($hasMedia, $mediaData),
        ];
    }
}

<?php

namespace App\Http\Resources\V2;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\Course;
use DB;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachDetailsByCoachResource extends JsonResource
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
        $user         = $this->user();
        $reviews      = 0;
        $rating       = 0;
        $followers    = 0;
        $studentCount = 0;

        $coachLogData = DB::table("user_coach_log")
            ->where("coach_id", $this->id)
            ->select(DB::raw("AVG(NULLIF(ratings ,0)) as Avgratings"), DB::raw("COUNT(NULLIF(ratings ,0)) as Totalreview"), DB::raw("COUNT(NULLIF(followed ,0)) as Totalfollower"))
            ->first();

        if (!empty($coachLogData)) {
            $reviews   = (!empty($coachLogData->Totalreview)) ? (int) $coachLogData->Totalreview : 0;
            $rating    = (!empty($coachLogData->Avgratings)) ? round($coachLogData->Avgratings) : 0;
            $followers = (!empty($coachLogData->Totalfollower)) ? (int) $coachLogData->Totalfollower : 0;
        }

        $courseData = Course::where("creator_id", $this->id)->where('status', true);
        $courseIds  = $courseData->pluck("id")->toArray();

        if (!empty($courseIds)) {
            $userCourseCount = DB::table("user_course")
                ->whereIn("course_id", $courseIds)
                ->where("joined", true)
                ->distinct('user_id')
                ->count('user_id');

            $rendomCourseCount = $courseData->select(DB::raw("SUM(random_students) as random_students"))->groupBy("creator_id")->first();

            $studentCount = (int) ($userCourseCount + $rendomCourseCount['random_students']);
        }
        $studentFollowing = DB::table("user_coach_log")
            ->where("coach_id", $this->id)
            ->where("user_id", $user->id)
            ->first();

        return [
            'id'          => $this->id,
            'name'        => $this->full_name,
            'description' => (!empty($this->profile->about)) ? $this->profile->about : "",
            'image'       => $this->getMediaData('logo', ['w' => 640, 'h' => 320]),
            'coverImage'  => $this->getMediaData('coverImage', ['w' => 1280, 'h' => 640]),
            'reviews'     => $reviews,
            'rating'      => $rating,
            'followers'   => $followers,
            'courses'     => $courseData->count(),
            'students'    => $studentCount,
            'isFollowing' => (!empty($studentFollowing) && $studentFollowing->followed) ? true : false,
            'isReviewed'  => (!empty($studentFollowing) && $studentFollowing->ratings > 0) ? true : false,
        ];
    }
}

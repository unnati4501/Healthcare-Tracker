<?php

namespace App\Http\Resources\V1;

use App\Http\Collections\V1\CourseReviewCollection;
use App\Http\Traits\ProvidesAuthGuardTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseRatingsResource extends JsonResource
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

        $ratingCalculation = $this->courseRatingsCalculation();

        $reviewsData = $this->courseUserLogs()->wherePivot("ratings", ">", 0)->orderBy("user_course.updated_at", "DESC")->limit(10)->get();

        $userReviewed = $this->courseUserLogs()->wherePivot("ratings", ">", 0)->wherePivot("user_id", $user->getKey())->first();

        return [
            'averageRating'    => (!empty($ratingCalculation) && !empty($ratingCalculation->Avgratings)) ? round($ratingCalculation->Avgratings) : 0,
            'totalUserRatings' => (!empty($ratingCalculation)) ? $ratingCalculation->totalUserRatings : 0,
            'five'             => (!empty($ratingCalculation)) ? (int) $ratingCalculation->five : 0,
            'four'             => (!empty($ratingCalculation)) ? (int) $ratingCalculation->four : 0,
            'three'            => (!empty($ratingCalculation)) ? (int) $ratingCalculation->three : 0,
            'two'              => (!empty($ratingCalculation)) ? (int) $ratingCalculation->two : 0,
            'one'              => (!empty($ratingCalculation)) ? (int) $ratingCalculation->one : 0,
            'isReviewed'       => (!empty($userReviewed)) ,
            'reviews'          => new CourseReviewCollection($reviewsData),
        ];
    }
}

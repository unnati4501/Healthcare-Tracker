<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Collections\V6\InspireHistoryCollection;
use App\Http\Controllers\API\V1\InspireController as v1InspireController;
use App\Http\Requests\Api\V1\GetInspireHistoryRequest;
use App\Http\Requests\Api\V1\GetInspireStatisticsRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InspireController extends v1InspireController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * variable to store the model object
     * @var category
     */
    protected $category;

    /**
     * contructor to initialize model object
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get inspire data of auth user.
     *
     * @param none
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInspireData()
    {
        try {
            // logged-in user
            $user = $this->user();

            $totalMeditation = $user->completedMeditationTracks()
                ->count();

            $completedCourses = $user->courseLogs()
                ->where('user_course.completed', 1)
                ->count();

            $enrolledCourses = $user->courseLogs()
                ->where('user_course.completed', 0)
                ->where('user_course.joined', 1)
                ->count();

            $data = [
                'data' => [
                    'totalMeditation'        => $totalMeditation,
                    'completedMasterClasses' => $completedCourses,
                    'enrolledMasterClasses'  => $enrolledCourses,
                ],
            ];

            return $this->successResponse($data, 'Inspire statistics retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get inspire statistics based on category id.
     *
     * @param GetInspireStatisticsRequest $request, $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInspireStatistics(GetInspireStatisticsRequest $request, $id)
    {
        try {
            // logged-in user
            $user = $this->user();

            $category = $this->category->find($id);

            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $data = [];

            if ($request->duration == 'weekly') {
                $totalWeeks = getWeeks($request->year, $timezone);
                foreach ($totalWeeks as $week => $dates) {
                    if ($category->short_name == 'meditation') {
                        $count = $user->completedMeditationTracks()
                            ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                            ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                            ->count();
                    } else {
                        $count = $user->courseLessonLogs()
                            ->leftJoin('courses', 'user_lession.course_id', '=', 'courses.id')
                            ->where('courses.category_id', $id)
                            ->where('user_lession.status', 'completed')
                            ->where(\DB::raw("DATE(CONVERT_TZ(user_lession.completed_at, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                            ->where(\DB::raw("DATE(CONVERT_TZ(user_lession.completed_at, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                            ->count();
                    }

                    $weekData          = [];
                    $weekData['key']   = $week;
                    $weekData['value'] = (int) $count;

                    array_push($data, $weekData);
                }
            } elseif ($request->duration == 'monthly') {
                $monthArr = getMonths($request->year);
                foreach ($monthArr as $month => $monthName) {
                    if ($category->short_name == 'meditation') {
                        $count = $user->completedMeditationTracks()
                            ->where(\DB::raw("MONTH(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                            ->where(\DB::raw("YEAR(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                            ->count();
                    } else {
                        $count = $user->courseLessonLogs()
                            ->leftJoin('courses', 'user_lession.course_id', '=', 'courses.id')
                            ->where('courses.category_id', $id)
                            ->where('user_lession.status', 'completed')
                            ->where(\DB::raw("MONTH(CONVERT_TZ(user_lession.completed_at, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                            ->where(\DB::raw("YEAR(CONVERT_TZ(user_lession.completed_at, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                            ->count();
                    }

                    $monthData          = [];
                    $monthData['key']   = ucfirst($monthName);
                    $monthData['value'] = (int) $count;

                    array_push($data, $monthData);
                }
            } elseif ($request->duration == 'daily') {
                $datesArr = getDates($request->year, $timezone);
                foreach ($datesArr as $key => $date) {
                    if ($category->short_name == 'meditation') {
                        $count = $user->completedMeditationTracks()
                            ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$user->timezone}'))"), '=', $date)
                            ->count();
                    } else {
                        $count = $user->courseLessonLogs()
                            ->leftJoin('courses', 'user_lession.course_id', '=', 'courses.id')
                            ->where('courses.category_id', $id)
                            ->where('user_lession.status', 'completed')
                            ->where(\DB::raw("DATE(CONVERT_TZ(user_lession.completed_at, '{$appTimezone}', '{$user->timezone}'))"), '=', $date)
                            ->count();
                    }

                    $dateData          = [];
                    $dateData['key']   = $key;
                    $dateData['value'] = (int) $count;

                    array_push($data, $dateData);
                }
            }

            $resultData = [
                'data' => $data,
            ];

            return $this->successResponse($resultData, 'Inspire statistics retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get inspire history based on category id.
     *
     * @param GetInspireHistoryRequest $request, $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInspireHistory(GetInspireHistoryRequest $request, $id)
    {
        try {
            // logged-in user
            $user = $this->user();

            $category = $this->category->find($id);

            if (empty($category)) {
                return $this->notFoundResponse("Sorry! Requested data not found");
            }

            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            if ($category->short_name == 'meditation') {
                $categoryData = $user->completedMeditationTracks()
                    ->orderByDesc('user_listened_tracks.created_at')
                    ->orderByDesc('user_listened_tracks.id')
                // ->distinct('user_listened_tracks.meditation_track_id')
                    ->limit(7)
                    ->get();
            } else {
                $categoryData = $user->courseLogs()
                    ->orderByDesc('user_course.completed_on')
                    ->orderByDesc('user_course.id')
                    ->wherePivot('completed', 1)
                    ->where('courses.category_id', $id)
                    ->limit(7)
                    ->get();
            }

            if ($categoryData->isEmpty()) {
                return $this->successResponse(['data' => []], 'No results');
            } else {
                $data = new InspireHistoryCollection($categoryData);
            }

            return $this->successResponse($data, 'Inspire statistics retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

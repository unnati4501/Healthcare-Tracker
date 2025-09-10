<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V43;

use App\Http\Collections\V6\InspireHistoryCollection;
use App\Http\Controllers\API\V6\InspireController as v6InspireController;
use App\Http\Requests\Api\V1\GetInspireHistoryRequest;
use App\Http\Requests\Api\V1\GetInspireStatisticsRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InspireController extends v6InspireController
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
                            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) >= ?",[
                                $appTimezone,$user->timezone,$dates['week_start']
                            ])
                            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) <= ? ",[
                                $appTimezone,$user->timezone,$dates['week_end']
                            ])
                            ->count();
                    } else {
                        $count = $user->courseLessonLogs()
                            ->leftJoin('courses', 'user_lession.course_id', '=', 'courses.id')
                            ->where('courses.category_id', $id)
                            ->where('user_lession.status', 'completed')
                            ->whereRaw("DATE(CONVERT_TZ(user_lession.completed_at, ?, ?)) >= ?",[
                                $appTimezone,$user->timezone,$dates['week_start']
                            ])
                            ->whereRaw("DATE(CONVERT_TZ(user_lession.completed_at, ?, ?)) <= ?",[
                                $appTimezone,$user->timezone,$dates['week_end']
                            ])
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
                            ->whereRaw("MONTH(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) = ?",[
                                $appTimezone,$user->timezone,$month
                            ])
                            ->whereRaw("YEAR(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) = ?",[
                                $appTimezone,$user->timezone,$request->year
                            ])
                            ->count();
                    } else {
                        $count = $user->courseLessonLogs()
                            ->leftJoin('courses', 'user_lession.course_id', '=', 'courses.id')
                            ->where('courses.category_id', $id)
                            ->where('user_lession.status', 'completed')
                            ->whereRaw("MONTH(CONVERT_TZ(user_lession.completed_at, ?, ?)) = ?",[
                                $appTimezone,$user->timezone,$month
                            ])
                            ->whereRaw("YEAR(CONVERT_TZ(user_lession.completed_at, ?, ?)) = ?",[
                                $appTimezone,$user->timezone,$request->year
                            ])
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
                            ->whereRaw("DATE(CONVERT_TZ(user_listened_tracks.created_at, ?, ?)) = ?",[
                                $appTimezone,$user->timezone,$date
                            ])
                            ->count();
                    } else {
                        $count = $user->courseLessonLogs()
                            ->leftJoin('courses', 'user_lession.course_id', '=', 'courses.id')
                            ->where('courses.category_id', $id)
                            ->where('user_lession.status', 'completed')
                            ->whereRaw("DATE(CONVERT_TZ(user_lession.completed_at, ?, ?)) = ?",[
                                $appTimezone,$user->timezone,$date
                            ])
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
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSteps(GetStepRequest $request)
    {
        try {
            // logged-in user
            $user = $this->user();
            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;

            $data = [];

            if ($request->duration == 'weekly') {
                $totalWeeks = getWeeks($request->year, $timezone);
                foreach ($totalWeeks as $week => $dates) {
                    $count = $user->steps()
                        ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) >= ?",[
                            $appTimezone,$user->timezone,$dates['week_start']
                        ])
                        ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) <= ?",[
                            $appTimezone,$user->timezone,$dates['week_end']
                        ])
                        ->sum($request->type);

                    $weekData          = [];
                    $weekData['key']   = $week;
                    $weekData['value'] = (int) $count;

                    array_push($data, $weekData);
                }
            } elseif ($request->duration == 'monthly') {
                $monthArr = getMonths($request->year);
                foreach ($monthArr as $month => $monthName) {
                    $count = $user->steps()
                        ->whereRaw("MONTH(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                            $appTimezone,$user->timezone,$month
                        ])
                        ->whereRaw("YEAR(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                            $appTimezone,$user->timezone,$request->year
                        ])
                        ->sum($request->type);

                    $monthData          = [];
                    $monthData['key']   = ucfirst($monthName);
                    $monthData['value'] = (int) $count;

                    array_push($data, $monthData);
                }
            } elseif ($request->duration == 'daily') {
                $datesArr = getDates($request->year, $timezone);
                foreach ($datesArr as $key => $date) {
                    $count = $user->steps()
                        ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                            $appTimezone,$user->timezone,$date
                        ])
                        ->sum($request->type);

                    $dateData          = [];
                    $dateData['key']   = $key;
                    $dateData['value'] = (int) $count;

                    array_push($data, $dateData);
                }
            }

            $extraData          = [];
            $extraData['total'] = (int) $user->steps()
                ->whereRaw("DATE(CONVERT_TZ(user_step.log_date, ?, ?)) = ?",[
                    $appTimezone,$user->timezone,now($user->timezone)->toDateString()
                ])
                ->sum($request->type);

            $distanceGoal = 0;
            if (!empty($user->goal)) {
                $distanceGoal = (($user->goal->steps * 1000) / 1400);
            }

            $extraData['goal'] = ($request->type == 'steps' && !empty($user->goal)) ? $user->goal->steps : round($distanceGoal);

            return $this->successResponse(array_merge(['data' => $data], $extraData), ucfirst($request->type) . ' data retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
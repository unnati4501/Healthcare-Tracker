<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V31;

use App\Http\Collections\V6\CategoryWiseMasterClassCollection as v6CategoryWiseMasterClassCollection;
use App\Http\Collections\V30\RecentMasterClassCollection;
use App\Http\Collections\V31\CategoryWiseMasterClassCollection;
use App\Http\Controllers\API\V30\MasterClassController as v30MasterClassController;
use App\Http\Resources\V31\MasterClassDetailsResource;
use App\Models\Course;
use App\Models\SubCategory;
use DB;
use Illuminate\Http\Request;

class MasterClassController extends v30MasterClassController
{
    /**
     * Get recent masterclass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentMasterClasses(Request $request)
    {
        try {
            $user                      = $this->user();
            $company                   = $user->company()->first();
            $team                      = $user->teams()->first();
            $timezone                  = $user->timezone ?? config('app.timezone');
            $data['recentMasterClass'] = $this->getRecentMasertClassList();
            $data['mostEnrolled']      = $this->getMostEnrolledMasterClassList();
            $data['mostLiked']         = Course::select(
                'courses.*',
                "sub_categories.name as courseSubCategory",
                DB::raw('IFNULL(sum(user_course.liked),0) AS most_liked')
            )
                ->join('masterclass_team', function ($join) use ($team) {
                    $join->on('courses.id', '=', 'masterclass_team.masterclass_id')
                        ->where('masterclass_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('user_course', 'user_course.course_id', '=', 'courses.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'courses.sub_category_id');
                })
                ->where("courses.status", true)
                ->orderBy('most_liked', 'DESC')
                ->groupBy('courses.id')
                ->having('most_liked', '>', '0')
                ->limit(10)
                ->get();
            //->shuffle();
            // Collect required data and return response
            return $this->successResponse(new RecentMasterClassCollection($data), 'recent masterclass listed successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recent masterclass [Most Listened, Most Watched, Latest Articles]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function getRecentMasertClassList($type = "")
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $records = Course::select(
                'courses.*',
                "sub_categories.name as courseSubCategory",
                //DB::raw('IFNULL(sum(user_course.view_count),0) AS view_count'),
                DB::raw('IFNULL(sum(user_course.liked),0) AS most_liked')
            )
                ->join('masterclass_team', function ($join) use ($team) {
                    $join->on('courses.id', '=', 'masterclass_team.masterclass_id')
                        ->where('masterclass_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('user_course', 'user_course.course_id', '=', 'courses.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'courses.sub_category_id');
                })
                ->where("courses.status", true);

            $records->orderBy('courses.updated_at', 'DESC');
            $records = $records->groupBy('courses.id')
                ->limit(10)
                ->get()
                ->shuffle();

            return $records;
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get enrolled masterclass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function getMostEnrolledMasterClassList($type = "")
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');

            $records = Course::select(
                'courses.*',
                "sub_categories.name as courseSubCategory",
                //DB::raw('IFNULL(sum(user_course.view_count),0) AS view_count'),
                DB::raw('IFNULL(sum(user_course.liked),0) AS most_liked')
            )
                ->join('masterclass_team', function ($join) use ($team) {
                    $join->on('courses.id', '=', 'masterclass_team.masterclass_id')
                        ->where('masterclass_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('user_course', 'user_course.course_id', '=', 'courses.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'courses.sub_category_id');
                })
                ->where("courses.status", true)
                ->where("user_course.joined", true)
                ->where("user_course.completed", false);

            $records->orderBy('courses.created_at', 'DESC');
            $records = $records->groupBy('courses.id')
                ->limit(10)
                ->get()
                ->shuffle();

            return $records;
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch masterclasses by category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryMasterClass(Request $request, $subcategory)
    {
        try {
            if ($subcategory > 0) {
                $subcategoryRecords = SubCategory::find($subcategory);

                if (empty($subcategoryRecords)) {
                    return $this->notFoundResponse("Sorry! Requested data not found");
                }
            }

            $user      = $this->user();
            $company   = $user->company()->first();
            $team      = $user->teams()->first();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            $categoryWiseCourseData = Course::join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            });

            if ($subcategory > 0) {
                $categoryWiseCourseData->where("sub_category_id", $subcategoryRecords->id);
            } elseif ($subcategory == 0) {
                $categoryWiseCourseData->join('user_course', 'courses.id', '=', 'user_course.course_id')
                    ->join('sub_categories', 'sub_categories.id', '=', 'courses.sub_category_id')
                    ->where("user_course.user_id", $user->id)
                    ->where(["favourited" => 1, "sub_categories.status" => 1])
                    ->orderByRaw("`courses`.`updated_at` DESC");
            }

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $categoryWiseCourseData->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id", DB::raw("(SELECT id FROM user_course WHERE course_id = courses.id and user_id = " . $user->id . " and joined = 1 limit 1) as isEnrolled"))
                    ->where("courses.status", true);
            } else {
                $categoryWiseCourseData->select("courses.id", "courses.title", "courses.instructions", "courses.creator_id");
            }

            $categoryWiseCourseData->addSelect(DB::raw("(SELECT count(id) FROM user_course WHERE course_id = courses.id) AS totalEnrolled"));
            $categoryWiseCourseData->where("courses.status", true);
            if ($subcategory > 0) {
                $categoryWiseCourseData->orderBy('courses.created_at', 'DESC');
            } else {
                $categoryWiseCourseData->orderByRaw("`totalEnrolled` DESC, `courses`.`created_at` DESC");
            }
            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                return $this->successResponse(new CategoryWiseMasterClassCollection($categoryWiseCourseData), 'Master class retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch enrolled masterclass of logged in user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function enrolledMasterClass(Request $request)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();
            $team    = $user->teams()->first();
            // get paginated course data by category
            $categoryWiseCourseData = Course::leftJoin('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            })
                ->select("courses.id", "courses.title", "courses.creator_id")
                ->where("courses.status", true)
                ->where("user_course.joined", true)
                ->where("user_course.completed", false)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            $totalEnrolledMasterclasses = Course::join('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            })->select(DB::raw('COUNT(DISTINCT courses.id) as counts'))
                ->where('user_course.joined', true)
                ->first()
                ->toArray();

            $return                               = [];
            $return['totalEnrolledMasterclasses'] = (int) $totalEnrolledMasterclasses['counts'];
            $return['data']                       = [];

            if ($categoryWiseCourseData->count() > 0) {
                $return = new CategoryWiseMasterClassCollection($categoryWiseCourseData, $totalEnrolledMasterclasses);
            }

            // return response
            return $this->successResponse(
                $return,
                ($categoryWiseCourseData->count() > 0) ? 'Master class retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch completed masterclass of logged in user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completedMasterClass(Request $request)
    {
        try {
            // logged-in user
            $user    = $this->user();
            $company = $user->company()->first();
            $team    = $user->teams()->first();
            // get paginated course data by category
            $categoryWiseCourseData = Course::leftJoin('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            })
                ->select("courses.id", "courses.title", "courses.creator_id")
                ->where("courses.status", true)
                ->where("user_course.completed", true)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData
                ->groupBy('courses.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            $totalEnrolledMasterclasses = Course::join('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })->join('masterclass_team', function ($join) use ($team) {
                $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                    ->where('masterclass_team.team_id', $team->id);
            })->select(DB::raw('COUNT(DISTINCT courses.id) as counts'))
                ->where('user_course.joined', true)
                ->first()
                ->toArray();

            $return                               = [];
            $return['totalEnrolledMasterclasses'] = (int) $totalEnrolledMasterclasses['counts'];
            $return['data']                       = [];

            if ($categoryWiseCourseData->count() > 0) {
                $return = new CategoryWiseMasterClassCollection($categoryWiseCourseData, $totalEnrolledMasterclasses);
            }

            // return response
            return $this->successResponse(
                $return,
                ($categoryWiseCourseData->count() > 0) ? 'Master class retrieved successfully.' : "No results"
            );
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch saved masterclasses
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function savedMasterClass(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();
            $team    = $user->teams()->first();

            $categoryWiseCourseData = Course::select("courses.id", "courses.title", "courses.creator_id")
                ->leftJoin('user_course', function ($join) use ($user) {
                    $join->on('courses.id', '=', 'user_course.course_id')
                        ->where('user_course.user_id', '=', $user->getKey());
                })
                ->join('masterclass_team', function ($join) use ($team) {
                    $join->on('masterclass_team.masterclass_id', '=', 'courses.id')
                        ->where('masterclass_team.team_id', $team->id);
                })
                ->where("courses.status", true)
                ->where("user_course.saved", true)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                return $this->successResponse(new v6CategoryWiseMasterClassCollection($categoryWiseCourseData), 'Master class retrieved successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * To get details of the masterclass
     *
     * @param Request $request
     * @param Course $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function masterClassDetail(Request $request, Course $course)
    {
        try {
            if (!$course->status) {
                return $this->notFoundResponse('Masterclass is not published yet.');
            }

            // get logged-in users company
            $company = $this->user()->company()->select('companies.id')->first();
            $team    = $this->user()->teams()->first();
            // check masterclass is available for logged-in user's company
            $visibleForCompany = $course->masterclassteam()
                ->where('team_id', $team->id)
                ->count('masterclass_team.id');
            if ($visibleForCompany == 0) {
                return $this->notFoundResponse("Masterclass isn't published for your company or has been removed.");
            }

            // send masterclass details success response
            return $this->successResponse([
                'data' => new MasterClassDetailsResource($course),
            ], 'Masterclass details retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong_try_again'));
        }
    }

    /**
     * API to favorited unfavourited Mastercalss
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function favouriteUnfavouriteMasterclass(Request $request, Course $course)
    {
        try {
            \DB::beginTransaction();
            $user           = $this->user();
            $message        = trans('api_messages.course.favorited');
            $pivotExsisting = $course
                ->courseUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('course_id', $course->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $favourited                           = $pivotExsisting->pivot->favourited;
                $pivotExsisting->pivot->favourited    = ($favourited == 1) ? 0 : 1;
                $pivotExsisting->pivot->favourited_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();
                if ($favourited == 1) {
                    $message = trans('api_messages.course.unfavorited');
                }
            } else {
                $course
                    ->courseUserLogs()
                    ->attach($user, ['favourited' => true, 'favourited_at' => now()->toDateTimeString()]);
            }

            \DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

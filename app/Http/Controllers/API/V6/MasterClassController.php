<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Collections\V6\CategoryWiseMasterClassCollection;
use App\Http\Collections\V6\MasterClassLessonCollection;
use App\Http\Collections\V6\MasterClassSurveyCollection;
use App\Http\Controllers\Controller;
use App\Http\Resources\V6\MasterClassDetailsResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Jobs\SendGroupPushNotification;
use App\Models\Badge;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLession;
use App\Models\CourseSurvey;
use App\Models\CourseSurveyQuestionAnswers;
use App\Models\CourseSurveyQuestions;
use App\Models\Group;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\UserLession;
use App\Notifications\SystemAutoNotification;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class MasterClassController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * API to fetch course data by given category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryMasterClass(Request $request, SubCategory $subcategory)
    {
        try {
            // logged-in user
            $user = $this->user();

            // get paginated course data by category
            $categoryWiseCourseData = Course::where("sub_category_id", $subcategory->id)
                ->select("courses.id", "courses.title", "courses.creator_id")
                ->where("courses.status", true)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new CategoryWiseMasterClassCollection($categoryWiseCourseData), 'Master class retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to fetch course data by given category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function enrolledMasterClass(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            // get paginated course data by category
            $categoryWiseCourseData = Course::leftJoin('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })
                ->select("courses.id", "courses.title", "courses.creator_id")
                ->where("courses.status", true)
                ->where("user_course.completed", false)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            $totalEnrolledMasterclasses = $user->courseLogs()->wherePivot('joined', true)->count();

            $return                               = [];
            $return['totalEnrolledMasterclasses'] = $totalEnrolledMasterclasses;
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
     * API to fetch course data by given category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completedMasterClass(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            // get paginated course data by category
            $categoryWiseCourseData = Course::leftJoin('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })
                ->select("courses.id", "courses.title", "courses.creator_id")
                ->where("courses.status", true)
                ->where("user_course.completed", true)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            $totalEnrolledMasterclasses = $user->courseLogs()->wherePivot('joined', true)->count();

            $return                               = [];
            $return['totalEnrolledMasterclasses'] = $totalEnrolledMasterclasses;
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
     * API to fetch course data by given category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function savedMasterClass(Request $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            // get paginated course data by category
            $categoryWiseCourseData = Course::leftJoin('user_course', function ($join) use ($user) {
                $join->on('courses.id', '=', 'user_course.course_id')
                    ->where('user_course.user_id', '=', $user->getKey());
            })
                ->select("courses.id", "courses.title", "courses.creator_id")
                ->where("courses.status", true)
                ->where("user_course.saved", true)
                ->orderBy('courses.created_at', 'DESC');

            $categoryWiseCourseData = $categoryWiseCourseData->groupBy('courses.id')->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($categoryWiseCourseData->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new CategoryWiseMasterClassCollection($categoryWiseCourseData), 'Master class retrieved successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to like unlike MasterClass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlikeMasterClass(Request $request, Course $course)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user           = $this->user();
            $message        = trans('api_messages.course.liked');
            $pivotExsisting = $course
                ->courseUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('course_id', $course->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = ($liked == 1) ? 0 : 1;
                $pivotExsisting->pivot->save();

                if ($liked == 1) {
                    $message = trans('api_messages.course.unliked');
                }
            } else {
                $message = trans('api_messages.course.liked');
                $course->courseUserLogs()->attach($user, ['liked' => true]);
            }

            \DB::commit();
            return $this->successResponse(['data' => ['totalLikes' => $course->getTotalLikes()]], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to save unsave MasterClass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveUnsaveMasterClass(Request $request, Course $course)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user    = $this->user();
            $message = trans('api_messages.course.saved');

            // fetch user course data
            $pivotExsisting = $course
                ->courseUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('course_id', $course->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $saved                           = $pivotExsisting->pivot->saved;
                $pivotExsisting->pivot->saved    = ($saved == 1) ? 0 : 1;
                $pivotExsisting->pivot->saved_at = now()->toDateTimeString();
                $pivotExsisting->pivot->save();

                if ($saved == 1) {
                    $message = trans('api_messages.course.unsaved');
                }
            } else {
                $message = trans('api_messages.course.saved');
                $course->courseUserLogs()->attach($user, ['saved' => true, 'saved_at' => now()->toDateTimeString()]);
            }

            \DB::commit();
            return $this->successResponse([], $message);
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to save unsave MasterClass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function surveyMasterClass(Request $request, $type, Course $course)
    {
        try {
            // logged-in user
            $user = $this->user();

            $surveyQuestion = CourseSurveyQuestions::join("course_survey", "course_survey.id", "=", "course_survey_questions.survey_id")
                ->where("course_survey_questions.status", true)
                ->where("course_survey.status", true)
                ->where("course_survey.course_id", $course->id)
                ->where("course_survey.type", $type)
                ->select("course_survey_questions.*", "course_survey.id as surveyId", "course_survey.title as surveyTitle")
                ->get();

            if ($surveyQuestion->count() > 0) {
                $data              = [];
                $data['surveyId']  = $surveyQuestion[0]->surveyId;
                $data['title']     = $surveyQuestion[0]->surveyTitle;
                $data['questions'] = new MasterClassSurveyCollection($surveyQuestion);

                return $this->successResponse(['data' => $data], 'Survey listed successfully.');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to save unsave MasterClass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitMasterClassSurvey(Request $request, CourseSurvey $survey)
    {
        try {
            // logged-in user
            $payload    = $request->all();
            $user       = $this->user();
            $companyId  = !is_null($user->company->first()) ? $user->company->first()->id : null;
            $surveyData = $payload;

            if (empty($surveyData)) {
                return $this->notFoundResponse("Requested data not found");
            }

            $checkSurvyExists = CourseSurveyQuestionAnswers::where("user_id", $user->id)
                ->where("survey_id", $survey->id)
                ->get();

            if ($checkSurvyExists->count() > 0) {
                return $this->notFoundResponse("Survey already submitted.");
            }

            $surveyReponseData = [];

            $surveyReponseData = collect($surveyData)->map(function ($value, $key) use ($survey, $user) {

                $surveyQuestionData       = CourseSurveyQuestions::where('id', $value['questionId'])->first();
                $surveyOptionsReponseData = collect($value['answers'])->map(function ($val, $k) use ($survey, $value, $surveyQuestionData, $user) {

                    return [
                        'user_id'            => $user->getKey(),
                        'course_id'          => $survey->course_id,
                        'survey_id'          => $survey->getKey(),
                        'question_id'        => $value['questionId'],
                        'question_option_id' => $val,
                    ];
                });
                return $surveyOptionsReponseData;
            });

            \DB::beginTransaction();

            $surveyReponseData = \Arr::collapse($surveyReponseData);

            $data = CourseSurveyQuestionAnswers::insert($surveyReponseData);

            // fetch user course data
            $course = Course::find($survey->course_id);

            $pivotExsisting = $course->courseUserLogs()->wherePivot('user_id', $user->getKey())->wherePivot('course_id', $course->getKey())->first();

            if ($survey->type == "pre") {
                if (!empty($pivotExsisting)) {
                    $pivotExsisting->pivot->pre_survey_completed    = 1;
                    $pivotExsisting->pivot->pre_survey_completed_on = now()->toDateTimeString();

                    $pivotExsisting->pivot->joined    = 1;
                    $pivotExsisting->pivot->joined_on = now()->toDateTimeString();

                    $pivotExsisting->pivot->save();
                } else {
                    $course->courseUserLogs()
                        ->attach($user, [
                            'pre_survey_completed'    => true,
                            'pre_survey_completed_on' => now()->toDateTimeString(),
                            'joined'                  => true,
                            'joined_on'               => now()->toDateTimeString(),
                        ]);
                }

                $courseMembers = $course->courseUserLogs()
                    ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                    ->where('user_course.joined', 1)
                    ->where('user_team.company_id', $user->company()->first()->getKey())
                    ->get()
                    ->count();

                if ($courseMembers >= 2) {
                    $groupExists = Group::where('model_id', $course->id)
                        ->where('model_name', 'masterclass')
                        ->first();

                    $members = $course->courseUserLogs()->where('joined', 1)->get()->pluck('id')->toArray();
                    if (!empty($groupExists)) {
                        $membersInput   = [];
                        $membersInput[] = [
                            'user_id'     => 1,
                            'group_id'    => $groupExists->id,
                            'status'      => "Accepted",
                            'joined_date' => now()->toDateTimeString(),
                        ];
                        foreach ($members as $key => $value) {
                            $membersInput[$value] = [
                                'user_id'     => $value,
                                'group_id'    => $groupExists->id,
                                'status'      => "Accepted",
                                'joined_date' => now()->toDateTimeString(),
                            ];
                        }
                        $groupExists->members()->sync($membersInput);
                    } else {
                        $subCategory = SubCategory::where('short_name', 'masterclass')->first();

                        $groupPayload = [
                            'name'             => $course->title,
                            'category'         => $subCategory->id,
                            'introduction'     => $course->instructions,
                            'members_selected' => $members,
                            'model_id'         => $course->id,
                            'model_name'       => 'masterclass',
                            'is_visible'       => 0,
                        ];

                        $groupModel = new Group();
                        $group      = $groupModel->storeEntity($groupPayload);

                        if (!empty($group)) {
                            if (!empty($course->getFirstMediaUrl('logo'))) {
                                $media     = $course->getFirstMedia('logo');
                                $imageData = explode(".", $media->file_name);
                                $name      = $group->id . '_' . \time();
                                $group->clearMediaCollection('logo')
                                    ->addMediaFromUrl(
                                        $course->getFirstMediaUrl('logo'),
                                        $course->getAllowedMediaMimeTypes('logo')
                                    )
                                    ->usingName($media->name)
                                    ->usingFileName($name . '.' . $imageData[1])
                                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
                            }

                            \dispatch(new SendGroupPushNotification($group, "user-assigned-group"));
                        }
                    }
                }
            } elseif ($survey->type == "post") {
                if (!empty($pivotExsisting)) {
                    $pivotExsisting->pivot->post_survey_completed    = 1;
                    $pivotExsisting->pivot->completed                = 1;
                    $pivotExsisting->pivot->post_survey_completed_on = now()->toDateTimeString();
                    $pivotExsisting->pivot->completed_on             = now()->toDateTimeString();

                    $pivotExsisting->pivot->save();
                } else {
                    $course->courseUserLogs()->attach($user, [
                        'post_survey_completed'    => true,
                        'completed'                => true,
                        'post_survey_completed_on' => now()->toDateTimeString(),
                        'completed_on'             => now()->toDateTimeString(),
                    ]);
                }

                $notification_setting = NotificationSetting::select('flag')
                    ->where(['flag' => 1, 'user_id' => $user->getKey()])
                    ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['badges', 'all'])
                    ->first();

                $courseBadge = Badge::where("model_id", $survey->course_id)
                    ->where("model_name", "masterclass")
                    ->where("type", "masterclass")
                    ->first();

                if (!empty($courseBadge)) {
                    $userBadgeData = $user->badges()
                        ->wherePivot("badge_id", $courseBadge->id)
                        ->wherePivot("user_id", $user->id)
                        ->first();

                    if (empty($userBadgeData)) {
                        $badgeInput = [
                            'status' => "Active",
                        ];
                        $courseBadge->badgeusers()->attach($user, $badgeInput);

                        $title   = trans('notifications.badge.course.title');
                        $message = trans('notifications.badge.course.message');
                        $message = str_replace(["#course_name#"], [$course->title], $message);

                        $notification = Notification::create([
                            'type'             => 'Auto',
                            'creator_id'       => $course->creator_id,
                            'company_id'       => $companyId,
                            'creator_timezone' => $user->timezone,
                            'title'            => $title,
                            'message'          => $message,
                            'push'             => ($notification_setting->flag ?? false),
                            'scheduled_at'     => now()->toDateTimeString(),
                            'deep_link_uri'    => 'zevolife://zevo/badges',
                            'tag'              => 'badge',
                        ]);

                        $user->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);

                        if (!empty($notification_setting) && $notification_setting->flag) {
                            // send notification to all users
                            \Notification::send(
                                $user,
                                new SystemAutoNotification($notification, '')
                            );
                        }
                    }
                }
            }

            \DB::commit();

            // collect required data and return response
            return $this->successResponse([], 'Your survey submitted successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to save unsave MasterClass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function masterClassDetail(Request $request, Course $course)
    {
        try {
            if (!$course->status) {
                return $this->notFoundResponse('Masterclass is not published yet.');
            }

            // get course details data with json response
            $data = array("data" => new MasterClassDetailsResource($course));
            return $this->successResponse($data, 'Course Info retrieved successfully');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * API to save unsave MasterClass
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLessonsList(Request $request, Course $course)
    {
        try {
            $user               = $this->user();
            $totalDuration      = $course->courseTotalDurarion();
            $surrentUserLession = $user->courseLessonLogs()
                ->wherePivot('course_id', $course->id)
                ->wherePivot('user_id', $user->id)
                ->wherePivot('status', "started")
                ->orderBy("user_lession.id", "DESC")
                ->first();
            $userMasterClassData = $course->courseUserLogs()->wherePivot("user_id", $user->id)->first();

            $data = array();

            $data['info']['id']              = $course->id;
            $data['info']['title']           = $course->title;
            $data['info']['duration']        = (!empty($totalDuration) && !empty($totalDuration->totalDurarion)) ? convertSecondToMinute($totalDuration->totalDurarion) : 0;
            $data['info']['currentLessonId'] = (!empty($surrentUserLession)) ? $surrentUserLession->id : 0;
            $data['info']['isCompleted']     = (!empty($userMasterClassData) && $userMasterClassData->pivot->completed && $userMasterClassData->pivot->post_survey_completed) ;

            if (!empty($userMasterClassData) && $userMasterClassData->pivot->completed && $userMasterClassData->pivot->post_survey_completed) {
                $lessonNotCompleted = CourseLession::leftJoin('user_lession', function ($join) use ($user) {
                    $join->on('course_lessions.id', '=', 'user_lession.course_lession_id')
                        ->where('user_lession.user_id', '=', $user->getKey());
                })
                    ->select("course_lessions.*", "user_lession.id as userLessionId")
                    ->where("course_lessions.course_id", $course->id)
                    ->where("course_lessions.status", true)
                    ->whereNull("user_lession.id")
                    ->get();
                if ($lessonNotCompleted->count() > 0) {
                    $user->courseLessonLogs()->attach($lessonNotCompleted, [
                        'course_id'    => $course->id,
                        'status'       => "completed",
                        'completed_at' => now()->toDateTimeString(),
                    ]);
                }
            }

            $lessonList = CourseLession::leftJoin('user_lession', function ($join) use ($user) {
                $join->on('course_lessions.id', '=', 'user_lession.course_lession_id')
                    ->where('user_lession.user_id', '=', $user->getKey());
            })
                ->where("course_lessions.course_id", $course->id)
                ->where("course_lessions.status", true)
                ->select("course_lessions.*", "user_lession.status as userLessonStatus", DB::raw("TIME_TO_SEC(course_lessions.duration) as courseDuration"), "user_lession.completed_at")
                ->orderBy("course_lessions.order_priority", "ASC")
                ->orderBy("course_lessions.id", "ASC")
                ->get();

            $data['lessons'] = ($lessonList->count() > 0) ? new MasterClassLessonCollection($lessonList) : [];
            // get course details data with json response
            $data = array("data" => $data);
            return $this->successResponse($data, 'Lesson list retrieved successfully');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    public function mlessonStatusChange(Request $request)
    {
        try {
            $user = $this->user();
            if (empty($request->get("completeLessonId")) && empty($request->get("runningLessonId"))) {
                return $this->notFoundResponse("Invalid Data.");
            }
            $data                = array();
            $data['isNextAllow'] = true;
            $data['isCompleted'] = true;
            $data['isRunning']   = false;

            if (!empty($request->get("completeLessonId"))) {
                $completedLessonExists = UserLession::join("course_lessions", "course_lessions.id", "=", "user_lession.course_lession_id")
                    ->where("user_lession.user_id", $user->getKey())
                    ->where("user_lession.course_lession_id", $request->get("completeLessonId"))
                    ->where("user_lession.status", "started")
                    ->select("user_lession.*", "course_lessions.auto_progress")
                    ->first();

                if (!empty($completedLessonExists)) {
                    $completedLessonExists->status       = "completed";
                    $completedLessonExists->completed_at = now()->toDateTimeString();
                    $completedLessonExists->save();

                    if (!$completedLessonExists->auto_progress && (Carbon::parse($completedLessonExists->completed_at, config('app.timezone'))->setTimezone($user->timezone)->toDateString() >= Carbon::now()->setTimezone($user->timezone)->toDateString())) {
                        $data['isNextAllow'] = false;
                    }
                }
            }

            if (!empty($request->get("runningLessonId"))) {
                $courseLessonExists  = CourseLession::where("id", $request->get("runningLessonId"))->first();
                $startedLessonExists = UserLession::where("user_lession.user_id", $user->getKey())
                    ->where("user_lession.course_lession_id", $request->get("runningLessonId"))
                    ->first();

                if (empty($startedLessonExists) && !empty($courseLessonExists)) {
                    $user->courseLessonLogs()->attach($courseLessonExists, [
                        'course_id' => $courseLessonExists->course_id,
                        'status'    => "started",
                    ]);

                    $startCourseLesson = $user->courseLogs()
                        ->wherePivot('user_id', $user->getKey())
                        ->wherePivot('course_id', $courseLessonExists->course_id)
                        ->first();
                    if (!$startCourseLesson->pivot->started_course) {
                        $startCourseLesson->pivot->started_course = true;
                        $startCourseLesson->pivot->save();
                    }
                }
            }

            $data = array("data" => $data);
            return $this->successResponse($data, 'Status changed successfully');
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

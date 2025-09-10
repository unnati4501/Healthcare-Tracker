<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V7;

use App\Http\Collections\V7\CategoryWiseMasterClassCollection;
use App\Http\Controllers\API\V6\MasterClassController as v6MasterClassController;
use App\Jobs\SendGroupPushNotification;
use App\Models\Badge;
use App\Models\Course;
use App\Models\CourseSurvey;
use App\Models\CourseSurveyQuestionAnswers;
use App\Models\CourseSurveyQuestions;
use App\Models\Group;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\SubCategory;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use DB;
use Illuminate\Http\Request;

class MasterClassController extends v6MasterClassController
{
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
            $data              = CourseSurveyQuestionAnswers::insert($surveyReponseData);

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
                    ->get();

                if ($courseMembers->count() >= 2) {
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

                        $membersId = [];
                        if ($courseMembers->count() == 2) {
                            $membersId = $courseMembers->pluck("id")->toArray();
                        } else {
                            $membersId[] = $user->getKey();
                        }

                        \dispatch(new SendGroupPushNotification($groupExists, "user-assigned-updated-group", "", "", $membersId));
                    } else {
                        $subCategory = SubCategory::where('short_name', 'masterclass')->first();
                        $members     = $course->courseUserLogs()
                            ->leftJoin('user_team', 'user_team.user_id', '=', 'users.id')
                            ->where('user_team.company_id', $user->company()->first()->getKey())
                            ->where('joined', 1)
                            ->get()
                            ->pluck('id')
                            ->toArray();

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
                            'is_mobile'        => config('notification.general_badges.masterclass.is_mobile'),
                            'is_portal'        => config('notification.general_badges.masterclass.is_portal'),
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
     * API to fetch completed masterclass of logged in user
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

            $categoryWiseCourseData = $categoryWiseCourseData
                ->groupBy('courses.id')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

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
}

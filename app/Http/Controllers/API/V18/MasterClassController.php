<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V18;

use App\Http\Controllers\API\V17\MasterClassController as v17MasterClassController;
use App\Http\Requests\Api\V18\CreateMasterclassCsatRequest;
use App\Http\Resources\V18\MasterClassDetailsResource;
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
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class MasterClassController extends v17MasterClassController
{
    /**
     * To store Masterclass CSAT(feedback) response of user
     * @param CreateMasterclassCsatRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitCsat(CreateMasterclassCsatRequest $request)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->select('companies.id')->first();
            $course      = Course::findorFail($request->typeId, ['id']);
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);

            // check masterclass is available for logged-in user's company
            $visibleForCompany = $course->masterclassCompany()
                ->where('company_id', $company->id)
                ->count('masterclass_company.id');
            if ($visibleForCompany == 0) {
                return $this->notFoundResponse("Masterclass isn't published for your company or has been removed.");
            }

            // check does user has completed the masterclass
            $hasCompletedMc = $user->courseLogs()
                ->select('user_course.id', 'user_course.completed_on')
                ->wherePivot('course_id', $course->id)
                ->wherePivot('completed', true)
                ->whereNotNull('user_course.completed_on')
                ->first();
            if (empty($hasCompletedMc)) {
                return $this->notFoundResponse("Please complete the masterclass first in order to submit feedback.");
            }

            // check CSAT is available for user or not
            $feedbackDT = Carbon::parse($hasCompletedMc->completed_on, $appTimezone)
                ->setTimezone($timezone)
                ->addDay()->setTime(14, 0, 0)->toDateTimeString();
            $now = now($timezone)->toDateTimeString();
            if ($now < $feedbackDT) {
                return $this->preConditionsFailedResponse('It seems feedback isn\'t available for the masterclass at this moment.');
            }

            // check user has already submitted CSAT
            $alreadySubmitted = $user->courseCsat()
                ->where('course_id', $course->id)
                ->count('masterclass_csat_user_logs.id');
            if ($alreadySubmitted > 0) {
                return $this->preConditionsFailedResponse('It seems user has already submitted feedback for the masterclass.');
            }

            // store CSAT feedback into database
            $course->csat()->attach($user, [
                'company_id'    => $company->id,
                'feedback'      => (!empty($request->feedback) ? $request->feedback : null),
                'feedback_type' => $request->feedbackType,
            ]);

            // send success response
            return $this->successResponse([], "Thanks, we really appreciate your feedback.");
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong_try_again'));
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

            // check masterclass is available for logged-in user's company
            $visibleForCompany = $course->masterclassCompany()
                ->where('company_id', $company->id)
                ->count('masterclass_company.id');
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
     * To submit pre/post survey of masterclass
     *
     * @param Request $request
     * @param CourseSurvey $survey
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitMasterClassSurvey(Request $request, CourseSurvey $survey)
    {
        try {
            $appTimezone = config('app.timezone');
            $surveyData  = $request->all();
            $user        = $this->user();
            $company     = $user->company->first();
            $companyId   = (!is_null($company) ? $company->id : null);

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
            $surveyReponseData = collect($surveyData)->map(function ($value, $key) use ($survey, $user, $companyId) {
                $surveyQuestionData       = CourseSurveyQuestions::where('id', $value['questionId'])->first();
                $surveyOptionsReponseData = collect($value['answers'])->map(function ($val, $k) use ($survey, $value, $surveyQuestionData, $user, $companyId) {

                    return [
                        'user_id'            => $user->getKey(),
                        'company_id'         => $companyId,
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

                if (!is_null($company)) {
                    $course->rewardPortalPointsToUser($user, $company, 'masterclass');
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
                        $courseBadge->badgeusers()->attach($user, [
                            'status' => "Active",
                        ]);

                        $title   = trans('notifications.badge.course.title');
                        $message = trans('notifications.badge.course.message');
                        $message = str_replace(["#course_name#"], [$course->title], $message);

                        $deepLinkId = \DB::table('badge_user')
                            ->where("badge_id", $courseBadge->id)
                            ->where("user_id", $user->id)
                            ->where("status", 'Active')
                            ->orderBy('id', 'DESC')
                            ->pluck('id')
                            ->first();

                        $notification = Notification::create([
                            'type'             => 'Auto',
                            'creator_id'       => $course->creator_id,
                            'company_id'       => $companyId,
                            'creator_timezone' => $user->timezone,
                            'title'            => $title,
                            'message'          => $message,
                            'push'             => ($notification_setting->flag ?? false),
                            'scheduled_at'     => now()->toDateTimeString(),
                            'deep_link_uri'    => 'zevolife://zevo/badge/' . $deepLinkId,
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

                        // schedule masterclass csat(feedback) notification to next day at 14:00
                        $feedBackNotificationMessage = trans('notifications.masterclass.csat.message', [
                            'first_name'       => $user->first_name,
                            'masterclass_name' => $course->title,
                        ]);
                        $feedBackNotificationsAt = now($user->timezone)->addDay()->setTime(14, 0, 0)
                            ->setTimezone($appTimezone)->toDateTimeString();
                        $feedbackNotificationDeeplink = __(config('zevolifesettings.deeplink_uri.masterclass_csat'), [
                            'id' => $course->id,
                        ]);
                        $feedBackNotification = Notification::create([
                            'type'             => 'Manual',
                            'creator_id'       => $course->creator_id,
                            'creator_timezone' => $user->timezone,
                            'title'            => trans('notifications.masterclass.csat.title'),
                            'message'          => $feedBackNotificationMessage,
                            'push'             => ($notification_setting->flag ?? false),
                            'scheduled_at'     => $feedBackNotificationsAt,
                            'deep_link_uri'    => $feedbackNotificationDeeplink,
                            'tag'              => 'masterclass',
                        ]);

                        if (isset($feedBackNotification)) {
                            $user->notifications()->attach($feedBackNotification, ['sent' => false]);
                        }
                    }
                }
            }

            \DB::commit();

            // collect required data and return response
            return $this->successResponse([], 'Your survey submitted successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

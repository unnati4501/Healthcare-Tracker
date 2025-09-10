<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V31;

use App\Events\SendSessionCancelledEvent;
use App\Http\Collections\V31\AppoitmentSessionListCollection;
use App\Http\Collections\V31\DigitalCounsellorListCollection;
use App\Http\Collections\V31\DigitalTherapyCollection;
use App\Http\Collections\V31\TopicListCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V31\DeleteCronofyScheduleRequest;
use App\Http\Resources\V31\CronofySessionDetailsResource;
use App\Http\Resources\V31\RealTimeSchedulingResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\AppSlide;
use App\Models\CronofySchedule;
use App\Models\ScheduleUsers;
use App\Models\Service;
use App\Models\ServiceSubCategory;
use App\Models\User;
use App\Repositories\CronofyRepository;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class DigitalTherapyController
 */
class DigitalTherapyController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * variable to store the Cronofy Repository Repository object
     * @var CronofyRepository $cronofyRepository
     */
    private $cronofyRepository;

    /**
     * contructor to initialize Repository object
     */
    public function __construct(CronofyRepository $cronofyRepository)
    {
        $this->cronofyRepository = $cronofyRepository;
    }

    /**
     * List all the digital therapy services list.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user           = $this->user();
            $company        = $user->company()->select('companies.id', 'companies.eap_tab')->first();
            $digitalTherapy = $company->digitalTherapy()->first();
            $checkAccess    = getCompanyPlanAccess($user, 'eap');
            if (!$checkAccess && !$company->eap_tab) {
                return $this->notFoundResponse('Digital therapy is disabled for this company.');
            }

            $currentTime   = now(config('app.timezone'))->todatetimeString();
            $paginateLimit = 5;
            $type          = 'eap';
            $slideRecords  = AppSlide::where('type', $type)->orderBy("order_priority", "ASC")->paginate($paginateLimit);
            $serviceList   = Service::join('service_sub_categories', 'service_sub_categories.service_id', '=', 'services.id')
                ->join('digital_therapy_services', 'digital_therapy_services.service_id', '=', 'services.id')
                ->where('digital_therapy_services.company_id', $company->id)
                ->select('services.id', 'services.name')
                ->distinct()
                ->get();

            $isSession = CronofySchedule::leftJoin('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
                ->select('cronofy_schedule.id')
                ->where(function ($q) use ($user) {
                    $q->where('session_group_users.user_id', $user->id);
                })
                ->whereNotIn('cronofy_schedule.status', ['open', 'rescheduled'])
                ->count();

            $data = [
                'allowAppoitment'        => ($isSession > 0) ,
                'allowEmergencyContacts' => (isset($digitalTherapy) && $digitalTherapy->emergency_contacts > 0) ,
                'serviceList'            => $serviceList,
                'sliders'                => $slideRecords,
            ];
            return $this->successResponse(new DigitalTherapyCollection($data), 'Digital therapy get successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * List all the digital therapy topic listing.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topicListing(Request $request, Service $service)
    {
        try {
            $loginUser   = $this->user();
            $company     = $loginUser->company()->select('companies.id', 'companies.eap_tab')->first();
            $checkAccess = getCompanyPlanAccess($loginUser, 'eap');
            $utcNow      = Carbon::now()->setTimezone(config('app.timezone'))->todatetimeString();
            if (!$checkAccess && !$company->eap_tab) {
                return $this->notFoundResponse('Digital therapy is disabled for this company.');
            }

            if ($service->name == 'Counselling') {
                $checkCounsellingSession = CronofySchedule::join('session_group_users', 'session_group_users.session_id', 'cronofy_schedule.id')
                    ->select('cronofy_schedule.id')
                    ->where('session_group_users.user_id', $loginUser->id)
                    ->where('cronofy_schedule.service_id', $service->id)
                    ->whereNotIn('cronofy_schedule.status', ['canceled', 'rescheduled', 'open'])
                    ->where(function ($query) use ($utcNow) {
                        $query->where('cronofy_schedule.end_time', '>=', $utcNow)
                            ->Where('cronofy_schedule.status', 'booked');
                    })
                    ->count();

                if ($checkCounsellingSession >= 1) {
                    return $this->notFoundResponse("You already have an upcoming Counselling appointment. Please complete or cancel it to make another booking.");
                }
            }

            $sessionUser    = config('cronofy.sessionUser');
            $sessionCompany = config('cronofy.sessionCompany');
            $digitalTherapy = $company->digitalTherapy()->first();

            if (!empty($digitalTherapy)) {
                $sessionUser    = $digitalTherapy->dt_max_sessions_user;
                $sessionCompany = $digitalTherapy->dt_max_sessions_company;
            }

            if ($sessionCompany != 0) {
                $companyUsers  = $company->members()->select('users.id')->get()->pluck('id')->toArray();
                $scheduleCount = CronofySchedule::whereIn('user_id', $companyUsers)
                    ->whereNotIn('status', ['canceled', 'rescheduled', 'open'])
                    ->where(function ($query) use ($utcNow) {
                        $query->where('end_time', '<=', $utcNow)
                            ->orWhere('status', 'completed')
                            ->orWhere('status', 'booked');
                    })
                    ->count();

                if ($scheduleCount >= $sessionCompany) {
                    return $this->notFoundResponse("Your company has exceeded the session limit. Please contact your Admin.");
                }
            }

            if ($sessionUser != 0) {
                $scheduleCount = $loginUser->bookedCronofySessions()
                    ->where(function ($query) use ($utcNow) {
                        $query->where('end_time', '<=', $utcNow)
                            ->orWhere('status', 'completed')
                            ->orWhere('status', 'booked');
                    })
                    ->whereNotIn('status', ['canceled', 'rescheduled', 'open'])
                    ->count();

                if ($scheduleCount >= $sessionUser) {
                    return $this->notFoundResponse("You have exceeded the session limit.  Please contact your Admin.");
                }
            }

            if (empty($service)) {
                return $this->notFoundResponse("Sorry! Digital therapy service data not found");
            }

            $getSubServiceId = ServiceSubCategory::join('services', 'services.id', '=', 'service_sub_categories.service_id')
                ->leftJoin('digital_therapy_services', 'digital_therapy_services.service_id', '=', 'services.id')
                ->leftJoin('users_services', 'users_services.user_id', '=', 'digital_therapy_services.ws_id')
                ->where('digital_therapy_services.company_id', $company->id)
                ->where('services.id', $service->id)
                ->select('service_sub_categories.id')
                ->get()
                ->pluck('id')
                ->toArray();

            $wellbeingSp = \DB::table('users_services')
                ->whereIn('users_services.service_id', $getSubServiceId)
                ->select('users_services.service_id')
                ->distinct()
                ->get()
                ->pluck('service_id')
                ->toArray();

            $subServiceData     = $service->serviceSubCategory()->whereIn('service_sub_categories.id', $wellbeingSp)->get();
            $serviceDescription = (!empty($service) ? $service->description : null);
            $serviceName        = (!empty($service) ? $service->name : null);
            if ($subServiceData->count() > 0) {
                $response = [
                    'description' => $serviceDescription,
                    'serviceName' => $serviceName,
                    'topics'      => $subServiceData,
                ];
                return $this->successResponse(new TopicListCollection($response), 'Digital therapy topic get successfully');
            } else {
                return $this->successResponse([
                    'data' => [
                        'topics'      => [],
                        'description' => (!is_null($serviceDescription) ? $serviceDescription : ""),
                        'serviceName' => (!is_null($serviceName) ? $serviceName : ""),
                    ],
                ], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * List all the Counsellor listing.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function counsellorListing(Request $request, ServiceSubCategory $serviceSubCategory)
    {
        try {
            $loginUser      = $this->user();
            $company        = $loginUser->company()->select('companies.id', 'companies.eap_tab')->first();
            $checkAccess    = getCompanyPlanAccess($loginUser, 'eap');
            $digitalTherapy = $company->digitalTherapy()->first();
            $nowInUTC       = now(config('app.timezone'))->todatetimeString();
            $xDeviceOs      = strtolower($request->header('X-Device-Os', ""));

            if (!$checkAccess && !$company->eap_tab) {
                return $this->notFoundResponse('Digital therapy is disabled for this company.');
            }

            if (empty($serviceSubCategory)) {
                return $this->notFoundResponse("Sorry! Counsellor listing data not found");
            }

            $cronofySchedule = $loginUser->bookedCronofySessions()
                ->where('end_time', '>=', $nowInUTC)
                ->whereNotIn('status', ['canceled', 'open', 'completed', 'rescheduled'])
                ->select('ws_id')
                ->distinct()
                ->get()
                ->pluck('ws_id')
                ->toArray();

            $getWellbeingSpecialist = User::select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS name"), 'users.id', 'user_profile.about', 'user_profile.gender')
                ->leftJoin('user_profile', 'user_profile.user_id', '=', 'users.id')
                ->leftJoin('ws_user', 'ws_user.user_id', '=', 'users.id')
                ->leftJoin('digital_therapy_services', 'digital_therapy_services.ws_id', '=', 'users.id')
                ->leftJoin('users_services', 'users_services.user_id', '=', 'users.id')
                ->where('users_services.service_id', $serviceSubCategory->id)
                ->where('ws_user.is_cronofy', true)
                ->where('digital_therapy_services.company_id', $company->id)
                ->whereNotIn('users.id', $cronofySchedule)
                ->distinct()
                ->get();

            if ($getWellbeingSpecialist->count() > 0) {
                $response = [
                    'data'      => $getWellbeingSpecialist,
                    'topicName' => (!is_null($serviceSubCategory) ? $serviceSubCategory->name : ""),
                ];
                return $this->successResponse(new DigitalCounsellorListCollection($response), 'Digital therapy counsellor listing get successfully');
            } else {
                return $this->successResponse([
                    'data'      => [],
                    'topicName' => (!is_null($serviceSubCategory && $xDeviceOs == config('zevolifesettings.PORTAL')) ? $serviceSubCategory->name : ""),
                ], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Get Scheduling url as per perticular Wellbeing specialist.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function realTimeScheduling(Request $request, User $user)
    {
        try {
            $loginUser = $this->user();
            $company   = $loginUser->company()->first();
            $utcNow    = Carbon::now()->setTimezone(config('app.timezone'))->todatetimeString();
            $wsDetails = $user->wsuser()->first();
            $record    = CronofySchedule::where('user_id', $loginUser->id)
                ->where('ws_id', $user->id)
                ->whereNotIn('status', ['canceled', 'completed', 'rescheduled', 'open'])
                ->where('end_time', '>=', $utcNow)
                ->first();

            if (empty($record)) {
                \DB::beginTransaction();
                $digitalTherapySlot = $company->digitalTherapySlots()->select('day', 'start_time', 'end_time')->get()->toArray();
                $wsSlot             = $user->healthCocahSlots()->select('day', 'start_time', 'end_time')->get()->toArray();

                $type = "";
                if ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) {
                    $type = "portal";
                }

                $combinedAvailability = alignedAvailability($digitalTherapySlot, $wsSlot);
                $response             = $this->cronofyRepository->updateAvailability($combinedAvailability, $user, false);
                $response             = $this->cronofyRepository->realTimeScheduling($user, $company, $loginUser, $request->serviceId, $type);
                $uid                 = date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app';
                
                $insertData                     = array();
                $insertData['event_id']         = $response['real_time_scheduling']['event']['event_id'];
                $insertData['scheduling_id']    = $response['real_time_scheduling']['real_time_scheduling_id'];
                $insertData['name']             = $response['real_time_scheduling']['event']['summary'];
                $insertData['user_id']          = $loginUser->id;
                $insertData['ws_id']            = $user->id;
                $insertData['created_by']       = $loginUser->id;
                $insertData['service_id']       = $request->serviceId;
                $insertData['topic_id']         = $request->topicId;
                $insertData['company_id']       = $company->id;
                $insertData['is_group']         = false;
                $insertData['event_identifier'] = $response['url'];
                $insertData['location']         = $wsDetails->video_link;
                $insertData['event_created_at'] = \now(config('app.timezone'))->toDateTimeString();
                $insertData['status']           = $response['real_time_scheduling']['status'];
                $insertData['created_at']       = \now(config('app.timezone'))->toDateTimeString();
                $insertData['updated_at']       = \now(config('app.timezone'))->toDateTimeString();

                $record = CronofySchedule::create($insertData);
                $scheduleUsers[] = [
                    'session_id' => $record->id,
                    'user_id'    => $loginUser->id,
                    'created_at' => Carbon::now(),
                ];
                ScheduleUsers::insert($scheduleUsers);
                \DB::commit();
            }

            return $this->successResponse([
                'data' => new RealTimeSchedulingResource($record),
            ], 'real time Scheduling generated successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Get Appointment listing which session booked by login user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function appointmentList(Request $request)
    {
        try {
            $loginUser   = $this->user();
            $company     = $loginUser->company()->select('companies.id', 'companies.eap_tab')->first();
            $checkAccess = getCompanyPlanAccess($loginUser, 'eap');
            if (!$checkAccess && !$company->eap_tab) {
                return $this->notFoundResponse('Digital therapy is disabled for this company.');
            }

            $scheduleList = CronofySchedule::leftJoin('session_group_users', 'session_group_users.session_id', '=', 'cronofy_schedule.id')
                ->where(function ($q) use ($loginUser) {
                    $q->where('session_group_users.user_id', $loginUser->id);
                })
                ->select(
                    'cronofy_schedule.id',
                    'event_id',
                    'scheduling_id',
                    'name',
                    'cronofy_schedule.user_id',
                    'ws_id',
                    'start_time',
                    'is_group',
                    'end_time',
                    'status',
                    'session_group_users.is_cancelled',
                    DB::raw("(SELECT COUNT(session_group_users.id) FROM session_group_users where cronofy_schedule.id = session_group_users.session_id) as participants"),
                    DB::raw("CASE
                        WHEN is_group = true AND start_time >= NOW() AND cronofy_schedule.status!='rescheduled' AND cronofy_schedule.status!='canceled' then 1
                        WHEN is_group = true AND (session_group_users.is_cancelled = false OR status = 'canceled') AND status!='booked' then 2
                        WHEN is_group = true AND end_time <= NOW() AND (status='booked' OR status = 'completed') then 3
                        WHEN is_group = false AND start_time >= NOW() AND cronofy_schedule.status!='rescheduled' AND cronofy_schedule.status!='canceled' then 4
                        WHEN is_group = false AND (session_group_users.is_cancelled = false OR status = 'canceled') AND status!='booked' then 5
                        WHEN is_group = false AND end_time <= NOW() AND (status='booked' OR status = 'completed') then 6
                        ELSE 7
                        END AS displayorder")
                )
                ->whereNotIn('status', ['open', 'rescheduled'])
                ->orderBy('displayorder', 'ASC')
                ->orderBy('cronofy_schedule.start_time', 'ASC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($scheduleList->count() > 0) {
                return $this->successResponse(new AppoitmentSessionListCollection($scheduleList), 'Appointment session list get successfully');
            } else {
                return $this->successResponse([
                    'data' => [],
                ], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Get Appointment details which session booked by login user
     *
     * @param Request $request
     * @param CronofySchedule $cronofySchedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function appointmentDetail(Request $request, CronofySchedule $cronofySchedule)
    {
        try {
            $loginUser   = $this->user();
            $company     = $loginUser->company()->select('companies.id', 'companies.eap_tab')->first();
            $checkAccess = getCompanyPlanAccess($loginUser, 'eap');
            $nowInUTC    = now(config('app.timezone'))->todatetimeString();

            if (!$checkAccess && !$company->eap_tab) {
                return $this->notFoundResponse('Digital therapy is disabled for this company.');
            }

            // Check for upcoming booking if calendly not passed
            if (!$cronofySchedule->exists) {
                $cronofySchedule = $loginUser->bookedCronofySessions()
                    ->where('end_time', '>=', $nowInUTC)
                    ->where('status', '!=', 'canceled')
                    ->where('status', '!=', 'open')
                    ->orderByDesc('cronofy_schedule.id')
                    ->first();
            }

            if (!empty($cronofySchedule)) {
                // check if session has been rescheduled then show 404
                if ($cronofySchedule->status == 'rescheduled') {
                    return $this->notFoundResponse('This session has been rescheduled.');
                }

                $hasUpComingSession = $loginUser->bookedCronofySessions()
                    ->where('end_time', '>=', $nowInUTC)
                    ->whereNull('cancelled_at')
                    ->where('status', '!=', 'rescheduled')
                    ->count('cronofy_schedule.id');
                $cronofySchedule->hasUpComingSession = (($hasUpComingSession > 0) ? 1 : 0);

                return $this->successResponse([
                    'data' => new CronofySessionDetailsResource($cronofySchedule),
                ], 'Session details retrieved successfully.');
            } else {
                return $this->notFoundResponse('No session details found.');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Get Appointment session cancel which session booked by login user
     *
     * @param Request $request
     * @param CronofySchedule $cronofySchedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function appointmentCancel(DeleteCronofyScheduleRequest $request, CronofySchedule $cronofySchedule)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->select('companies.id', 'companies.name', 'companies.eap_tab')->first();
            $checkAccess = getCompanyPlanAccess($user, 'eap');
            $nowInUTC    = now(config('app.timezone'))->todatetimeString();

            if (!$checkAccess && !$company->eap_tab) {
                return $this->notFoundResponse('Digital therapy is disabled for this company.');
            }

            // Check for upcoming booking if calendly not passed
            if (!$cronofySchedule->exists) {
                $cronofySchedule = $user->bookedCronofySessions()
                    ->where('end_time', '>=', $nowInUTC)
                    ->where('status', '!=', 'canceled')
                    ->orderByDesc('cronofy_schedule.id')
                    ->first();
            }

            if (!empty($cronofySchedule)) {
                // check if session has been canceled then show 404
                if ($cronofySchedule->status == 'canceled') {
                    return $this->notFoundResponse('This session has been canceled.');
                }
                \DB::beginTransaction();

                if (!$cronofySchedule->is_group) {
                    $cronofySchedule->update([
                        'cancelled_reason' => $request->reason,
                        'cancelled_by'     => $user->id,
                        'cancelled_at'     => $nowInUTC,
                        'status'           => 'canceled',
                    ]);

                    $response = $this->cronofyRepository->cancelEvent($cronofySchedule->ws_id, $cronofySchedule->event_id);
                } else {
                    $updateDetails                   = ScheduleUsers::where('user_id', $user->id)->where('session_id', $cronofySchedule->id)->first();
                    $updateDetails->is_cancelled     = true;
                    $updateDetails->cancelled_reason = $request->reason;
                    $updateDetails->cancelled_at     = $nowInUTC;
                    $updateDetails->updated_at       = $nowInUTC;
                    $updateDetails->save();
                }

                $compnay      = $user->company->first();
                $userTimeZone = $user->timezone;
                $eventDate    = Carbon::parse("{$cronofySchedule->start_time}", config('app.timezone'))->setTimezone($userTimeZone)->format('M d, Y');
                $eventTime    = Carbon::parse("{$cronofySchedule->start_time}", config('app.timezone'))->setTimezone($userTimeZone)->format('h:i A');
                $duration     = Carbon::parse($cronofySchedule->end_time)->diffInMinutes($cronofySchedule->start_time);
                $meta         = $cronofySchedule->meta;
                $uid          = (!empty($meta->uid) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
                
                //Send session cancel email to ws
                $sessionData = [
                    'company'         => (!empty($compnay->id) ? $compnay->id : null),
                    'email'           => $cronofySchedule->wellbeingSpecialist->email,
                    'userName'        => $user->first_name,
                    'wsName'          => $cronofySchedule->wellbeingSpecialist->first_name,
                    'serviceName'     => $cronofySchedule->name,
                    'cancelledReason' => (!empty($request->reason) ? $request->reason : ''),
                    'eventDate'       => $eventDate,
                    'eventTime'       => $eventTime,
                    'duration'        => $duration,
                    'isGroup'         => $cronofySchedule->is_group,
                    'cancelledBy'     => 'user'
                ];
                event(new SendSessionCancelledEvent($sessionData));

                //Send session cancel email to user with ical event cancelled
                $sessionDataForUsers = [
                    'company'         => (!empty($compnay->id) ? $compnay->id : null),
                    'email'           => $user->email,
                    'userName'        => $cronofySchedule->wellbeingSpecialist->first_name,
                    'wsName'          => $user->first_name,
                    'serviceName'     => $cronofySchedule->name,
                    'cancelledReason' => (!empty($request->reason) ? $request->reason : ''),
                    'eventDate'       => $eventDate,
                    'eventTime'       => $eventTime,
                    'duration'        => $duration,
                    'isGroup'         => $cronofySchedule->is_group,
                    'cancelledBy'     => 'user',
                    'iCal'           => generateiCal([
                        'uid'         => $uid,
                        'appName'     => config('app.name'),
                        'inviteTitle' => $cronofySchedule->name,
                        'description' => "{$cronofySchedule->name} event has been cancelled.",
                        'timezone'    => config('app.timezone'),
                        'today'       => Carbon::parse($nowInUTC)->format('Ymd\THis\Z'),
                        'startTime'   => Carbon::parse($cronofySchedule->start_time)->format('Ymd\THis\Z'),
                        'endTime'     => Carbon::parse($cronofySchedule->end_time)->format('Ymd\THis\Z'),
                        'orgName'     => $cronofySchedule->wellbeingSpecialist->full_name,
                        'orgEamil'    => $cronofySchedule->wellbeingSpecialist->email,
                        'sequence'    => 0,
                    ], 'cancelled'),
                ];

                event(new SendSessionCancelledEvent($sessionDataForUsers));

                \DB::commit();
                return $this->successResponse([], trans('api_messages.digital_therapy.deleted'));
            } else {
                return $this->notFoundResponse('No session details found.');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Get Appointment reschedule which session booked by login user
     *
     * @param Request $request
     * @param CronofySchedule $cronofySchedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function appointmentReschedule(Request $request, CronofySchedule $cronofySchedule)
    {
        try {
            $loginUser   = $this->user();
            $company     = $loginUser->company()->select('companies.id', 'companies.eap_tab')->first();
            $checkAccess = getCompanyPlanAccess($loginUser, 'eap');
            $nowInUTC    = now(config('app.timezone'))->todatetimeString();

            if (!$checkAccess && !$company->eap_tab) {
                return $this->notFoundResponse('Digital therapy is disabled for this company.');
            }

            if (!empty($cronofySchedule)) {
                \DB::beginTransaction();

                $serviceId = $cronofySchedule->service_id;
                $topicId   = $cronofySchedule->topic_id;

                // $cronofySchedule->update([
                //     'status'       => 'rescheduled',
                //     'cancelled_at' => $nowInUTC,
                //     'updated_at'   => $nowInUTC,
                // ]);

                $wsUser             = User::where('id', $cronofySchedule->ws_id)->first();
                $wsDetails          = $wsUser->wsuser()->first();
                $digitalTherapySlot = $company->digitalTherapySlots()->select('day', 'start_time', 'end_time')->get()->toArray();
                $wsSlot             = $wsUser->healthCocahSlots()->select('day', 'start_time', 'end_time')->get()->toArray();

                $type = "";
                if ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) {
                    $type = "portal";
                }

                $combinedAvailability = alignedAvailability($digitalTherapySlot, $wsSlot);
                $response             = $this->cronofyRepository->updateAvailability($combinedAvailability, $wsUser, false);
                $response             = $this->cronofyRepository->realTimeScheduling($wsUser, $company, $loginUser, $serviceId, $type, $cronofySchedule->event_id);

                $insertData                     = array();
                $insertData['event_id']         = $response['real_time_scheduling']['event']['event_id'];
                $insertData['scheduling_id']    = $response['real_time_scheduling']['real_time_scheduling_id'];
                $insertData['name']             = $response['real_time_scheduling']['event']['summary'];
                $insertData['created_by']       = $loginUser->id;
                $insertData['user_id']          = $loginUser->id;
                $insertData['ws_id']            = $wsUser->id;
                $insertData['service_id']       = $serviceId;
                $insertData['topic_id']         = $topicId;
                $insertData['company_id']       = $company->id;
                $insertData['is_group']         = false;
                $insertData['event_identifier'] = $response['url'];
                $insertData['location']         = $wsDetails->video_link;
                $insertData['event_created_at'] = \now(config('app.timezone'))->toDateTimeString();
                $insertData['status']           = $response['real_time_scheduling']['status'];
                $insertData['created_at']       = \now(config('app.timezone'))->toDateTimeString();
                $insertData['updated_at']       = \now(config('app.timezone'))->toDateTimeString();

                $record = CronofySchedule::create($insertData);

                $scheduleUsers[] = [
                    'session_id' => $record->id,
                    'user_id'    => $loginUser->id,
                    'created_at' => Carbon::now(),
                ];
                ScheduleUsers::insert($scheduleUsers);
                \DB::commit();

                return $this->successResponse([
                    'data' => new RealTimeSchedulingResource($record),
                ], 'real time Scheduling generated successfully');
            } else {
                return $this->notFoundResponse('No session details found.');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Add user notes when user books the session.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNotes(Request $request, CronofySchedule $cronofySchedule)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->select('companies.id', 'companies.eap_tab')->first();
            $checkAccess = getCompanyPlanAccess($user, 'eap');
            $nowInUTC    = now(config('app.timezone'))->todatetimeString();

            if (!$checkAccess && !$company->eap_tab) {
                return $this->notFoundResponse('Digital therapy is disabled for this company.');
            }

            if (!empty($cronofySchedule)) {
                $sessionExists = $cronofySchedule->where('user_id', '=', $user->id)->first();
            }

            if (!empty($sessionExists) && !empty($request->notes)) {
                \DB::beginTransaction();
                $cronofySchedule->update([
                    'user_notes' => $request->notes,
                ]);
                \DB::commit();
                return $this->successResponse([], trans('api_messages.digital_therapy.notes_added'));
            } else {
                return $this->notFoundResponse('No session details found.');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }
}

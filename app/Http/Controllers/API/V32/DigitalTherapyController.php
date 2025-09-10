<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V32;

use App\Events\DigitaltherapyExceptionHandlingEvent;
use App\Events\SendSessionBookedEvent;
use App\Events\SendSessionCancelledEvent;
use App\Http\Collections\V31\AppoitmentSessionListCollection;
use App\Http\Collections\V31\DigitalCounsellorListCollection;
use App\Http\Collections\V31\DigitalTherapyCollection;
use App\Http\Collections\V31\TopicListCollection;
use App\Http\Controllers\API\V31\DigitalTherapyController as v31DigitalTherapyController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V31\DeleteCronofyScheduleRequest;
use App\Http\Requests\Api\V32\CreateEventSlogDTRequest;
use App\Http\Resources\V31\RealTimeSchedulingResource;
use App\Http\Resources\V32\CronofySessionDetailsResource;
use App\Http\Resources\V32\RealTimeSchedulingDataResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\AppSlide;
use App\Models\Company;
use App\Models\CronofyAuthenticate;
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
use Illuminate\Support\Str;

/**
 * Class DigitalTherapyController
 */
class DigitalTherapyController extends v31DigitalTherapyController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * variable to store the Cronofy Repository Repository object
     * @var CronofyRepository $cronofyRepository
     */
    private $cronofyRepository;

    /**
     * variable to store the Cronofy Authenticate model
     * @var CronofyAuthenticate $authenticateModel
     */
    private $authenticateModel;

    /**
     * contructor to initialize Repository object
     */
    public function __construct(CronofyRepository $cronofyRepository, CronofyAuthenticate $authenticateModel)
    {
        $this->cronofyRepository = $cronofyRepository;
        $this->authenticateModel = $authenticateModel;
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
            $company        = $user->company()->select('companies.id', 'companies.eap_tab', 'companies.parent_id')->first();
            $digitalTherapy = $company->digitalTherapy()->first();
            $xDeviceOS      = $request->header('X-Device-Os');
            if ($xDeviceOS == config('zevolifesettings.PORTAL')) {
                $checkAccess = getCompanyPlanAccess($user, 'digital-therapy');
            } else {
                $checkAccess = getCompanyPlanAccess($user, 'eap');
            }

            if (!$checkAccess) {
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
            $company     = $loginUser->company()->select('companies.id', 'companies.eap_tab', 'companies.parent_id')->first();
            $checkAccess = getCompanyPlanAccess($loginUser, 'eap');
            $utcNow      = Carbon::now()->setTimezone(config('app.timezone'))->todatetimeString();
            $xDeviceOS   = $request->header('X-Device-Os');
            if ($xDeviceOS == config('zevolifesettings.PORTAL')) {
                $checkAccess = getCompanyPlanAccess($loginUser, 'digital-therapy');
            }

            if (!$checkAccess) {
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
            $company        = $loginUser->company()->select('companies.id', 'companies.eap_tab', 'companies.parent_id')->first();
            $checkAccess    = getCompanyPlanAccess($loginUser, 'eap');
            $digitalTherapy = $company->digitalTherapy()->first();
            $nowInUTC       = now(config('app.timezone'))->todatetimeString();
            $xDeviceOS      = $request->header('X-Device-Os');
            if ($xDeviceOS == config('zevolifesettings.PORTAL')) {
                $checkAccess = getCompanyPlanAccess($loginUser, 'digital-therapy');
            }
            if (!$checkAccess) {
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
                    'topicName' => (!is_null($serviceSubCategory && $xDeviceOS == config('zevolifesettings.PORTAL')) ? $serviceSubCategory->name : ""),
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
        $loginUser = $this->user();
        $company   = $loginUser->company()->first();
        try {
            $utcNow    = Carbon::now()->setTimezone(config('app.timezone'))->todatetimeString();
            $wsDetails = $user->wsuser()->first();
            $record    = CronofySchedule::where('user_id', $loginUser->id)
                ->where('ws_id', $user->id)
                ->whereNotIn('status', ['canceled', 'completed', 'rescheduled', 'open'])
                ->where('end_time', '>=', $utcNow)
                ->first();

            if (empty($record)) {
                \DB::beginTransaction();
                $digitalTherapySlot = $company->digitalTherapySlots()->select('day', 'start_time', 'end_time')->whereRaw('find_in_set("' . $user->id . '", ws_id)')->get()->toArray();
                $wsSlot             = $user->healthCocahSlots()->select('day', 'start_time', 'end_time')->get()->toArray();

                $type = "";
                if ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) {
                    $type = "portal";
                }

                $combinedAvailability = alignedAvailability($digitalTherapySlot, $wsSlot);
                $updateAvailability   = $this->cronofyRepository->updateAvailability($combinedAvailability, $user, false);
                $response             = $this->cronofyRepository->realTimeScheduling($user, $company, $loginUser, $request->serviceId, $type);

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
            ], 'Real time scheduling generated successfully');
        } catch (\Exception $e) {
            \DB::rollBack();
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent([
                'type'         => 'Booking',
                'message'      => (string) trans('labels.common_title.something_wrong'),
                'company'      => $company,
                'wsDetails'    => $user,
                'errorDetails' => json_encode($e->error_details()),
            ]));
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
        $loginUser = $this->user();
        $company   = $loginUser->company()->select('companies.id', 'companies.name', 'companies.eap_tab')->first();
        try {

            $checkAccess = getCompanyPlanAccess($loginUser, 'eap');
            $nowInUTC    = now(config('app.timezone'))->todatetimeString();
            $xDeviceOS   = $request->header('X-Device-Os');
            if ($xDeviceOS == config('zevolifesettings.PORTAL')) {
                $checkAccess = getCompanyPlanAccess($loginUser, 'digital-therapy');
            }
            if (!$checkAccess) {
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
                $digitalTherapySlot = $company->digitalTherapySlots()->select('day', 'start_time', 'end_time')->whereRaw('find_in_set("' . $cronofySchedule->ws_id . '", ws_id)')->get()->toArray();
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
                ], 'Real time scheduling generated successfully');
            } else {
                return $this->notFoundResponse('No session details found.');
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            $user = [];
            if (!empty($cronofySchedule)) {
                $user = User::where('id', $cronofySchedule->ws_id)->first();
            }
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent([
                'type'         => 'Rescheduling',
                'message'      => (string) trans('labels.common_title.something_wrong'),
                'company'      => $company,
                'wsDetails'    => $user,
                'errorDetails' => json_encode($e->error_details()),
            ]));
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
            $loginUser = $this->user();
            $company   = $loginUser->company()->select('companies.id', 'companies.eap_tab', 'companies.parent_id')->first();
            $xDeviceOS = $request->header('X-Device-Os');
            if ($xDeviceOS == config('zevolifesettings.PORTAL')) {
                $checkAccess = getCompanyPlanAccess($loginUser, 'digital-therapy');
            } else {
                $checkAccess = getCompanyPlanAccess($loginUser, 'eap');
            }

            if (!$checkAccess) {
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
                        WHEN is_group = true AND end_time >= NOW() AND cronofy_schedule.status='booked' then 0
                        WHEN is_group = false AND end_time >= NOW() AND cronofy_schedule.status='booked' then 1
                        WHEN is_group = true AND end_time <= NOW() AND cronofy_schedule.cancelled_by is null AND session_group_users.is_cancelled = false AND cronofy_schedule.cancelled_by is null AND
                            status != 'canceled' AND
                            (status='booked' OR status = 'completed') then 2
                        WHEN is_group = false AND end_time <= NOW() AND cronofy_schedule.cancelled_by is null AND session_group_users.is_cancelled = false AND session_group_users.is_cancelled = false AND
                            status != 'canceled' AND
                            (status='booked' OR status = 'completed') then 3
                        WHEN is_group = true AND (session_group_users.is_cancelled = true OR status = 'canceled') then 4
                        WHEN is_group = false AND (session_group_users.is_cancelled = true OR status = 'canceled' OR status = 'short_canceled') then 5
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
     * Get Appointment session cancel which session booked by login user
     *
     * @param Request $request
     * @param CronofySchedule $cronofySchedule
     * @return \Illuminate\Http\JsonResponse
     */
    public function appointmentCancel(DeleteCronofyScheduleRequest $request, CronofySchedule $cronofySchedule)
    {
        $user    = $this->user();
        $company = $user->company()->select('companies.id', 'companies.name', 'companies.eap_tab')->first();
        try {
            $checkAccess = getCompanyPlanAccess($user, 'eap');
            $nowInUTC    = now(config('app.timezone'))->todatetimeString();
            $xDeviceOS   = $request->header('X-Device-Os');
            if ($xDeviceOS == config('zevolifesettings.PORTAL')) {
                $checkAccess = getCompanyPlanAccess($user, 'digital-therapy');
            }

            if (!$checkAccess) {
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
                    'wsName'          => $cronofySchedule->wellbeingSpecialist->full_name,
                    'serviceName'     => $cronofySchedule->name,
                    'cancelledReason' => (!empty($request->reason) ? $request->reason : ''),
                    'eventDate'       => $eventDate,
                    'eventTime'       => $eventTime,
                    'duration'        => $duration,
                    'isGroup'         => $cronofySchedule->is_group,
                    'cancelledBy'     => 'user',
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
                    'iCal'            => generateiCal([
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
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent([
                'type'         => 'Cancellation',
                'message'      => (string) trans('labels.common_title.something_wrong'),
                'company'      => $company,
                'wsDetails'    => $user,
                'errorDetails' => [],
            ]));
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
            $xDeviceOS   = $request->header('X-Device-Os');
            if ($xDeviceOS == config('zevolifesettings.PORTAL')) {
                $checkAccess = getCompanyPlanAccess($loginUser, 'digital-therapy');
            }

            if (!$checkAccess) {
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
     * Get Scheduling Data for Portal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function realTimeUIElementData(Request $request, User $user)
    {
        $loginUser = $this->user();
        $company   = $loginUser->company()->first();
        try {
            $data      = array();
            $utcNow    = Carbon::now()->setTimezone(config('app.timezone'))->todatetimeString();
            $wsDetails = $user->wsuser()->first();

            \DB::beginTransaction();
            $appTimezone        = config('app.timezone');
            $digitalTherapySlot = $company->digitalTherapySlots()->select('day', 'start_time', 'end_time')->whereRaw('find_in_set("' . $user->id . '", ws_id)')->get()->toArray();
            $wsSlot             = $user->healthCocahSlots()->select('day', 'start_time', 'end_time')->get()->toArray();
            $timezone           = (!empty($loginUser->timezone) ? $loginUser->timezone : $appTimezone);
            $type               = "";
            $oldSession         = [];
            if ($request->header('X-Device-Os') == config('zevolifesettings.PORTAL')) {
                $type = "portal";
            }
            $services              = Service::where('id', $request->serviceId)->select('services.name')->first();
            $duration              = config('cronofy.schedule_duration');
            $digitalTherapyDetails = $company->digitalTherapy()->first();
            $featureBooking        = config('cronofy.feature_booking');
            $advanceBooking        = config('cronofy.advanceBooking');
            $serviceName           = (!empty($services)) ? $services->name : config('cronofy.serviceName');
            if (!empty($digitalTherapyDetails)) {
                $advanceBooking = $digitalTherapyDetails->dt_advanced_booking;
                $featureBooking = $digitalTherapyDetails->dt_future_booking;
                $duration       = ($serviceName == 'Counselling') ? $digitalTherapyDetails->dt_counselling_duration : $digitalTherapyDetails->dt_coaching_duration;
            }

            $startTime            = Carbon::now()->setTimezone($appTimezone)->addHour($advanceBooking)->toDateTimeString();
            $endTime              = Carbon::now()->setTimezone($appTimezone)->addDays($featureBooking)->toDateTimeString();
            $combinedAvailability = [];
            if (!empty($digitalTherapySlot) && !empty($wsSlot)) {
                $combinedAvailability = alignedAvailability($digitalTherapySlot, $wsSlot);
                $response             = $this->cronofyRepository->updateAvailability($combinedAvailability, $user, false);
            }

            $tokens             = $this->authenticateModel->getTokens($user->id);
            $subId              = $tokens['subId'];
            $response           = $this->cronofyRepository->dateTimePicker($user->id, $request->url);
            $queryPeriod        = generateQueryPeriod($combinedAvailability, $startTime, $endTime, $appTimezone, $timezone);
            $date               = Carbon::now();
            $eventId            = 'zevolife_dt_' . (string) $date->valueOf();
            $realTimeScheduleId = 'sch_' . (string) Str::uuid();
            if ($request->reschedule && isset($request->sessionId) && !empty($request->sessionId)) {
                $oldSession         = CronofySchedule::where('id', $request->sessionId)->first();
                $eventId            = $oldSession->event_id;
                $realTimeScheduleId = $oldSession->scheduling_id;
            }
            $insertData                     = array();
            $insertData['event_id']         = $eventId;
            $insertData['scheduling_id']    = $realTimeScheduleId;
            $insertData['name']             = $serviceName;
            $insertData['user_id']          = $loginUser->id;
            $insertData['ws_id']            = $user->id;
            $insertData['created_by']       = $loginUser->id;
            $insertData['service_id']       = $request->serviceId;
            $insertData['topic_id']         = $request->topicId;
            $insertData['company_id']       = $company->id;
            $insertData['is_group']         = false;
            $insertData['event_identifier'] = null;
            $insertData['location']         = $wsDetails->video_link;
            $insertData['token']            = !(empty($response)) ? $response['element_token']['token'] : null;
            $insertData['event_created_at'] = \now(config('app.timezone'))->toDateTimeString();
            $insertData['status']           = 'open';
            $insertData['created_at']       = \now(config('app.timezone'))->toDateTimeString();
            $insertData['updated_at']       = \now(config('app.timezone'))->toDateTimeString();
            $record                         = CronofySchedule::create($insertData);
            $data                           = $insertData;
            $data['id']                     = null;
            if ($record) {
                $data['id']      = $record->id;
                $scheduleUsers[] = [
                    'session_id' => $record->id,
                    'user_id'    => $loginUser->id,
                    'created_at' => Carbon::now(),
                ];
                ScheduleUsers::insert($scheduleUsers);
            }

            $data['duration']    = $duration;
            $data['timezone']    = $timezone;
            $data['subId']       = $subId;
            $data['startTime']   = $startTime;
            $data['endTime']     = $endTime;
            $data['queryPeriod'] = $queryPeriod;
            $data['reschedule']  = $request->reschedule;
            $data['dataCenter']  = env('CRONOFY_DATA_CENTER');

            \DB::commit();
            return $this->successResponse([
                'data' => new RealTimeSchedulingDataResource($data),
            ], 'Real time scheduling data generated successfully');

        } catch (\Exception $e) {
            \DB::rollback();
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent([
                'type'         => 'Booking',
                'message'      => (string) trans('labels.common_title.something_wrong'),
                'company'      => $company,
                'wsDetails'    => $user,
                'errorDetails' => [],
            ]));
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Create Event Slot when UI element done.
     *
     * @param CreateEventSlogDTRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEventSlotDT(CreateEventSlogDTRequest $request, User $user)
    {
        $loginUser = $this->user();
        $company   = $loginUser->company()->first();
        try {
            \DB::beginTransaction();
            $utcNow             = \now(config('app.timezone'))->toDateTimeString();
            $meta               = [];
            $newScheduleDetails = CronofySchedule::where('id', $request->scheduleId)->select('id', 'created_by', 'user_id', 'company_id', 'ws_id', 'name', 'location', 'is_group', 'meta')->first();
            $notificationTag    = "group-session-invite";
            $isRescheduled      = false;
            if ($request->reschedule) {
                $notificationTag = "group-session-reschedule";
                $isRescheduled   = true;
                CronofySchedule::where('event_id', $request->eventId)
                    ->where('scheduling_id', $request->schedulingId)
                    ->whereNotIn('id', [$request->scheduleId])
                    ->update([
                        'cancelled_at' => $utcNow,
                        'updated_at'   => $utcNow,
                        'status'       => 'rescheduled',
                    ]);
            }

            $inviteUsers = scheduleUsers::leftjoin('users', 'users.id', '=', 'session_group_users.user_id')->where('session_group_users.session_id', $request->scheduleId)
                ->select(\DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS display_name"), 'users.email')
                ->get()
                ->toArray();

            $availabilityResponse = $this->cronofyRepository->createEvent($request->all(), $inviteUsers);
            $startDate            = date("Y-m-d H:i:s", strtotime($request->notification['notification']['slot']['start']));
            $endDate              = date("Y-m-d H:i:s", strtotime($request->notification['notification']['slot']['end']));
            $bookingTimezone      = $request->notification['notification']['tzid'];
            $meta                 = $newScheduleDetails->meta;
            $uid                  = (!empty($meta) ? $meta->uid : date('Ymd') . 'T' . date('His') . '-' . rand() . '@zevo.app');
            if (!empty($newScheduleDetails->user)) {
                $company      = $newScheduleDetails->user->company->first();
                $userTimeZone = $newScheduleDetails->user->timezone;
            } else {
                $company      = Company::where('id', $newScheduleDetails->company_id)->first();
                $userTimeZone = $newScheduleDetails->wellbeingSpecialist->timezone;
            }
            $meta = [
                "wellbeing_specialist" => $newScheduleDetails->ws_id,
                "timezone"             => $userTimeZone,
                "uid"                  => $uid,
            ];

            $records = CronofySchedule::where('id', $request->scheduleId)
                ->update([
                    'start_time' => $startDate,
                    'end_time'   => $endDate,
                    'meta'       => $meta,
                    'timezone'   => $bookingTimezone,
                    'updated_at' => $utcNow,
                    'status'     => 'booked',
                ]);

            if ($records) {
                $sequenceLog = $newScheduleDetails->inviteSequence()->select('users.id')->where('user_id', $newScheduleDetails->user->id)->first();
                $sequence    = 0;
                if (is_null($sequenceLog)) {
                    // record not exist adding
                    $newScheduleDetails->inviteSequence()->attach([$newScheduleDetails->user->id]);
                    $sequence = 0;
                } else {
                    // record exist updating sequence
                    $sequence = ($sequenceLog->pivot->sequence + 1);
                    $sequenceLog->pivot->update([
                        'sequence' => $sequence,
                    ]);
                }
                $inviteTitle = $newScheduleDetails->name;
                $appName     = config('app.name');
                $duration    = Carbon::parse($endDate)->diffInMinutes($startDate);
                $eventDate   = Carbon::parse("{$startDate}", config('app.timezone'))->setTimezone($userTimeZone)->format('M d, Y');
                $eventTime   = Carbon::parse("{$startDate}", config('app.timezone'))->setTimezone($userTimeZone)->format('h:i A');
                // Send session booked email to User
                $sessionDataToUser = [
                    'company'       => (!empty($company) ? $company->id : null),
                    'email'         => $newScheduleDetails->user->email,
                    'userFirstName' => $newScheduleDetails->user->first_name,
                    'userName'      => $newScheduleDetails->user->full_name,
                    'wsFirstName'   => $newScheduleDetails->wellbeingSpecialist->first_name,
                    'wsName'        => $newScheduleDetails->wellbeingSpecialist->full_name,
                    'serviceName'   => $newScheduleDetails->name,
                    'eventDate'     => $eventDate,
                    'eventTime'     => $eventTime,
                    'duration'      => $duration,
                    'location'      => $newScheduleDetails->location,
                    'to'            => 'user',
                    'isGroup'       => $newScheduleDetails->is_group,
                    'isRescheduled' => $isRescheduled,
                    'iCal'          => generateiCal([
                        'uid'         => $uid,
                        'appName'     => $appName,
                        'inviteTitle' => $inviteTitle,
                        'description' => (!empty($newScheduleDetails->name) ? $newScheduleDetails->name . " event has been confirmed" : null),
                        'timezone'    => config('app.timezone'),
                        'today'       => Carbon::parse($utcNow)->format('Ymd\THis\Z'),
                        'startTime'   => Carbon::parse($startDate)->format('Ymd\THis\Z'),
                        'endTime'     => Carbon::parse($endDate)->format('Ymd\THis\Z'),
                        'orgName'     => $newScheduleDetails->wellbeingSpecialist->full_name,
                        'orgEamil'    => $newScheduleDetails->user->email,
                        'sequence'    => $sequence,
                    ]),
                ];
                event(new SendSessionBookedEvent($sessionDataToUser));

                // Send session booked email to Ws
                $sendTo          = null;
                $sessionDataToWs = [
                    'company'       => (!empty($company) ? $company->id : null),
                    'email'         => $newScheduleDetails->wellbeingSpecialist->email,
                    'userFirstName' => (!empty($newScheduleDetails) && !empty($newScheduleDetails->user) && isset($newScheduleDetails->user->first_name) ? $newScheduleDetails->user->first_name : null),
                    'userName'      => (!empty($newScheduleDetails) && !empty($newScheduleDetails->user) && isset($newScheduleDetails->user->full_name) ? $newScheduleDetails->user->full_name : null),
                    'wsFirstName'   => $newScheduleDetails->wellbeingSpecialist->first_name,
                    'wsName'        => $newScheduleDetails->wellbeingSpecialist->full_name,
                    'serviceName'   => $newScheduleDetails->name,
                    'eventDate'     => $eventDate,
                    'eventTime'     => $eventTime,
                    'duration'      => $duration,
                    'companyName'   => (!empty($company) ? $company->name : null),
                    'location'      => $newScheduleDetails->location,
                    'isGroup'       => $newScheduleDetails->is_group,
                    'sessionId'     => $newScheduleDetails->id,
                    'to'            => ((!$newScheduleDetails->is_group) ? 'wellbeing_specialist' : 'zca'),
                    'isRescheduled' => $isRescheduled,
                ];

                event(new SendSessionBookedEvent($sessionDataToWs));

                \DB::commit();
                return $this->successResponse(['data' => []], trans('Cronofy.group_session.message.data_update_success'));
            } else {
                \DB::rollback();
                return $this->badRequestResponse(trans('Cronofy.group_session.message.something_wrong'));
            }
        } catch (\Exception $e) {
            \DB::rollback();
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent([
                'type'         => 'Booking',
                'message'      => (string) trans('labels.common_title.something_wrong'),
                'company'      => $company,
                'wsDetails'    => $user,
                'errorDetails' => [],
            ]));
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Send Mail when Exception
     *
     * @param CreateEventSlogDTRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cronofyException(Request $request)
    {
        $loginUser = $this->user();
        $company   = $loginUser->company()->first();
        try {
            $company      = [];
            $user         = [];
            $errorMessage = null;
            if (isset($request->companyId)) {
                $company = Company::where('id', $request->companyId)->first();
            }

            if (isset($request->wsId)) {
                $user = User::where('id', $request->wsId)->first();
            }

            if (isset($request->errorMessage)) {
                $errorMessage = (string) strip_tags($request->errorMessage);
            }

            $data = [
                'type'         => 'Portal Session Booking',
                'message'      => $errorMessage,
                'company'      => $company,
                'wsDetails'    => $user,
                'errorDetails' => [],
            ];
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent($data));

            return $this->successResponse(['data' => []], trans('Cronofy.group_session.message.email_send_success'));
        } catch (\Exception $e) {
            \DB::rollback();
            // Send email when trow error while digital therapy any operation
            event(new DigitaltherapyExceptionHandlingEvent([
                'type'         => 'Booking',
                'message'      => (string) trans('labels.common_title.something_wrong'),
                'company'      => $company,
                'wsDetails'    => $user,
                'errorDetails' => [],
            ]));
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }
}

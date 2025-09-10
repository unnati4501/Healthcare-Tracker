<?php

namespace App\Repositories;

use App\Interfaces\CronofyRepositoryInterface;
use App\Models\CronofyAuthenticate;
use App\Models\CronofyCalendar;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Cronofy\Cronofy;
use Illuminate\Support\Facades\Log;

/**
 * Class CronofyRepository
 */
class CronofyRepository implements CronofyRepositoryInterface
{
    /**
     * Cronofy Client Id
     * @var $clientId
     */
    private $clientId;

    /**
     * Cronofy Client Secret
     * @var $clientSecret
     */
    private $clientSecret;

    /**
     * Redirect uri for cronofy authenticate
     * @var $redirectUri
     */
    private $redirectUri;

    /**
     * Data center for cronofy authenticate
     * @var $redirectUri
     */
    private $dataCenter = null;

    /**
     * variable to store the Cronofy Authenticate model object
     * @var CronofyAuthenticate $authenticateModel
     */
    protected $authenticateModel;

    /**
     * variable to store the Cronofy calendar model object
     * @var CronofyCalendar $cronofyCalendar
     */
    protected $cronofyCalendar;

    /**
     * contructor to initialize model object
     */
    public function __construct(CronofyAuthenticate $authenticateModel, CronofyCalendar $cronofyCalendar)
    {
        $this->clientId     = config('cronofy.client_id');
        $this->clientSecret = config('cronofy.client_secret');
        $this->redirectUri  = config('cronofy.redirect_uri');
        $this->dataCenter   = config('cronofy.data_center');

        $this->authenticateModel = $authenticateModel;
        $this->cronofyCalendar   = $cronofyCalendar;
    }

    /**
     * Cronofy Authenticate
     * @return string
     */
    public function authenticate()
    {
        $cronofy = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "data_center"   => $this->dataCenter,
        ]);

        $params = [
            'redirect_uri'  => $this->redirectUri,
            'scope'         => ['read_account', 'list_calendars', 'read_events', 'create_event', 'delete_event'],
            'avoid_linking' => true,
        ];
        return $cronofy->getAuthorizationURL($params);
    }

    /**
     * Cronofy Authenticate for get access token
     * @return json
     */
    public function callback($code)
    {
        $result  = [];
        $cronofy = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "data_center"   => $this->dataCenter,
        ]);

        $params = [
            'redirect_uri' => $this->redirectUri,
            'code'         => $code,
        ];
        $response = $cronofy->requestToken($params);

        if ($response) {
            $accessToken  = $cronofy->accessToken;
            $refreshToken = $cronofy->refreshToken;
            $expriesIn    = $cronofy->expiresIn;
            $sub          = $cronofy->tokens['sub'];
            $profileName  = $cronofy->tokens['linking_profile']['profile_name'];

            $responseArray = [
                'accessToken'  => $accessToken,
                'refreshToken' => $refreshToken,
                'expiresIn'    => $expriesIn,
                'subId'        => $sub,
                'profileName'  => $profileName,
            ];

            $authenticate = $this->authenticateModel->storeAuthenticate($responseArray);

            $cronofyListCalendar = new Cronofy([
                "client_id"     => $this->clientId,
                "client_secret" => $this->clientSecret,
                "access_token"  => $accessToken,
                "refresh_token" => $refreshToken,
                "data_center"   => $this->dataCenter,
            ]);

            $calendarList = $cronofyListCalendar->listCalendars();

            if (!empty($calendarList)) {
                return $this->cronofyCalendar->storeCalendar($calendarList['calendars'], $authenticate->id);
            }
        }
        return $result;
    }

    /**
     * Cronofy Authenticate for get access token from the refresh token
     * @return json
     */
    public function refreshToken($authentication)
    {
        $accessToken  = $authentication->access_token;
        $refreshToken = $authentication->refresh_token;
        $userId       = $authentication->user_id;

        $cronofy = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $params = [
            "grant_type"    => "refresh_token",
            'refresh_token' => $refreshToken,
        ];

        $cronofy->refreshToken($params);
        $responseArray = [
            'accessToken'  => $cronofy->accessToken,
            'refreshToken' => $cronofy->refreshToken,
            'userId'       => $userId,
        ];

        $this->authenticateModel->updateAuthenticate($responseArray);
    }

    /**
     * Cronofy link calendar
     * @return json
     */
    public function linkCalendar()
    {
        $user         = auth()->user();
        $tokens       = $this->authenticateModel->getTokens($user->id);
        $accessToken  = $tokens['accessToken'];
        $refreshToken = $tokens['refreshToken'];

        $cronofy = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $response = $cronofy->requestLinkToken();

        $params = [
            'redirect_uri' => $this->redirectUri,
            'scope'        => ['read_account', 'list_calendars', 'read_events', 'create_event', 'delete_event'],
            'link_token'   => $response['link_token'],
        ];
        return $cronofy->getAuthorizationURL($params);
    }

    /**
     * Cronofy unlink calendar
     * @return json
     */
    public function unlinkCalendar($profileId)
    {
        $user         = auth()->user();
        $tokens       = $this->authenticateModel->getTokens($user->id);
        $accessToken  = $tokens['accessToken'];
        $refreshToken = $tokens['refreshToken'];

        $cronofy = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $cronofy->revokeProfile($profileId);

        return $this->cronofyCalendar->removeCalendar($profileId);
    }

    /**
     * Cronofy update availability slots
     * @return json
     */
    public function updateAvailability($slots, $user, $availabilityTimezone = "", $isSequence = true, $appTimezone = 'UTC', $duration = 30)
    {
        $tokens        = $this->authenticateModel->getTokens($user->id);
        $accessToken   = $tokens['accessToken'];
        $refreshToken  = $tokens['refreshToken'];
        $response      = [];
        $weeklyPeriods = [];
        $cronofy       = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $availabilityDays = config('zevolifesettings.hc_availability_days');
        $todayDate        = Carbon::now()->setTimezone($appTimezone)->toDateTimeString();

        if ($isSequence) {
            if (!empty($slots)) {
                foreach ($slots as $day => $slot) {
                    if (is_array($slot['start_time'])) {
                        foreach ($slot['start_time'] as $key => $time) {
                            $endTime   = $slot['end_time'][$key];
                            $startTime = $time;
                             
                            if ($endTime >= $todayDate && $endTime > $startTime) {
                                $dateCheckDuration = Carbon::parse($startTime);
                                $diff              = $dateCheckDuration->diffInMinutes($endTime);

                                if ($diff >= $duration) {
                                    $periods = [
                                        'day'        => strtolower($availabilityDays[$day]),
                                        'start_time' => $startTime,
                                        'end_time'   => $endTime,
                                    ];
                                    array_push($weeklyPeriods, $periods);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if (!empty($slots)) {
                foreach ($slots as $key => $slot) {
                    $endTime   = $slot['end_time'];
                    $startTime = $slot['start_time'];
                    if(Carbon::parse($endTime)->format('H:i') != '00:00') {
                        if ($endTime >= $todayDate && $endTime > $startTime) {
                            $dateCheckDuration = Carbon::parse($startTime);
                            $diff              = $dateCheckDuration->diffInMinutes($endTime);

                            if ($diff >= $duration) {
                                $periods = [
                                    'day'        => strtolower($availabilityDays[$slot['day']]),
                                    'start_time' => $startTime,
                                    'end_time'   => $endTime,
                                ];
                                array_push($weeklyPeriods, $periods);
                            }
                        }
                    } else {
                        $periods = [
                            'day'        => strtolower($availabilityDays[$slot['day']]),
                            'start_time' => $startTime,
                            'end_time'   => "24:00",
                        ];
                        array_push($weeklyPeriods, $periods);
                    }
                    
                }
            }
        }
        $calendarIds = $this->cronofyCalendar->getCalendarIds($user->id);

        if (!empty($calendarIds) && !empty($weeklyPeriods)) {
            // The details of the event to create or update:
            $params = [
                "availability_rule_id" => "default",
                "calendar_ids"         => $calendarIds,
                "tzid"                 => !is_null($availabilityTimezone) ? $availabilityTimezone : $user->timezone,
                "weekly_periods"       => $weeklyPeriods,
            ];

            $response = $cronofy->createAvailabilityRule($params);
        }

        return $response;
    }

    /**
     * Cronofy General Real time scheduling for mobile
     * @return json
     */
    public function realTimeScheduling($user, $company, $loginUser, $serviceId, $type = '', $eventId = '', $isRescheduled = false, $healthCoachUnavailable = [], $digitalTherapySlot = [], $combinedAvailability = [], $specificAvailabilities = [])
    {
        $appTimezone = config('app.timezone');
        $timezone    = (!empty($loginUser->timezone) ? $loginUser->timezone : $appTimezone);
        $tokens      = $this->authenticateModel->getTokens($user->id);

        $accessToken  = $tokens['accessToken'];
        $refreshToken = $tokens['refreshToken'];
        $subId        = $tokens['subId'];

        $cronofy = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $calendarIds    = $this->cronofyCalendar->getCalendarIds($user->id);
        $targetCalendar = [];
        foreach ($calendarIds as $calendar) {
            $tempTargetCalendar = [
                'sub'         => $subId,
                'calendar_id' => $calendar,
            ];
            array_push($targetCalendar, $tempTargetCalendar);
        }
        $prefixEvent    = config('cronofy.eventIdPrefix');
        if ($type == 'portal') {
            $redirectUrl = Request()->get('redirectUrl');
        } else if ($type == 'backend') {
            $redirectUrl = config('cronofy.backend_redirect_url');
        } else {
            $redirectUrl = config('cronofy.redirectUri');
        }
        $callbackUrl           = ($isRescheduled) ? config('cronofy.rescheduledCallbackUrl') : config('cronofy.callbackUrl');
        $services              = Service::where('id', $serviceId)->select('services.name', 'services.session_duration')->first();
        $digitalTherapyDetails = $company->digitalTherapy()->first();
        $serviceName           = (!empty($services)) ? $services->name : config('cronofy.serviceName');
        if (!empty($digitalTherapyDetails)) {
            $advanceBooking      = $digitalTherapyDetails->dt_advanced_booking;
            $featureBooking      = $digitalTherapyDetails->dt_future_booking ?? config('cronofy.feature_booking');
            $duration            = (!empty($services)) ? $services->session_duration : config('cronofy.schedule_duration');
            $setHoursBy          = $digitalTherapyDetails->set_hours_by;
            $setAvailabilityBy   = $digitalTherapyDetails->set_availability_by;
            $startTime           = Carbon::now()->setTimezone(config('app.timezone'))->addHour($advanceBooking)->toDateTimeString();
            $availabilityRecords = [];
            if (($setHoursBy == 2 && $setAvailabilityBy == 1) || ($setHoursBy == 1 && $setAvailabilityBy == 1)) {
                $endTime = Carbon::now()->setTimezone(config('app.timezone'))->addDays($featureBooking)->toDateTimeString();
                if (!empty($combinedAvailability)) {
                    $availabilityRecords = generateQueryPeriod($combinedAvailability, $startTime, $endTime, $appTimezone, $digitalTherapySlot['timezone'], $healthCoachUnavailable, $duration, $specificAvailabilities);
                }

                $targetUnavailable = [
                    [
                        "start" => date("Y-m-d\TH:i:s.000\Z", strtotime($startTime)),
                        "end"   => date("Y-m-d\TH:i:s.000\Z", strtotime($endTime)),
                    ],
                ];
                if (!empty($availabilityRecords)) {
                    $targetUnavailable = [];
                    foreach ($availabilityRecords as $queryPeriod) {
                        if ($queryPeriod['start'] < $queryPeriod['end']) {
                            $tempArray = [
                                'start' => date("Y-m-d\TH:i:s.000\Z", strtotime($queryPeriod['start'])),
                                'end'   => date("Y-m-d\TH:i:s.000\Z", strtotime($queryPeriod['end'])),
                            ];
                            array_push($targetUnavailable, $tempArray);
                        }
                    }
                }
            } else if (($setHoursBy == 2 && $setAvailabilityBy == 2) || ($setHoursBy == 1 && $setAvailabilityBy == 2)) {
                $targetUnavailable = [];
                $endTime           = Carbon::now()->setTimezone(config('app.timezone'))->addYear()->toDateTimeString();
                if (!empty($digitalTherapySlot['data'])) {
                    foreach ($digitalTherapySlot['data'] as $queryPeriod) {
                        if ($queryPeriod['start_time'] < $queryPeriod['end_time']) {
                            $start = Carbon::parse($queryPeriod['start_time'], $digitalTherapySlot['timezone'])->setTimezone($appTimezone)->toDateTimeString();
                            $end   = Carbon::parse($queryPeriod['end_time'], $digitalTherapySlot['timezone'])->setTimezone($appTimezone)->toDateTimeString();
                            if ($end >= $startTime) {
                                if ($start < $startTime) {
                                    $start = $startTime;
                                }
                                $dateCheckDuration = Carbon::parse($start);
                                $diff              = $dateCheckDuration->diffInMinutes($end);

                                if ($diff >= $duration) {
                                    $tempArray = [
                                        'start' => date("Y-m-d\TH:i:s.000\Z", strtotime($start)),
                                        'end'   => date("Y-m-d\TH:i:s.000\Z", strtotime($end)),
                                    ];
                                    array_push($targetUnavailable, $tempArray);
                                }
                            }
                        }
                    }
                }
            }

            $startTime          = date("Y-m-d\TH:i:s.000\Z", strtotime($startTime));
            $endTime            = date("Y-m-d\TH:i:s.000\Z", strtotime($endTime));
            $date               = Carbon::now();
            $timeInMilliseconds = (string) $date->valueOf();
            $eventId            = empty($eventId) ? $prefixEvent . $timeInMilliseconds : $eventId;
            $timezone           = implode('/', array_map('ucwords', explode('/', $timezone)));
            $params             = [
                'oauth'            => [
                    'redirect_uri' => $redirectUrl,
                ],
                'event'            => [
                    'event_id' => $eventId,
                    'start'    => [
                        'time' => $startTime,
                        'tzid' => $digitalTherapySlot['timezone'],
                    ],
                    'end'      => [
                        'time' => $endTime,
                        'tzid' => $digitalTherapySlot['timezone'],
                    ],
                    'summary'  => $serviceName,
                ],
                'availability'     => [
                    'participants'      => [
                        [
                            'members'  => [
                                [
                                    'sub'                  => $subId,
                                    'managed_availability' => false,
                                    "calendar_ids"         => $calendarIds,
                                ],
                            ],
                            "required" => "all",
                        ],
                    ],
                    'required_duration' => [
                        'minutes' => $duration,
                    ],
                    "start_interval"   => [
                        "minutes" => config('zevolifesettings.start_interval'),
                    ],
                    'query_periods'     => $targetUnavailable,
                    "buffer" => [
                        "before" => [
                            "minutes" => config('cronofy.buffer.before'),
                        ],
                        "after" => [
                            "minutes" => config('cronofy.buffer.after'),
                        ],
                    ],
                    "max_results" => config('cronofy.max_result'),
                ],
                "target_calendars" => $targetCalendar,
                'tzid'             => $timezone,
                "callback_urls"    => [
                    "completed_url"         => $callbackUrl,
                    "no_times_suitable_url" => $callbackUrl,
                ],
            ];

            if ($type == 'backend' || $type == 'portal') {
                $params['redirect_urls'] = [
                    'completed_url' => $redirectUrl,
                ];
            }
            $response = $cronofy->realTimeScheduling($params);
            return $response;
        }
    }

    /**
     * Cronofy cancel Event
     * @return json
     */
    public function cancelEvent($wsId, $eventId)
    {
        $tokens       = $this->authenticateModel->getTokens($wsId);
        $accessToken  = $tokens['accessToken'];
        $refreshToken = $tokens['refreshToken'];
        $cronofy      = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $calendarIds = $this->cronofyCalendar->getCalendarIds($wsId);
        $response    = [];
        if (!empty($calendarIds)) {
            $params = [
                'calendar_id' => $calendarIds[0],
                'event_id'    => $eventId,
            ];

            $response = $cronofy->deleteEvent($params);
        }

        return $response;
    }

    /**
     * Cronofy update calendar Ids
     * @param $userId
     * @param $authenticateId
     * @return json
     */
    public function updateUserInfo($userId, $authenticateId)
    {
        $tokens       = $this->authenticateModel->getTokens($userId);
        $accessToken  = $tokens['accessToken'];
        $refreshToken = $tokens['refreshToken'];

        $cronofy = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $getUserInfo = $cronofy->getUserInfo();
        if (!empty($getUserInfo)) {
            $profiles = $getUserInfo['cronofy.data']['profiles'];
            if (!empty($profiles)) {
                $calenderIds      = array_column($profiles[0]['profile_calendars'], 'calendar_id');
                $getCalendarCount = CronofyCalendar::where('user_id', $userId)->where('cronofy_id', $authenticateId)->select('id')->distinct()->count();
                if (count($calenderIds) != $getCalendarCount) {
                    CronofyCalendar::where('user_id', $userId)->where('cronofy_id', $authenticateId)->delete();
                    $calendarList = $cronofy->listCalendars();
                    if (!empty($calendarList)) {
                        return $this->cronofyCalendar->storeCalendar($calendarList['calendars'], $authenticateId);
                    }
                }
            }
        }
    }

    /**
     * Cronofy UI Element Token
     * @param $userId
     * @param $currentUrl
     * @return json
     */
    public function dateTimePicker($userId, $currentUrl = null)
    {
        $tokens       = $this->authenticateModel->getTokens($userId);
        $accessToken  = $tokens['accessToken'];
        $refreshToken = $tokens['refreshToken'];
        $subId        = $tokens['subId'];

        $cronofy = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $params = [
            "version"     => "1",
            "permissions" => ["managed_availability", "availability"],
            "subs"        => [$subId],
            "origin"      => (!empty($currentUrl)) ? $currentUrl : substr_replace(env('APP_URL'), "", -1),
        ];

        return $cronofy->requestElementToken($params);
    }

    /**
     * Create event in calendar
     * @param Array $params
     * @param Array $inviteUsers
     * @return json
     */
    public function createEvent(array $params, array $inviteUsers = [])
    {
        $tokens       = $this->authenticateModel->getTokens($params['wsId']);
        $accessToken  = $tokens['accessToken'];
        $refreshToken = $tokens['refreshToken'];
        $response     = array();
        $cronofy      = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $calendarIds = $this->cronofyCalendar->getCalendarIds($params['wsId']);
        $start       = (isset($params['notification']['notification']['slot']['start'])) ? $params['notification']['notification']['slot']['start'] : '';
        $end         = (isset($params['notification']['notification']['slot']['end'])) ? $params['notification']['notification']['slot']['end'] : '';
        $wbsDetails  = User::find($params['wsId']);

        if (!empty($calendarIds)) {
            $params = [
                'calendar_id' => $calendarIds[0],
                'event_id'    => $params['eventId'],
                'event_uid'   => $params['schedulingId'], // For update event
                'summary'     => $params['name'],
                'description' => $params['name'],
                'start'       => [
                    "time" => $start,
                    "tzid" => $wbsDetails->timezone,
                ],
                'end'         => [
                    "time" => $end,
                    "tzid" => $wbsDetails->timezone,
                ],
                'location'    => [
                    'description' => $params['name'],
                ],
            ];
            $response = $cronofy->upsertEvent($params);
        }
        return $response;
    }

    /**
     * Custom Availablity Slot
     * @param Array $slot
     * @param Object $user
     * @param String $timezone
     * @return json
     */
    public function customAvailability($targetUnavailable, $user, $company, $serviceId)
    {
        $tokens            = $this->authenticateModel->getTokens($user->id);
        $accessToken       = $tokens['accessToken'];
        $refreshToken      = $tokens['refreshToken'];
        $subId             = $tokens['subId'];
        $response          = [];
        $cronofy           = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $calendarIds    = $this->cronofyCalendar->getCalendarIds($user->id);
        $targetCalendar = [];
        foreach ($calendarIds as $calendar) {
            $tempTargetCalendar = [
                'sub'         => $subId,
                'calendar_id' => $calendar,
            ];
            array_push($targetCalendar, $tempTargetCalendar);
        }
        $services              = Service::where('id', $serviceId)->select('services.name', 'services.session_duration')->first();
        $digitalTherapyDetails = $company->digitalTherapy()->first();
        if (!empty($digitalTherapyDetails)) {
            $duration       = (!empty($services)) ? $services->session_duration : config('cronofy.schedule_duration');
        }
        if (!empty($calendarIds)) {
            $params = [
                'available_periods' => $targetUnavailable,
                'participants'      => [
                    [
                        'members'  => $targetCalendar,
                        "required" => "all",
                    ],
                ],
                'required_duration' => [
                    'minutes' => $duration,
                ],
                "buffer" => [
                    "before" => [
                        "minutes" => config('cronofy.buffer.before'),
                    ],
                    "after" => [
                        "minutes" => config('cronofy.buffer.after'),
                    ],
                ],
            ];
            $response = $cronofy->availability($params);
        }
        return $response;
    }

    /**
     * Availability Rule Remove
     * @param $user
     *
     * @return null
     */
    public function availabilityRuleRemove($user)
    {
        $tokens            = $this->authenticateModel->getTokens($user->id);
        $accessToken       = $tokens['accessToken'];
        $refreshToken      = $tokens['refreshToken'];
        $cronofy           = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $cronofy->deleteAvailabilityRule('default');
    }

    /**
     * Get all event created in calendar 
     * @param $params
     *
     * @return null
     */
    public function getEvents($userId, $params = [])
    {
        $tokens            = $this->authenticateModel->getTokens($userId);
        $accessToken       = $tokens['accessToken'];
        $refreshToken      = $tokens['refreshToken'];
        $cronofy           = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        $result = $cronofy->readEvents($params);

        return $result;
    }

    /**
     * Get all event created in calendar 
     * @param $params
     *
     * @return null
     */
    public function getFreeBuzySlots($userId, $params = [])
    {
        $tokens            = $this->authenticateModel->getTokens($userId);
        $accessToken       = $tokens['accessToken'];
        $refreshToken      = $tokens['refreshToken'];
        $cronofy           = new Cronofy([
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
            "data_center"   => $this->dataCenter,
        ]);

        return $cronofy->freeBusy($params);
    }
}

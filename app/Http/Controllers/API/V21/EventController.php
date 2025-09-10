<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V21;

use App\Http\Collections\V20\EventListCollection;
use App\Http\Controllers\API\V20\EventController as v20EventController;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends v20EventController
{
    /**
     * List all the event based on user company.
     *
     * @param string $type
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $type = 'registered')
    {
        try {
            $user        = $this->user();
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $company     = $user->company()->first();
            $now         = now($timezone)->toDateTimeString();

            $eventRecords = Event::select('events.id', 'event_booking_logs.id as booking_id', 'events.creator_id', 'event_booking_logs.presenter_user_id', 'events.subcategory_id', 'events.name', 'events.location_type', DB::raw('IFNULL(events.capacity, 0) AS capacity'), 'events.duration', DB::raw('concat("<p>",events.description,"</p>",IFNULL(event_booking_logs.notes, "")) as description'), 'event_booking_logs.booking_date', 'event_booking_logs.start_time', 'events.created_at', 'event_booking_logs.meta', \DB::raw("CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}') AS event_date_time"))
                ->join('event_companies', function ($join) use ($company) {
                    $join
                        ->on('event_companies.event_id', '=', 'events.id')
                        ->where('event_companies.company_id', '=', $company->id);
                })
                ->join('event_booking_logs', function ($join) use ($company) {
                    $join
                        ->on('event_booking_logs.event_id', '=', 'events.id')
                        ->where('event_booking_logs.company_id', '=', $company->id);
                });

            if ($type == 'booked') {
                $eventRecords->join('event_registered_users_logs', function ($join) use ($user) {
                    $join
                        ->on('event_registered_users_logs.event_booking_log_id', '=', 'event_booking_logs.id')
                        ->where('event_registered_users_logs.user_id', '=', $user->id)
                        ->where('event_registered_users_logs.is_cancelled', '=', 0);
                });
            } elseif ($type == 'registered') {
                $checkAlreadyRegisterd = $user->registeredEvents()->get()->pluck('event_booking_log_id')->toArray();
                $eventRecords
                    ->whereNotIn('event_booking_logs.id', $checkAlreadyRegisterd)
                    // condition to check if the event is ongoaing and user is not registered then event record should be removed from listing AND in case of seat full events, records should not appear for other users who are not registered
                    ->whereRaw("('{$now}' BETWEEN CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}') AND ADDTIME(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}'), events.duration)) != 1 AND IF(capacity IS NOT NULL AND capacity > 0 AND ((SELECT IFNULL(COUNT(event_registered_users_logs.user_id), 0) FROM event_registered_users_logs WHERE event_registered_users_logs.is_cancelled = 0 AND event_registered_users_logs.event_booking_log_id = event_booking_logs.id) >= capacity), 1, 0) != 1");
            } elseif ($type == 'upcoming') {
                $eventRecords
                    ->leftJoin('event_registered_users_logs', function ($join) use ($user) {
                        $join
                            ->on('event_registered_users_logs.event_booking_log_id', '=', 'event_booking_logs.id')
                            ->where('event_registered_users_logs.user_id', '=', $user->id);
                    })
                    ->addSelect('event_registered_users_logs.is_cancelled')
                    // ->addSelect(DB::raw("((SELECT IFNULL(COUNT(event_registered_users_logs.user_id), 0) FROM event_registered_users_logs WHERE event_registered_users_logs.is_cancelled = 0 AND event_registered_users_logs.event_booking_log_id = event_booking_logs.id) >= capacity) AS seatful"))
                    // ->addSelect(DB::raw("(event_registered_users_logs.is_cancelled IS NOT NULL AND event_registered_users_logs.is_cancelled = 0) AS isregistered"))
                    // ->addSelect(DB::raw("IF((capacity IS NOT NULL AND (event_registered_users_logs.is_cancelled IS NULL OR event_registered_users_logs.is_cancelled = 1)), IF(((SELECT IFNULL(COUNT(event_registered_users_logs.user_id), 0) FROM event_registered_users_logs WHERE event_registered_users_logs.is_cancelled = 0 AND event_registered_users_logs.event_booking_log_id = event_booking_logs.id) >= capacity), 0, 1), 1) AS seatfulflag"))
                    // ->addSelect(DB::raw("IF(('{$now}' BETWEEN CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}') AND ADDTIME(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}'), events.duration) AND (event_registered_users_logs.is_cancelled IS NULL OR event_registered_users_logs.is_cancelled = 1)), 0, 1) AS isongoingflag"))
                    ->where(function ($where) {
                        $where
                            ->whereNull('event_registered_users_logs.is_cancelled')
                            ->orWhere('event_registered_users_logs.is_cancelled', 0);
                    })
                    // condition to check if the event is ongoaing and user is not registered then event record should be removed from listing
                    ->whereRaw("IF(('{$now}' BETWEEN CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}') AND ADDTIME(CONVERT_TZ(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time), '{$appTimezone}', '{$timezone}'), events.duration) AND (event_registered_users_logs.is_cancelled IS NULL OR event_registered_users_logs.is_cancelled = 1)), 0, 1)")
                    // condition to check in case of seat full events, records should not appear for other users who are not registered
                    ->whereRaw("IF((capacity IS NOT NULL AND capacity > 0 AND (event_registered_users_logs.is_cancelled IS NULL OR event_registered_users_logs.is_cancelled = 1)), IF(((SELECT IFNULL(COUNT(event_registered_users_logs.user_id), 0) FROM event_registered_users_logs WHERE event_registered_users_logs.is_cancelled = 0 AND event_registered_users_logs.event_booking_log_id = event_booking_logs.id) >= capacity), 0, 1), 1)");
            }

            // where condition to get an events based on passed month in request
            if (!empty($request->month)) {
                $fromdate = Carbon::createFromFormat('d-m-Y', "01-{$request->month}", $timezone)->startOfMonth()
                    ->setTimezone($appTimezone)->toDateTimeString();
                $todate = Carbon::createFromFormat('d-m-Y', "01-{$request->month}", $timezone)->endOfMonth()
                    ->setTimezone($appTimezone)->toDateTimeString();
                $eventRecords
                    ->whereRaw("TIMESTAMP(CONCAT(event_booking_logs.booking_date, ' ', event_booking_logs.start_time)) BETWEEN '{$fromdate}' AND '{$todate}'");
            }

            $eventRecords = $eventRecords
                ->where('event_booking_logs.status', '4')
                ->where('events.status', '2')
                ->groupBy('event_booking_logs.id')
                ->orderBy('event_date_time')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($eventRecords->count() > 0) {
                return $this->successResponse(new EventListCollection($eventRecords), 'Events Retrieved Successfully');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }
}

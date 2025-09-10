<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V11;

use App\Http\Collections\V11\EventListCollection;
use App\Http\Controllers\Controller;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Event;
use App\Models\EventRegisteredUserLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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
            $user    = $this->user();
            $company = $user->company()->first();

            $eventRecords = Event::select('events.id', 'events.creator_id', 'events.subcategory_id', 'events.name', 'events.location_type', 'events.capacity', 'events.description', 'event_booking_logs.booking_date', 'event_booking_logs.start_time', 'events.created_at')
                ->join('event_companies', function ($join) use ($company) {
                    $join->on('event_companies.event_id', '=', 'events.id')
                        ->where('event_companies.company_id', '=', $company->id);
                })
                ->join('event_booking_logs', function ($join) use ($company) {
                    $join->on('event_booking_logs.event_id', '=', 'events.id')
                        ->where('event_booking_logs.company_id', '=', $company->id);
                });

            if ($type == 'booked') {
                $eventRecords->join('event_registered_users_logs', function ($join) use ($user) {
                    $join
                        ->on('event_registered_users_logs.event_booking_log_id', '=', 'event_booking_logs.id')
                        ->where('event_registered_users_logs.user_id', '=', $user->id)
                        ->where('event_registered_users_logs.is_cancelled', '=', 0);
                });
            } else {
                $checkAlreadyRegisterd = EventRegisteredUserLog::where('user_id', $user->id)->pluck('event_booking_log_id')->toArray();

                $eventRecords->whereNotIn('event_booking_logs.id', $checkAlreadyRegisterd);
            }

            $eventRecords->where('event_booking_logs.status', '4')
                ->where('events.status', '2');

            $eventRecords = $eventRecords->groupBy('events.id')
                ->orderBy('event_booking_logs.booking_date', 'ASC')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($eventRecords->count() > 0) {
                // collect required data and return response
                return $this->successResponse(new EventListCollection($eventRecords), 'Events Retrieved Successfully');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

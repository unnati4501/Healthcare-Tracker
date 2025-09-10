<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V21;

use App\Http\Collections\V21\CounsellorCollection;
use App\Http\Collections\V21\EAPSessionCollection;
use App\Http\Controllers\Controller;
use App\Http\Resources\V21\EAPSessionResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\AppSlide;
use App\Models\Calendly;
use App\Models\ZdTicket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CounsellorController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get EAP onboard slider and counselor details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();
            if (!$company->eap_tab) {
                return $this->notFoundResponse('EAP is disabled for this company.');
            }
            $type          = 'eap';
            $paginateLimit = 5;
            $slideRecords  = AppSlide::where('type', $type)->orderBy("order_priority", "ASC")->paginate($paginateLimit);
            $eapTickets    = ZdTicket::where('user_id', $user->id)
                ->whereIn('status', ['Pending', 'Open', 'New'])
                ->whereNotNull('user_id')
                ->whereNotNull('therapist_id')
                ->orderBy('id', 'DESC')
                ->first();

            $data['eapTickets'] = $eapTickets;
            $data['sliders']    = $slideRecords;

            // Collect required data and return response
            return $this->successResponse(new CounsellorCollection($data), 'Counsellor details get successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get counselor session list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sessionList(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->first();
            if (!$company->eap_tab) {
                return $this->notFoundResponse('EAP is disabled for this company.');
            }
            $calendlySessionDetails = Calendly::select('id', 'name', 'user_id', 'therapist_id', 'event_identifier', 'notes', 'start_time', 'end_time', 'location', 'cancel_url', 'reschedule_url', 'status')
                ->where('user_id', $user->id)
                ->whereNotNull('therapist_id')
                ->orderBy("updated_at", "DESC")
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($calendlySessionDetails->count() > 0) {
                // Collect required data and return response
                return $this->successResponse(new EAPSessionCollection($calendlySessionDetails), 'Session Listed successfully.');
            } else {
                // return empty response
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get counselor session details as per last booked
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sessionDetail(Request $request, Calendly $calendly)
    {
        try {
            $user         = $this->user();
            $appTimezone  = config('app.timezone');
            $userTimeZone = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $company      = $user->company()->first();
            if (!$company->eap_tab) {
                return $this->notFoundResponse('EAP is disabled for this company.');
            }
            if (empty($calendly)) {
                $currentTime     = Carbon::now()->timezone($userTimeZone)->todatetimeString();
                $calendly = Calendly::where('user_id', $user->id)->where(\DB::raw("CONVERT_TZ(start_time, '{$appTimezone}', '{$userTimeZone}')"), ">=", $currentTime)->where("status", "!=", "canceled")->get();
            }

            if ($calendly->exists) {
                // Collect required data and return response
                return $this->successResponse(['data' => new EAPSessionResource($calendly)], 'Session details get successfully.');
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

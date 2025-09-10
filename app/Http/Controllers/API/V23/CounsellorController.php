<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V23;

use App\Http\Collections\V21\CounsellorCollection;
use App\Http\Collections\V23\EAPSessionCollection;
use App\Http\Controllers\API\V22\CounsellorController as v22CounsellorController;
use App\Http\Resources\V23\EAPSessionDetailsResource;
use App\Models\AppSlide;
use App\Models\Calendly;
use App\Models\ZdTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CounsellorController extends v22CounsellorController
{
    /**
     * Get EAP onboard slider and assigned counselor details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->select('companies.id', 'companies.eap_tab')->first();
            if (!$company->eap_tab) {
                return $this->notFoundResponse('EAP is disabled for this company.');
            }

            $currentTime   = now(config('app.timezone'))->todatetimeString();
            $type          = 'eap';
            $paginateLimit = 5;
            $slideRecords  = AppSlide::where('type', $type)->orderBy("order_priority", "ASC")->paginate($paginateLimit);
            $eapTickets    = ZdTicket::where('user_id', $user->id)
                ->whereIn('status', ['Pending', 'Open', 'New'])
                ->whereNotNull('user_id')
                ->whereNotNull('therapist_id')
                ->orderBy('id', 'DESC')
                ->first();

            $showHistory = $user->bookedSessions()
                ->where(function ($query) use ($currentTime) {
                    $query
                        ->whereIn('eap_calendly.status', ['canceled', 'completed'])
                        ->orWhere(function ($subQuery) use ($currentTime) {
                            $subQuery
                                ->where('eap_calendly.status', 'active')
                                ->where('eap_calendly.end_time', '<=', $currentTime);
                        });
                })
                ->count('eap_calendly.id');

            $data = [
                'eapTickets'         => $eapTickets,
                'sliders'            => $slideRecords,
                'showBookingHistory' => ($showHistory > 0),
            ];

            return $this->successResponse(new CounsellorCollection($data), 'Counsellor details get successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }

    /**
     * Get sessions history list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sessionList(Request $request)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->select('companies.id', 'companies.eap_tab')->first();

            // check company has access of eap tab
            if (!$company->eap_tab) {
                return $this->notFoundResponse('EAP is disabled for this company.');
            }

            $appTimezone = config('app.timezone');
            $currentTime = now($appTimezone)->todatetimeString();

            $calendlySessionDetails = $user->bookedSessions()
                ->select(
                    'eap_calendly.id',
                    'eap_calendly.name',
                    'eap_calendly.user_id',
                    'eap_calendly.therapist_id',
                    'eap_calendly.start_time',
                    'eap_calendly.status'
                )
                ->where(function ($query) use ($currentTime) {
                    $query
                        ->whereIn('eap_calendly.status', ['canceled', 'completed'])
                        ->orWhere(function ($subQuery) use ($currentTime) {
                            $subQuery
                                ->where('eap_calendly.status', 'active')
                                ->where('eap_calendly.end_time', '<=', $currentTime);
                        });
                })
                ->orderByDesc('eap_calendly.start_time')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            if ($calendlySessionDetails->count() > 0) {
                return $this->successResponse(
                    new EAPSessionCollection($calendlySessionDetails),
                    'Session Listed successfully.'
                );
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
     * Get session details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sessionDetail(Request $request, Calendly $calendly)
    {
        try {
            $user    = $this->user();
            $company = $user->company()->select('companies.id', 'companies.eap_tab')->first();

            // check company has access of eap tab
            if (!$company->eap_tab) {
                return $this->notFoundResponse('EAP is disabled for this company.');
            }

            $nowInUTC = now(config('app.timezone'))->todatetimeString();

            // Check for upcoming booking if calendly not passed
            if (!$calendly->exists) {
                $calendly = $user->bookedSessions()
                    ->where('end_time', '>=', $nowInUTC)
                    ->where('status', '!=', 'canceled')
                    ->orderByDesc('eap_calendly.id')
                    ->first();
            }

            if (!empty($calendly)) {
                // check if session has been rescheduled then show 404
                if ($calendly->status == 'rescheduled') {
                    return $this->notFoundResponse('This session has been rescheduled.');
                }

                $hasUpComingSession = $user->bookedSessions()
                    ->where('end_time', '>=', $nowInUTC)
                    ->whereNull('cancelled_at')
                    ->count('eap_calendly.id');
                $calendly->hasUpComingSession = (($hasUpComingSession > 0) ? 1 : 0);

                return $this->successResponse([
                    'data' => new EAPSessionDetailsResource($calendly),
                ], 'Session details retrieved successfully.');
            } else {
                return $this->notFoundResponse('No session details found.');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }
}

<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V30;

use App\Http\Controllers\API\V27\CounsellorController as v27CounsellorController;
use App\Http\Collections\V30\CounsellorCollection;
use App\Models\AppSlide;
use App\Models\Calendly;
use App\Models\ZdTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CounsellorController extends v27CounsellorController
{
        /**
     * Get EAP onboard slider and assigned counselor details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->select('companies.id', 'companies.eap_tab')->first();
            $checkAccess = getCompanyPlanAccess($user, 'eap');
            if (!$checkAccess && !$company->eap_tab) {
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
}

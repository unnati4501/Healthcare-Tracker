<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V25;

use App\Http\Controllers\API\V24\CounsellorController as v24CounsellorController;
use App\Models\AppSlide;
use App\Http\Requests\Api\V25\CreateCounsellorCsatRequest;
use App\Http\Resources\V25\EAPSessionDetailsResource;
use App\Models\Calendly;
use App\Models\EapCsatLogs;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CounsellorController extends v24CounsellorController
{

    /**
     * Get session details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sessionDetail(Request $request, Calendly $calendly)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->select('companies.id', 'companies.eap_tab')->first();
            $checkAccess = getCompanyPlanAccess($user, 'eap');

            if (!$checkAccess && !$company->eap_tab) {
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
    /**
     * Submit the EAP Feedback
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitCounsellorCsat(CreateCounsellorCsatRequest $request)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->select('companies.id')->first();
            $calendy     = Calendly::findorFail($request->sessionId, ['id', 'end_time']);
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($user->timezone) ? $user->timezone : $appTimezone);
            $currentTime = now($appTimezone)->todatetimeString();

            $checkAccess = getCompanyPlanAccess($user, 'eap');
            if (!$checkAccess && !$company->eap_tab) {
                return $this->notFoundResponse('EAP is disabled for this company.');
            }

            // check session is exist for logged-in user
            $sessionIsCanceled = $user->bookedSessions()
                ->where(function ($query) {
                    $query->where('status', '=', 'canceled')
                        ->orWhere('status', '=', 'rescheduled');
                })
                ->where('id', '=', $calendy->id)
                ->count('eap_calendly.id');

            if ($sessionIsCanceled > 0) {
                return $this->notFoundResponse("Session has been canceled.");
            }

            // check user has already submitted CSAT
            $alreadySubmitted = $user->eapCsat()
                ->where('eap_calendy_id', $calendy->id)
                ->count('eap_csat_user_logs.id');
            if ($alreadySubmitted > 0) {
                return $this->preConditionsFailedResponse('It seems user has already submitted feedback for the eap.');
            }

            // store CSAT feedback into database
            $insertArray[] = [
                'user_id'        => $user->id,
                'company_id'     => $company->id,
                'eap_calendy_id' => $calendy->id,
                'feedback'       => (!empty($request->feedback) ? $request->feedback : null),
                'feedback_type'  => $request->feedbackType,
            ];
            $stored = EapCsatLogs::insert($insertArray);
            // send success response
            return $this->successResponse([], "Thanks, we really appreciate your feedback.");
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong_try_again'));
        }
    }
}

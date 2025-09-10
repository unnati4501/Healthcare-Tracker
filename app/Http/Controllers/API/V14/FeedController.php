<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V14;

use App\Http\Controllers\API\V12\FeedController as v12FeedController;
use App\Http\Resources\V12\FeedResource;
use App\Models\Feed;
use Illuminate\Http\Request;

class FeedController extends v12FeedController
{
    /**
     * Get feed details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request, Feed $feed)
    {
        try {
            $user      = $this->user();
            $role      = getUserRole();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $company   = $user->company()->first();

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $checkRecords = $feed->feedcompany()->where('company_id', $company->id)->count();
                if ($checkRecords <= 0) {
                    return $this->notFoundResponse('Feed not found');
                }
            }

            if (!is_null($company)) {
                $feed->rewardPortalPointsToUser($user, $company, 'feed');
            }

            return $this->successResponse([
                'data' => new FeedResource($feed),
            ], 'Feed details retrived successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

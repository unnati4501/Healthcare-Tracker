<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V43;

use App\Http\Collections\V18\NotificationCollection;
use App\Http\Controllers\API\V41\NotificationController as v41NotificationController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Notification;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends v41NotificationController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function alertList(Request $request)
    {
        try {
            $user            = $this->user();
            $xDeviceOs       = strtolower($request->header('X-Device-Os', ""));
            $defaultTimeZone = config('app.timezone');
            $today           = now($user->timezone)->toDateTimeString();
            $dayStartFrom    = now($user->timezone)->subDays(7)->todatetimeString();

            $notifaction = $user->notifications()
                ->wherePivot('sent', 1)
                ->whereRaw("CONVERT_TZ(notification_user.sent_on, ?, ?) >= ?",[
                    $defaultTimeZone,$user->timezone,$dayStartFrom
                ])
                ->whereRaw("CONVERT_TZ(notification_user.sent_on, ?, ?) <= ?",[
                    $defaultTimeZone,$user->timezone,$today
                ]);

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $notifaction->where('is_portal', true);
            } else {
                $notifaction->where('is_mobile', true);
            }

            $notifaction = $notifaction->orderByDesc('notification_user.sent_on')
                ->paginate(config('zevolifesettings.datatable.pagination.short'));

            $notifaction->each(function ($value, $key) use ($request) {
                $headers = $request->headers->all();
                $payload = $request->all();

                if ($value->pivot->read == 0) {
                    $version              = config('zevolifesettings.version.api_version');
                    $notificationRequest  = Request::create("api/" . $version . "/notification/read-unread/" . $value->id, 'PUT', $headers, $payload);
                    $notificationResponse = \Route::dispatch($notificationRequest);
                }
            });

            return $this->successResponse(
                ($notifaction->count() > 0) ? new NotificationCollection($notifaction) : ['data' => []],
                ($notifaction->count() > 0) ? 'Notification List retrieved successfully.' : 'No results'
            );
        } catch (\Exception $e) {
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

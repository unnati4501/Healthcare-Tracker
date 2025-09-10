<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Collections\V6\NotificationCollection;
use App\Http\Controllers\API\V5\NotificationController as v5NotificationController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends v5NotificationController
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
            $user = $this->user();

            $defaultTimeZone = config('app.timezone');

            $today        = now($user->timezone)->toDateTimeString();
            $dayStartFrom = now($user->timezone)->subDays(7)->todatetimeString();

            $notifaction = $user->notifications()
                ->wherePivot('sent', 1)
                ->where(\DB::raw("CONVERT_TZ(notification_user.sent_on, '{$defaultTimeZone}', '{$user->timezone}')"), '>=', $dayStartFrom)
                ->where(\DB::raw("CONVERT_TZ(notification_user.sent_on, '{$defaultTimeZone}', '{$user->timezone}')"), '<=', $today)
                ->orderByDesc('notification_user.sent_on')
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

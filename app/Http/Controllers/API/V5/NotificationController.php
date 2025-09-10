<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V5;

use App\Http\Collections\V1\NotificationCollection;
use App\Http\Controllers\API\V1\NotificationController as v5NotificationController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Notification;
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

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function readUnread(Request $request, Notification $notification)
    {
        try {
            \DB::beginTransaction();

            $user = $this->user();

            $pivotExsisting = $user->notifications()->wherePivot('user_id', $user->getKey())->wherePivot('notification_id', $notification->getKey())->first();

            if (!empty($pivotExsisting)) {
                $is_read = $pivotExsisting->pivot->read;

                $pivotExsisting->pivot->read = ($is_read == 1) ? 0 : 1;

                if ($is_read == 1) {
                    $pivotExsisting->pivot->read_at = null;
                } else {
                    $pivotExsisting->pivot->read_at = now()->toDateTimeString();
                }

                $pivotExsisting->pivot->save();

                $returnData['unread_notifications_count'] = $user->userUnreadNotifactionCount();

                \DB::commit();
                if ($is_read == 1) {
                    return $this->successResponse(['data' => $returnData], "Notifications marked as unread successfully.");
                } else {
                    return $this->successResponse(['data' => $returnData], "Notifications marked as read successfully.");
                }
            } else {
                \DB::rollback();
                return $this->notFoundResponse("Unable to find requested notification for user");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, Notification $notification)
    {
        try {
            \DB::beginTransaction();

            $user = $this->user();

            $pivotExsisting = $user->notifications()->wherePivot('user_id', $user->getKey())->wherePivot('notification_id', $notification->getKey())->first();

            if (!empty($pivotExsisting)) {
                $user->notifications()->detach($notification);

                $returnData['unread_notifications_count'] = $user->userUnreadNotifactionCount();

                \DB::commit();
                return $this->successResponse(['data' => $returnData], trans('api_messages.notification.delete'));
            } else {
                \DB::rollback();
                return $this->notFoundResponse("Unable to find requested notification for user");
            }
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

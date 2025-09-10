<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V27;

use App\Http\Collections\V18\NotificationCollection;
use App\Http\Controllers\API\V18\NotificationController as v18NotificationController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Notification;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends v18NotificationController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Delete the notifications for user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $notificationId)
    {
        try {
            \DB::beginTransaction();
            $user = $this->user();
            $ids = [];
            $pivotExsisting = $user->notifications()->wherePivot('user_id', $user->getKey());
            if ($notificationId != 0) {
                $pivotExsisting = $pivotExsisting->wherePivot('notification_id', $notificationId)->first();
            } else {
                $pivotExsisting = $pivotExsisting->get();
                foreach ($pivotExsisting as $key => $val) {
                    $ids[] = $val->id;
                }
            }
            if (!empty($pivotExsisting)) {
                if (!empty($ids)) {
                    $user->notifications()->detach($ids);
                } else {
                    $user->notifications()->detach($notificationId);
                }
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

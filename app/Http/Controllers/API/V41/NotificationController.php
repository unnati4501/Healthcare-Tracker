<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V41;

use App\Http\Collections\V18\NotificationCollection;
use App\Http\Controllers\API\V27\NotificationController as v27NotificationController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Notification;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends v27NotificationController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Unread Notification Count
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadNotificationCount(Request $request)
    {
        try {
            $user = $this->user();
            $returnData['unread_notifications_count'] = $user->userUnreadNotifactionCount();
            return $this->successResponse(['data' => $returnData], trans('api_messages.notification.successfully'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}

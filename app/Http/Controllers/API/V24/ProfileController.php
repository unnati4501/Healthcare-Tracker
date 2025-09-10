<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V24;

use App\Http\Collections\V1\UserNotificationSettingCollection;
use App\Http\Controllers\API\V20\ProfileController as v20ProfileController;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends v20ProfileController
{
    /**
     * Update user wise notification settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editNotificationSettings(Request $request)
    {
        try {
            $nowInUTC = now(config('app.timezone'))->toDateTimeString();
            \DB::beginTransaction();
            $user    = $this->user();
            $modules = $request->all();
            if (!empty($modules)) {
                foreach ($modules as $module => $flag) {
                    $setting = $user->notificationSettings()->updateOrCreate([
                        'user_id' => $user->getKey(),
                        'module'  => strtolower($module),
                    ], [
                        'flag' => (bool) $flag,
                    ]);
                }
            }

            // check if event toggle is being set to false/true then update push to 0/1 respectively for scheduled and unsent notifications those are controlled using event toggle.
            if (isset($modules['events'])) {
                Notification::where('tag', 'new-eap')
                    ->where('type', 'Manual')
                    ->where('creator_id', $user->id)
                    ->where('scheduled_at', '>=', $nowInUTC)
                    ->update([
                        'push' => (int) $modules['events'],
                    ]);
            }

            \DB::commit();

            return $this->successResponse(
                new UserNotificationSettingCollection($user->notificationSettings),
                trans('api_messages.profile.notification-setting')
            );
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            if ($e instanceof AuthenticationException) {
                return $this->unauthorizedResponse(trans('labels.common_title.something_wrong'));
            }
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }
}

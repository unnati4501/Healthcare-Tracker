<?php declare (strict_types = 1);

namespace App\Observers;

use App\Models\BroadcastMessage;
use App\Models\Notification;

/**
 * Class EventObserver
 *
 * @package App\Observers
 */
class BroadcastMessageObserver
{
    /**
     * Handle the user team "deleted" event.
     *
     * @param  App\Models\BroadcastMessage  $message
     * @return void
     */
    public function deleted(BroadcastMessage $message)
    {
        $deepLink = __(config('zevolifesettings.deeplink_uri.group'), [
            'id' => $message->group_id,
        ]);
        Notification::where('tag', 'broadcast')
            ->where(function ($query) use ($deepLink) {
                $query
                    ->where('deep_link_uri', 'LIKE', $deepLink . '/%')
                    ->orWhere('deep_link_uri', 'LIKE', $deepLink);
            })
            ->delete();
    }
}

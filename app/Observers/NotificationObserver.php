<?php
declare(strict_types = 1);

namespace App\Observers;

use App\Models\Notification;

/**
 * Class NotificationObserver
 *
 * @package App\Observers
 */
class NotificationObserver
{
    /**
     * @param Notification $notification
     */
    public function creating()
    {
        return true;
    }

    /**
     * @param Notification $notification
     */
    public function created()
    {
        return true;
    }
}

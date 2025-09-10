<?php declare (strict_types = 1);

namespace App\Observers;

use App\Models\Event;
use App\Models\Notification;

/**
 * Class EventObserver
 *
 * @package App\Observers
 */
class EventObserver
{
    /**
     * @param Event $event
     */
    public function created(Event $event)
    {
        $deepLinkURI = "zevolife://zevo/event/" . $event->getKey();
        $event->update(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param Event $event
     */
    public function deleted(Event $event)
    {
        $deepLinkURI = "zevolife://zevo/event/" . $event->getKey();
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
    }
}

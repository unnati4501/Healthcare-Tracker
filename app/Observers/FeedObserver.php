<?php
declare(strict_types = 1);

namespace App\Observers;

use App\Models\Notification;
use App\Models\Feed;

/**
 * Class FeedObserver
 *
 * @package App\Observers
 */
class FeedObserver
{
    /**
     * @param Feed $feed
     */
    public function creating(Feed $feed)
    {
        $deepLinkURI = "zevolife://zevo/feed/".$feed->getKey();
        $feed->forceFill(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param Feed $feed
     */
    public function created(Feed $feed)
    {
        $deepLinkURI = "zevolife://zevo/feed/".$feed->getKey();
        $feed->update(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param Feed $feed
     */
    public function deleted(Feed $feed)
    {
        $deepLinkURI = "zevolife://zevo/feed/" . $feed->getKey();
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
    }
}

<?php
declare (strict_types = 1);

namespace App\Observers;

use App\Models\Podcast;
use App\Models\Notification;

/**
 * Class PodcastObserver
 *
 * @package App\Observers
 */
class PodcastObserver
{
    /**
     * @param MeditationTrack $track
     */
    public function deleted(Podcast $podcast)
    {
        $deepLinkURI = "zevolife://zevo/podcast/" . $podcast->getKey() . '/' . $podcast->sub_category_id;
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
    }
}

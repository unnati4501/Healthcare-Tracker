<?php
declare (strict_types = 1);

namespace App\Observers;

use App\Models\MeditationTrack;
use App\Models\Notification;

/**
 * Class TrackObserver
 *
 * @package App\Observers
 */
class TrackObserver
{
    /**
     * @param MeditationTrack $track
     */
    public function deleted(MeditationTrack $track)
    {
        $deepLinkURI = "zevolife://zevo/meditation-track/" . $track->getKey() . '/' . $track->sub_category_id;
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
    }
}

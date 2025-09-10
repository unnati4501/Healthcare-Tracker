<?php declare (strict_types = 1);

namespace App\Observers;

use App\Models\Notification;
use App\Models\Webinar;

/**
 * Class WebinarObserver
 *
 * @package App\Observers
 */
class WebinarObserver
{
    /**
     * @param Webinar $webinar
     */
    public function created(Webinar $webinar)
    {
        $deepLinkURI = "zevolife://zevo/webinar/" . $webinar->getKey();
        $webinar->update(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param Webinar $webinar
     */
    public function deleted(Webinar $webinar)
    {
        $deepLinkURI = "zevolife://zevo/webinar/" . $webinar->getKey();
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
    }
}

<?php declare (strict_types = 1);

namespace App\Observers;

use App\Models\Notification;
use App\Models\EAP;

/**
 * Class RecipeObserver
 *
 * @package App\Observers
 */
class EapObserver
{
    /**
     * @param EAP $eap
     */
    public function created(EAP $eap)
    {
        $deepLinkURI = "zevolife://zevo/eap/" . $eap->getKey();
        $eap->update(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param EAP $eap
     */
    public function deleted(EAP $eap)
    {
        $deepLinkURI = "zevolife://zevo/eap/" . $eap->getKey();
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
    }
}

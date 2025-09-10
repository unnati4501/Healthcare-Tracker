<?php
declare (strict_types = 1);

namespace App\Observers;

use App\Models\NpsProject;
use App\Models\Notification;

/**
 * Class NpsProjectObserver
 *
 * @package App\Observers
 */
class NpsProjectObserver
{

    /**
     * @param NpsProject $npsProject
     */
    public function created(NpsProject $npsProject)
    {
        if ($npsProject->type == "public") {
            $user        = \Auth::user();
            $userCompany = $user->company()->first();

            $key = encrypt($npsProject->getKey());
            $surveyLink = route('projectSurveyResponse', $key);

            if (!empty($userCompany) && $userCompany->is_branding) {
                $brandingData       = getBrandingData($userCompany->id);
                if (!empty($brandingData->sub_domain)) {
                    $surveyLink = getBrandingUrl($surveyLink, $brandingData->sub_domain);
                }
            }

            $npsProject->update(['public_survey_url' => $surveyLink]);
        }
    }
}

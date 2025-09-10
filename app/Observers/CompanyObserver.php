<?php
declare(strict_types = 1);

namespace App\Observers;

use App\Models\Company;

/**
 * Class CompanyObserver
 *
 * @package App\Observers
 */
class CompanyObserver
{
    /**
     * @param Team $team
     */
    public function creating(Company $company)
    {
        if (\blank($company->getAttributeValue('code'))) {
            $company->forceFill(['code' => $company->createUniqueCode()]);
        }
    }
}

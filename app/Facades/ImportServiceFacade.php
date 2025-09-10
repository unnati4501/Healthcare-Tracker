<?php
declare(strict_types = 1);

namespace App\Facades;

use App\Services\ImportService;
use Illuminate\Support\Facades\Facade;

/**
 * Class ChallengeServiceFacade
 *
 * @package App\Facades
 */
class ImportServiceFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        return ImportService::class;
    }
}

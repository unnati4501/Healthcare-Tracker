<?php

namespace App\Interfaces;

/**
 * Class AuditLogRepositoryInterface
 */
interface AuditLogRepositoryInterface
{
    /**
     * Log method for datadog
     */
    public function created();
}

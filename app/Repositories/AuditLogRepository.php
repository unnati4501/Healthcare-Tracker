<?php

namespace App\Repositories;

use App\Interfaces\AuditLogRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

/**
 * Class AuditLogRepository
 */
class AuditLogRepository implements AuditLogRepositoryInterface
{
    /**
     * @var $comment
     */
    protected $comment;

    /**
     * @var $log
     */
    protected $log;

    /**
     * contructor to initialize model object
     */
    public function __construct()
    {
        // create a log channel
        $this->log = new Logger('useractivity');

        // create a Json formatter
        $formatter = new JsonFormatter();

        if (!file_exists(storage_path('logs/user-activity.log'))) {
            touch(storage_path('logs/user-activity.log'));
        }

        // create a handler
        $stream = new StreamHandler(storage_path('logs/user-activity.log'), Logger::DEBUG);
        $stream->setFormatter($formatter);

        // bind
        $this->log->pushHandler($stream);
    }

    /**
     * @return string
     */
    public function created($message = 'Adding a new user', $data = array('username' => 'Test'))
    {
        $this->log->info($message, $data);
    }

     /**
     * @return string
     */
    public static function createdstatic($message = 'Adding a new user', $data = array('username' => 'Test'))
    {
        $this->log->info($message, $data);
    }
}
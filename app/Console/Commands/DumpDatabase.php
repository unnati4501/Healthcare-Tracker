<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Process;

class DumpDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mysql:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create current mysql back into storage/backup folder';

    /**
     * Execute the console command.
     * (1) Generate sql back up
     * (2) Upload to digital ocean
     * (3) Send email to administrator
     *
     * @return mixed
     */
    public function handle()
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        $dateTime             = Carbon::now()->format('Y-m-d');
        $appEnvironment       = app()->environment();

        try {
            if ($appEnvironment == 'local') {
                $message                  = 'Local environment does not support this functionality.';
                $cronData['is_exception'] = 1;
                $cronData['log_desc']     = $message;
                cronlog($cronData, 1);
                throw new \Exception($message);
            }
            $backUpDirectory = storage_path("backup/mysql");
            if (!File::isDirectory($backUpDirectory)) {
                File::makeDirectory($backUpDirectory, 0777, true, true);
            }
            if (!File::isDirectory($backUpDirectory)) {
                File::makeDirectory($backUpDirectory, 0777, true, true);
            }
            $fileNameWithPath = sprintf("%s/backup-%s.sql.gz", $backUpDirectory, $dateTime);
            $databaseConfig = config('database.connections.mysql.database');

            $commandString = 'mysqldump -h'.config('database.connections.mysql.host').' -u'.config('database.connections.mysql.username').' -p'.config('database.connections.mysql.password').' '.$databaseConfig.' --ignore-table='.$databaseConfig.'.api_logs --ignore-table='.$databaseConfig.'.cron_logs --ignore-table='.$databaseConfig.'.tracker_logs --ignore-table='.$databaseConfig.'.telescope_entries --ignore-table='.$databaseConfig.'.telescope_entries_tags --ignore-table='.$databaseConfig.'.telescope_monitoring --ignore-table='.$databaseConfig.'.challenge_user_exercise_history --ignore-table='.$databaseConfig.'.challenge_user_inspire_history --ignore-table='.$databaseConfig.'.challenge_user_steps_history --routines | gzip > '.$fileNameWithPath;
            
            $process = Process::run($commandString);
            $process->output();

            if ($process->successful() && file_exists($fileNameWithPath) && is_readable($fileNameWithPath)) {
                $disk_name = config('medialibrary.disk_name');
                if ($disk_name == "spaces") {
                    $bucket = uploadFileToSpaces(file_get_contents($fileNameWithPath), 'BackUp/mysql/' . $appEnvironment . "/backup-$dateTime.sql.gz");
                    if (null != $bucket && is_string($bucket->get('ObjectURL'))) {
                        unlink($fileNameWithPath);
                    }
                } elseif ($disk_name == "azure") {
                    $bucket = uploadeFileToBlob(file_get_contents($fileNameWithPath), "backup-$dateTime.sql.gz", 'BackUp/mysql/');
                    if ($bucket) {
                        unlink($fileNameWithPath);
                    }
                }
            }
            $message = 'The backup has been processed successfully.';
            $this->info($message);
            cronlog($cronData, 1);
        } catch (ProcessFailedException $exception) {
            // Send email to administrator
            $data = [
                'subject' => sprintf('Database backup fail for %s on %s', $appEnvironment, $dateTime),
                'data'    => [
                    'status'      => 'Fail',
                    'date'        => $dateTime,
                    'environment' => $appEnvironment,
                    'exception'   => $exception->getMessage(),
                    'line'        => $exception->getLine(),
                    'file'        => $exception->getFile(),
                ],
            ];
            $cronData['data'] = $data;
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}

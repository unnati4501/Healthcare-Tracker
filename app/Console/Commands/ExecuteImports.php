<?php
declare (strict_types = 1);

namespace App\Console\Commands;

use App\Facades\ImportServiceFacade;
use App\Models\FileImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class RunFileImports
 *
 * @package App\Console\Commands
 */
class ExecuteImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fileimport:executeimports {company?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will execute all imports for all companies based on conditions';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];

        cronlog($cronData);

        try {
            $dataToProcess = FileImport::where('in_process', 0)
                ->where('is_processed', 0)
                ->where('is_imported_successfully', 0)
                ->whereNull('process_started_at')
                ->get();

            foreach ($dataToProcess as $value) {

                if (!empty($value)) {
                    $value->update(
                        [
                            'in_process'         => 1,
                            'process_started_at' => now()->toDateTimeString(),
                        ]
                    );
                    ImportServiceFacade::performValidationOnFile($value);
                }
            }
            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}

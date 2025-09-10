<?php

namespace App\Jobs;

use App\Events\ChallengeDetailExportEvent;
use App\Models\Company;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportChallengeDetailJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $challenge;
	public $user;
	public $payload;
	public $participants;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct($challenge, $user, $payload, $participants) {
		$this->queue        = 'mail';
		$this->challenge    = $challenge;
		$this->user         = $user;
		$this->payload      = $payload;
		$this->participants = $participants;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle() {
		try {
			$role           = getUserRole($this->user);
            $route          = (!empty($this->payload['exportRoute']) ? $this->payload['exportRoute'] : 'interCompanyChallenges');
			$timezone       = (!empty($this->user->timezone) ? $this->user->timezone : config('app.timezone'));
			$dateTimeString = Carbon::now()->toDateTimeString();
			$challengeTitle = str_replace(' ', '-', $this->challenge->title);
			$startDate      = Carbon::parse($this->challenge->start_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_date'));
			$startTime      = Carbon::parse($this->challenge->start_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_time'));
			$endDate        = Carbon::parse($this->challenge->end_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_date'));
			$endTime        = Carbon::parse($this->challenge->end_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_time'));
			$fileName       = $challengeTitle . '_' . $dateTimeString . '.xlsx';

			$spreadsheet    = new Spreadsheet();
			$sheet          = $spreadsheet->getActiveSheet();
			$index          = 2;

			$sheet->setCellValue('A' . $index, 'Challenge Name');
			$sheet->setCellValue('B' . $index, $this->challenge->title);
			$index += 1;

			if ($role->group == 'company') {
				$company = $this->user->company()->first();
				$sheet->setCellValue('A' . $index, 'Company Name');
				$sheet->setCellValue('B' . $index, $company->name);
				$index += 1;
			}

			$sheet->setCellValue('A' . $index, 'Start Date/ Time');
			$sheet->setCellValue('B' . $index, $startDate . ' ' . $startTime);
			$index += 1;

			$sheet->setCellValue('A' . $index, 'End Date/ Time');
			$sheet->setCellValue('B' . $index, $endDate . ' ' . $endTime);
			$index += 2;

			$sheet->setCellValue('A' . $index, 'Participants');
			$sheet->setCellValue('B' . $index, $this->payload['totalmembers']);
			$index += 1;
            
            if($route != 'challenges') {
                $sheet->setCellValue('A' . $index, 'Teams');
                $sheet->setCellValue('B' . $index, $this->payload['totalteams']);
                $index += 1;
                
                if($route == 'interCompanyChallenges') {
                    $sheet->setCellValue('A' . $index, 'Companies');
                    $sheet->setCellValue('B' . $index, $this->payload['totalcompanies']);
                    $index += 1;
                }
            }

            $index += 1;
			/*******  Company Participants Data  ********/
            if(!empty($this->participants['company']['list'])){
                $sheet->setCellValue('A' . $index, 'Company Ranking');
                $index += 1;
                $companysheetTitle = [
                    'Rank',
                    'Company Name',
                    'Points',
                ];

                $sheet->fromArray($companysheetTitle, null, 'A' . $index);
                $index = $index + 1;

                $companyChallengeType = !empty($this->participants['company']['challenge_type']) ? $this->participants['company']['challenge_type'] : null;
                foreach ($this->participants['company']['list'] as $value) {
                    if (!empty($companyChallengeType) && $companyChallengeType == 'upcoming') {
                        $companyName = (!empty($value)) ? $value['name'] : 'Deleted';
                    } else {
                        $company     = Company::find($value['company_id']);
                        $companyName = (!empty($company)) ? $company->name : 'Deleted';
                    }

                    $tempArray = [
                        !empty($value['rank']) ? $value['rank'] : '0',
                        $companyName,
                        !empty($value['points']) ? number_format((float) $value['points'], 1, '.', '') : '0',
                    ];
                    $sheet->fromArray($tempArray, null, 'A' . $index);
                    $index = $index + 1;
                }
                $index += 2;
            }
			

			/*******  Team Participants Data  ********/
            if(!empty($this->participants['team']['list'])){
                $sheet->setCellValue('A' . $index, 'Team Ranking');
                $index += 1;
                $teamsheetTitle = [
                    'Rank',
                    'Team Name',
                    'Points',
                ];

                $challengeType = !empty($this->participants['team']['challenge_type']) ? $this->participants['team']['challenge_type'] : null;
                $sheet->fromArray($teamsheetTitle, null, 'A' . $index);
                $index = $index + 1;
                foreach ($this->participants['team']['list'] as $value) {
                    if (!empty($challengeType) && $challengeType == 'upcoming') {
                        $teamName   = (!empty($value)) ? $value['name'] : 'Deleted';
                    } else {
                        $team       = Team::find($value['team_id']);
                        $teamName   = (!empty($team)) ? $value['participant_name'] : 'Deleted';
                    }
                    $tempArray = [
                        !empty($value['rank']) ? $value['rank'] : '0',
                        $teamName,
                        !empty($value['points']) ? number_format((float) $value['points'], 1, '.', '') : '0',
                    ];
                    $sheet->fromArray($tempArray, null, 'A' . $index);
                    $index = $index + 1;
                }
                $index += 2;
            }

			/*******   Individual Participants Data  ********/
            if(!empty($this->participants['members']['list'])){
                $sheet->setCellValue('A' . $index, 'Individual Ranking');
                $index += 1;
                $indheetTitle = [
                    'Rank',
                    'Individual Name',
                    'Points',
                ];

                $sheet->fromArray($indheetTitle, null, 'A' . $index);
                $index = $index + 1;
                $indchallengeType = !empty($this->participants['members']['challenge_type']) ? $this->participants['members']['challenge_type'] : null;

                foreach ($this->participants['members']['list'] as $value) {
                    if (!empty($indchallengeType) && $indchallengeType == 'upcoming') {
                        $userName = (!empty($value['participant_name'])) ? $value['participant_name'] : 'Deleted';
                    } else {
                        $userName = (!empty($value['user'])) ? $value['participant_name'] : 'Deleted';
                    }
                    $tempArray = [
                        !empty($value['rank']) ? $value['rank'] : '0',
                        $userName,
                        !empty($value['points']) ? $value['points'] : '0',
                    ];
                    $sheet->fromArray($tempArray, null, 'A' . $index);
                    $index = $index + 1;
                }
            }

			$sheet->getColumnDimension('A')->setAutoSize(true);
			$sheet->getColumnDimension('B')->setAutoSize(false);
			$sheet->getColumnDimension('B')->setWidth(30);

			$writer     = new Xlsx($spreadsheet);
			$temp_file  = tempnam(sys_get_temp_dir(), $fileName);
			$source     = fopen($temp_file, 'rb');
			$writer->save($temp_file);

			$root       = config("filesystems.disks.spaces.root");
			$foldername = config('zevolifesettings.excelfolderpath');

			$uploaded = uploadFileToSpaces($source, "{$root}/{$foldername}/{$fileName}", "public");
			if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
				$url        = $uploaded->get('ObjectURL');
				$uploaded   = true;
			}

			if ($uploaded) {
				event(new ChallengeDetailExportEvent($this->user, $this->challenge, $url, $this->payload, $fileName));
				return true;
			}
		} catch (\Exception $exception) {
			Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
		}
	}
}

<?php

namespace App\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\TeamLocation;
use Illuminate\Support\Facades\Cache;
use App\Events\UserRegisterEvent;
use App\Models\User;
use App\Models\CompanyModerator;

class SpContentAssignToCompanyJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $masterclassCompany;
    public $meditationCompanyInput;
    public $webinarCompanyInput;
    public $feedCompanyInput;
    public $podcastCompanyInput;
    public $recipeCompanyInput;

    public $companyId;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($masterclassCompany, $meditationCompanyInput, $webinarCompanyInput, $feedCompanyInput, $podcastCompanyInput, $recipeCompanyInput, $companyId)
    {
        $this->masterclassCompany = $masterclassCompany;
        $this->meditationCompanyInput = $meditationCompanyInput;
        $this->webinarCompanyInput = $webinarCompanyInput;
        $this->feedCompanyInput = $feedCompanyInput;
        $this->podcastCompanyInput = $podcastCompanyInput;
        $this->recipeCompanyInput = $recipeCompanyInput;
        $this->companyId = $companyId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
           
            if (!empty($this->masterclassCompany)) {
                foreach (array_chunk($this->masterclassCompany, 100) as $masterclassCompany) {
                    DB::table('masterclass_company')->insert($masterclassCompany);
                }
            }
            if (!empty($this->meditationCompanyInput)) {
                foreach (array_chunk($this->meditationCompanyInput, 100) as $meditationCompany) {
                    DB::table('meditation_tracks_company')->insert($meditationCompany);
                }
            }
            if (!empty($this->webinarCompanyInput)) {
                foreach (array_chunk($this->webinarCompanyInput, 100) as $webinarCompany) {
                    DB::table('webinar_company')->insert($webinarCompany);
                }
            }
            if (!empty($this->feedCompanyInput)) {
                foreach (array_chunk($this->feedCompanyInput, 100) as $feedCompany) {
                    DB::table('feed_company')->insert($feedCompany);
                }
            }
            if (!empty($this->recipeCompanyInput)) {
                foreach (array_chunk($this->recipeCompanyInput, 5000) as $recipeCompany) {
                    DB::table('recipe_company')->insert($recipeCompany);
                }
            }
            if (!empty($this->podcastCompanyInput)) {
                foreach (array_chunk($this->podcastCompanyInput, 5000) as $podcastCompany) {
                    DB::table('podcast_company')->insert($podcastCompany);
                }
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}

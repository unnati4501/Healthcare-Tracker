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

class SpContentAssignFromCompanyJob implements ShouldQueue
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
                DB::table('masterclass_company')->where('company_id', $this->companyId)->delete();
            }
            if (!empty($this->meditationCompanyInput)) {
                DB::table('meditation_tracks_company')->where('company_id', $this->companyId)->delete();
            }
            if (!empty($this->webinarCompanyInput)) {
                DB::table('webinar_company')->where('company_id', $this->companyId)->delete();
            }
            if (!empty($this->feedCompanyInput)) {
                DB::table('feed_company')->where('company_id', $this->companyId)->delete();
            }
            if (!empty($this->recipeCompanyInput)) {
                DB::table('recipe_company')->where('company_id', $this->companyId)->delete();
            }
            if (!empty($this->podcastCompanyInput)) {
                DB::table('podcast_company')->where('company_id', $this->companyId)->delete();
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}

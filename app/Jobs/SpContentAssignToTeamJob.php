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

class SpContentAssignToTeamJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $masterclassTeamInput;
    public $meditationTeamInput;
    public $webinarTeamInput;
    public $feedTeamInput;
    public $podcastTeamInput;
    public $recipeTeamInput;

    public $teamLocation;

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
    public function __construct($masterclassTeamInput, $meditationTeamInput, $webinarTeamInput, $feedTeamInput, $podcastTeamInput, $recipeTeamInput, $teamLocation)
    {
        $this->masterclassTeamInput = $masterclassTeamInput;
        $this->meditationTeamInput = $meditationTeamInput;
        $this->webinarTeamInput = $webinarTeamInput;
        $this->feedTeamInput = $feedTeamInput;
        $this->podcastTeamInput = $podcastTeamInput;
        $this->recipeTeamInput = $recipeTeamInput;
        $this->teamLocation = $teamLocation;
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
            
            if (!empty($this->masterclassTeamInput)) {
                foreach (array_chunk($this->masterclassTeamInput, 100) as $masterclassTeam) {
                    DB::table('masterclass_team')->insert($masterclassTeam);
                }
            }
            if (!empty($this->meditationTeamInput)) {
                foreach (array_chunk($this->meditationTeamInput, 100) as $meditationTeam) {
                    DB::table('meditation_tracks_team')->insert($meditationTeam);
                }
            }
            if (!empty($this->webinarTeamInput)) {
                foreach (array_chunk($this->webinarTeamInput, 100) as $webinarTeam) {
                    DB::table('webinar_team')->insert($webinarTeam);
                }
            }
            if (!empty($this->feedTeamInput)) {
                foreach (array_chunk($this->feedTeamInput, 1000) as $feedTeam) {
                    DB::table('feed_team')->insert($feedTeam);
                }
            }
            if (!empty($this->recipeTeamInput)) {
                foreach (array_chunk($this->recipeTeamInput, 1000) as $recipeTeam) {
                    DB::table('recipe_team')->insert($recipeTeam);
                }
            }
            if (!empty($this->podcastTeamInput)) {
                foreach (array_chunk($this->podcastTeamInput, 1000) as $podcastTeam) {
                    DB::table('podcast_team')->insert($podcastTeam);
                }
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}

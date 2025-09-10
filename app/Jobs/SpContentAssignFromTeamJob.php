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

class SpContentAssignFromTeamJob implements ShouldQueue
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
                DB::table('masterclass_team')->whereIn('team_id', $this->teamLocation)->delete();
            }
            if (!empty($this->meditationTeamInput)) {
                DB::table('meditation_tracks_team')->whereIn('team_id', $this->teamLocation)->delete();
            }
            if (!empty($this->webinarTeamInput)) {
                DB::table('webinar_team')->whereIn('team_id', $this->teamLocation)->delete();
            }
            if (!empty($this->feedTeamInput)) {
                DB::table('feed_team')->whereIn('team_id', $this->teamLocation)->delete();
            }
            if (!empty($this->recipeTeamInput)) {
                DB::table('recipe_team')->whereIn('team_id', $this->teamLocation)->delete();
            }
            if (!empty($this->podcastTeamInput)) {
                DB::table('podcast_team')->whereIn('team_id', $this->teamLocation)->delete();
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}

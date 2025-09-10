<?php
namespace Database\Seeders;

use App\Models\Course;
use App\Models\Feed;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConvertExistingHCUsersToHCRole extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            // find users with HC Role
            $usersWithHCRole = User::with('roles')
                ->whereHas('roles', function ($query) {
                    $query->where('roles.default', 1)
                        ->where('roles.slug', 'health_coach');
                })
                ->pluck('users.id')
                ->toArray();

            // update all other user to non HC users
            User::whereNotIn('id', (!empty($usersWithHCRole) ? $usersWithHCRole : []))->update(['is_coach' => 0]);

            // set ZSA as author of every masterclass
            Course::where('id', '>', 0)->update(['creator_id' => 1]);

            // set ZSA as author of every meditation tracks
            MeditationTrack::where('id', '>', 0)->update(['coach_id' => 1]);

            // For feed
            // set ZSA as author of feed which comapny id is null means that feed is added by SA
            Feed::whereNull('company_id')->update(['creator_id' => 1]);

            // Now find feeds which company id isn't null measn those are added by CA so update first moderator of that company as author
            $feedCompany = Feed::whereNotNull('company_id')->pluck('company_id')->toArray();
            if (!empty($feedCompany)) {
                $feedCompany = implode(",", array_values($feedCompany));
                // here we find feed companies and first moderator of that company
                $feedCompanyWithModerator = DB::select("SELECT `companies`.`id`, (SELECT `company_moderator`.`user_id` FROM `company_moderator` WHERE `company_moderator`.`company_id` = `companies`.`id` ORDER BY `company_moderator`.`user_id` ASC LIMIT 1) as moderator FROM `companies` WHERE  FIND_IN_SET(`companies`.`id`, ?)", [$feedCompany]);
                $feedCompanyWithModerator = collect($feedCompanyWithModerator)->pluck('moderator', 'id')->toArray();
                // now we have moderator of each company so now assign moderator as author to each feed thier company
                foreach ($feedCompanyWithModerator as $company_id => $moderator) {
                    Feed::where('company_id', $company_id)->update(['creator_id' => $moderator]);
                }
            }

            // For recipe
            // set ZSA as author of recipe which comapny id is null means that recipe is added by SA
            Recipe::whereNull('company_id')->update(['creator_id' => 1, 'chef_id' => 1]);
        } catch (\Exception $e) {
            $this->command->error($e);
        }
    }
}

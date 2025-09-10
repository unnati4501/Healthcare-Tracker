<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddIndexingForIntercompanyChallenge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE INDEX index_rules_user_exercise ON `user_exercise` (`deleted_at`, `user_id`, `start_date`, `end_date`)");
        DB::statement("CREATE INDEX index_rules_second_user_exercise ON `user_exercise` (`exercise_id`, `created_at`, `updated_at`)");
        DB::statement("CREATE INDEX index_rules_user_step ON `user_step` ( `user_id`, `log_date`)");
        DB::statement("CREATE INDEX index_rules_user_listened_tracks ON `user_listened_tracks` (`user_id`, `meditation_track_id`, `created_at`, `updated_at`)");
        DB::statement("CREATE INDEX index_rules_challenge_history ON `challenge_history` (`challenge_id`, `start_date`, `end_date`)");
        DB::statement("CREATE INDEX index_rules_freezed_challenge_participents ON `freezed_challenge_participents` ( `challenge_id`, `user_id`, `team_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_second_freezed_challenge_participents ON `freezed_challenge_participents` ( `challenge_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_third_freezed_challenge_participents ON `freezed_challenge_participents` ( `challenge_id`, `user_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_four_freezed_challenge_participents ON `freezed_challenge_participents` ( `challenge_id`, `team_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_fifth_freezed_challenge_participents ON `freezed_challenge_participents` ( `user_id`, `team_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_freezed_team_challenge_participents ON `freezed_team_challenge_participents` ( `challenge_id`, `user_id`, `team_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_one_freezed_team_challenge_participents ON `freezed_team_challenge_participents` ( `challenge_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_two_freezed_team_challenge_participents ON `freezed_team_challenge_participents` ( `challenge_id`, `user_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_three_freezed_team_challenge_participents ON `freezed_team_challenge_participents` ( `challenge_id`, `team_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_four_freezed_team_challenge_participents ON `freezed_team_challenge_participents` ( `user_id`, `team_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_freezed_challenge_steps ON `freezed_challenge_steps` ( `challenge_id`, `user_id`, `log_date`)");
        DB::statement("CREATE INDEX index_rules_one_freezed_challenge_steps ON `freezed_challenge_steps` ( `challenge_id`, `log_date`)");
        DB::statement("CREATE INDEX index_rules_two_freezed_challenge_steps ON `freezed_challenge_steps` ( `user_id`, `log_date`)");
        DB::statement("CREATE INDEX index_rules_challenge_wise_user_ponits ON `challenge_wise_user_ponits` ( `challenge_id`, `company_id`, `team_id`, `user_id`)");
        DB::statement("CREATE INDEX index_rules_one_challenge_wise_user_ponits ON `challenge_wise_user_ponits` ( `company_id`, `team_id`, `user_id`)");
        DB::statement("CREATE INDEX index_rules_two_challenge_wise_user_ponits ON `challenge_wise_user_ponits` ( `challenge_id`, `user_id`)");
        DB::statement("CREATE INDEX index_rules_three_challenge_wise_user_ponits ON `challenge_wise_user_ponits` ( `challenge_id`, `company_id`, `team_id`)");
        DB::statement("CREATE INDEX index_rules_challenge_wise_team_ponits ON `challenge_wise_team_ponits` ( `challenge_id`, `company_id`, `team_id`)");
        DB::statement("CREATE INDEX index_rules_one_challenge_wise_team_ponits ON `challenge_wise_team_ponits` ( `challenge_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_challenge_wise_company_points ON `challenge_wise_company_points` ( `challenge_id`, `company_id`)");
        DB::statement("CREATE INDEX index_rules_challenges ON `challenges` ( `company_id`, `challenge_category_id`)");
        DB::statement("CREATE INDEX index_rules_one_challenges ON `challenges` ( `company_id`, `start_date`, `end_date`)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::disableForeignKeyConstraints();
        // DB::statement("ALTER TABLE `user_exercise` DROP INDEX index_rules_user_exercise");
        // DB::statement("ALTER TABLE `user_exercise` DROP INDEX index_rules_second_user_exercise");
        // DB::statement("ALTER TABLE `user_step` DROP INDEX index_rules_user_step");
        // DB::statement("ALTER TABLE `user_listened_tracks` DROP INDEX index_rules_user_listened_tracks");
        // DB::statement("ALTER TABLE `challenge_history` DROP INDEX index_rules_challenge_history");
        // DB::statement("ALTER TABLE `freezed_challenge_participents` DROP INDEX index_rules_freezed_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_challenge_participents` DROP INDEX index_rules_second_freezed_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_challenge_participents` DROP INDEX index_rules_third_freezed_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_challenge_participents` DROP INDEX index_rules_four_freezed_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_challenge_participents` DROP INDEX index_rules_fifth_freezed_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_team_challenge_participents` DROP INDEX index_rules_freezed_team_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_team_challenge_participents` DROP INDEX index_rules_one_freezed_team_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_team_challenge_participents` DROP INDEX index_rules_two_freezed_team_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_team_challenge_participents` DROP INDEX index_rules_three_freezed_team_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_team_challenge_participents` DROP INDEX index_rules_four_freezed_team_challenge_participents");
        // DB::statement("ALTER TABLE `freezed_challenge_steps` DROP INDEX index_rules_freezed_challenge_steps");
        // DB::statement("ALTER TABLE `freezed_challenge_steps` DROP INDEX index_rules_one_freezed_challenge_steps");
        // DB::statement("ALTER TABLE `freezed_challenge_steps` DROP INDEX index_rules_two_freezed_challenge_steps");
        // DB::statement("ALTER TABLE `challenge_wise_user_ponits` DROP INDEX index_rules_challenge_wise_user_ponits");
        // DB::statement("ALTER TABLE `challenge_wise_user_ponits` DROP INDEX index_rules_one_challenge_wise_user_ponits");
        // DB::statement("ALTER TABLE `challenge_wise_user_ponits` DROP INDEX index_rules_two_challenge_wise_user_ponits");
        // DB::statement("ALTER TABLE `challenge_wise_user_ponits` DROP INDEX index_rules_three_challenge_wise_user_ponits");
        // DB::statement("ALTER TABLE challenge_wise_team_ponits DROP INDEX index_rules_challenge_wise_team_ponits");
        // DB::statement("ALTER TABLE challenge_wise_team_ponits DROP INDEX index_rules_one_challenge_wise_team_ponits");
        // DB::statement("ALTER TABLE challenge_wise_company_points DROP INDEX index_rules_challenge_wise_company_points");
        // DB::statement("ALTER TABLE challenges DROP INDEX index_rules_challenges");
        // DB::statement("ALTER TABLE challenges DROP INDEX index_rules_one_challenges");
        // Schema::enableForeignKeyConstraints();
    }
}

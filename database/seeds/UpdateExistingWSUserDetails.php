<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\WsUser;
use Illuminate\Database\Seeder;

class UpdateExistingWSUserDetails extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $wsUserDetails = User::whereNotNull('conferencing_mode')->whereNotNull('language')->select('id', 'language', 'conferencing_mode', 'video_link', 'shift', 'years_of_experience', 'is_authenticate', 'is_availability')->get();

        if (!empty($wsUserDetails)) {
            foreach ($wsUserDetails as $value) {
                $role = getUserRole($value);
                if ($role->slug == 'wellbeing_specialist') {
                    WsUser::updateOrCreate(
                        ['user_id' => $value->id],
                        [
                            'language'            => $value->language,
                            'conferencing_mode'   => $value->conferencing_mode,
                            'video_link'          => $value->video_link,
                            'shift'               => $value->shift,
                            'years_of_experience' => $value->years_of_experience,
                            'is_authenticate'     => $value->is_authenticate,
                            'is_availability'     => $value->is_availability,
                        ]
                    );
                }
            }
        }
    }
}

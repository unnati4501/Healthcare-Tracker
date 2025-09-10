<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminAlert;
use App\Models\AdminAlertUsers;

class AdminAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
        $now                 = date('Y-m-d H:i:s');
        $appEnvironment      = app()->environment();
       
        $alertData = [
            // [
            //     'title'        => config('zevolifesettings.admin_alerts.next_to_kin_info_title'),
            //     'description'  => config('zevolifesettings.admin_alerts.next_to_kin_info_desc'),
            //     'users' => [
            //         'user_name'  => 'Demo User',
            //         'user_email' => config('zevolifesettings.admin_alert_emails.next_to_access_kin_info.' . $appEnvironment)
            //     ]
            // ],
            // [
            //     'title'        => config('zevolifesettings.admin_alerts.digital_therapy_title'),
            //     'description'  => config('zevolifesettings.admin_alerts.digital_therapy_desc'),
            //     'users' => [
            //         'user_name'  => 'Demo User',
            //         'user_email' => config('zevolifesettings.admin_alert_emails.dt_exception_emails.' . $appEnvironment)
            //     ]
            // ],
            [
                'title'        => config('zevolifesettings.admin_alerts.digital_therapy_company_deletion_title'),
                'description'  => config('zevolifesettings.admin_alerts.digital_therapy_company_deletion_desc'),
                'users' => [
                    'user_name'  => 'Demo User',
                    'user_email' => config('zevolifesettings.admin_alert_emails.dt_deletion_emails.' . $appEnvironment)
                ]
            ],
            [
                'title'        => config('zevolifesettings.admin_alerts.wbs_profile_verification_title'),
                'description'  => config('zevolifesettings.admin_alerts.wbs_profile_verification_desc'),
                'users' => [
                    'user_name'  => 'Demo User',
                    'user_email' => config('zevolifesettings.admin_alert_emails.dt_deletion_emails.' . $appEnvironment)
                ]
            ]
        ];

        foreach ($alertData as $value) {
            $record = AdminAlert::updateOrCreate(
                ['title' => $value['title']],
                [
                    'title'       => $value['title'],
                    'description' => $value['description'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            );

            if($record) {
                $record->users()->updateOrCreate(
                    ['user_email' => $value['users']['user_email']],
                    [
                        'alert_id'        => $record->id,
                        'user_name'       => $value['users']['user_name'],
                        'user_email'      => $value['users']['user_email'], 
                    ]);
            }
        }
    }
}

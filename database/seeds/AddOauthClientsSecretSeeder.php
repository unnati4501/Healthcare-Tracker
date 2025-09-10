<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class AddOauthClientsSecretSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    	\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('oauth_clients')->truncate();

        $oauthClientData = [
            [
                'user_id'     			=> '',
                'name'        			=> 'zevolife Personal Access Client',
                'secret'       			=> '0T0P1vTR8So4Wkn5707YHcfBqRDD7EKs4TKVjga2',
                'redirect'				=> env('APP_URL'),
                'provider' 				=> 'users',
                'personal_access_client'=> 1,
                'password_client'       => 0,
                'revoked'           	=> 0,
                'created_at'        	=> date('Y-m-d H:i:s'),
                'updated_at'        	=> date('Y-m-d H:i:s'),
            ],
            [
                'user_id'          		=> '',
                'name'        			=> 'zevolife Password Grant Client',
                'secret'       			=> 'O9kXqF8FrSImPnqUjeMqZ2UrmIOVdvL7eRr9qrcX',
                'redirect'				=> env('APP_URL'),
                'provider' 				=> 'users',
                'personal_access_client'=> 0,
                'password_client'       => 1,
                'revoked'           	=> 0,
                'created_at'        	=> date('Y-m-d H:i:s'),
                'updated_at'        	=> date('Y-m-d H:i:s'),
            ],
        ];

        DB::table('oauth_clients')->insert($oauthClientData);

        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

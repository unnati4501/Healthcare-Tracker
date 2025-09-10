<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class IndustriesSeeder
 */
class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \DB::table('users')->truncate();

            $user = User::create([
                'first_name'    => 'Laravel',
                'last_name'     => 'Admin',
                'email'         => 'admin@test.com',
                'timezone'      => 'Asia/Calcutta',
                'password'      => Hash::make('Admin@123'),
                'last_login_at' => now()->toDateTimeString(),
                'created_at'    => now()->toDateTimeString(),
                'updated_at'    => now()->toDateTimeString(),
                'is_coach'      => true,
            ]);

            $role = Role::where('slug', 'super_admin')->first();

            $user->roles()->attach($role);

            // save user profile
            $user->profile()->create([
                'gender'     => 'male',
                'height'     => '100',
                'birth_date' => '1900-01-01',
            ]);

            // save user weight
            $user->weights()->create([
                'weight' => '50',
            ]);

            $categories = \App\Models\Category::where('in_activity_level', 1)->get();

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $user->expertiseLevels()->attach($category, ['expertise_level' => 'beginner']);
                }
            }
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}

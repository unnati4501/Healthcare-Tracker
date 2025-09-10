<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UpdateExistingUsersStartDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::beginTransaction();
            $users = User::all()->chunk(500);
            $users->each(function ($userChunk) {
                $userChunk->each(function ($user) {
                    $role = getUserRole($user);
                    if ($role->group == 'company') {
                        $company = $user->company()->first();
                        $user->update(['start_date' => $company->subscription_start_date]);
                    }
                });
            });
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}

<?php declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\AppVersion;
use Illuminate\Database\Seeder;
use DB;

/**
 * Class AppVersionSeeder
 */
class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::beginTransaction();
            AppVersion::insert([
                'andriod_version'      => config('zevolifesettings.version.andriod_version'),
                'andriod_force_update' => config('zevolifesettings.version.andriod_force_update'),
                'ios_version'          => config('zevolifesettings.version.ios_version'),
                'ios_force_update'     => config('zevolifesettings.version.ios_force_update'),
            ]);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
        }
    }
}

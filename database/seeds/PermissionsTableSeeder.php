<?php
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class PermissionsTableSeeder.
 */
class PermissionsTableSeeder extends Seeder
{
    use DisableForeignKeys, TruncateTable;

    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        // truncate table
        $this->truncateMultiple(['permissions']);

        $permissionList = \json_decode(
            \file_get_contents(
                __DIR__ . '/data/permissions.json'
            ),
            true
        );

        $permissions = [];

        $sort = 1;
        $now  = Carbon::now();
        foreach ($permissionList as $value) {
            $permissions[] = [
                'parent_id'    => $value['parent_id'],
                'name'         => str_slug($value['display_name']),
                'display_name' => $value['display_name'],
                'sort'         => $sort,
                'status'       => isset($value['status']) ? $value['status'] : 1,
                'created_by'   => 1,
                'updated_by'   => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
            $sort++;
        }

        try {
            DB::beginTransaction();
            foreach ($permissions as $permission) {
                Permission::create($permission);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            echo $exception->getMessage();
        }

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}

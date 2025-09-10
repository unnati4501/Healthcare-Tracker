<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Traits\TruncateTable;
use App\Http\Traits\DisableForeignKeys;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyWiseCredits;

class EmptyCreditHistoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('company_wise_credits')->truncate();
        if (Schema::hasTable('companies')) {
            DB::statement("UPDATE `companies` SET `credits` = 0 where credits > 0");
        }
    }
}

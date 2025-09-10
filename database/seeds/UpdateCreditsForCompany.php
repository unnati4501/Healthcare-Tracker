<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateCreditsForCompany extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {            
            // Empty the credits table data
            DB::statement("TRUNCATE TABLE company_wise_credits");

            // Update total credits to 0 for all companies
            DB::statement("UPDATE `companies` SET `credits` = 0;");

        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}

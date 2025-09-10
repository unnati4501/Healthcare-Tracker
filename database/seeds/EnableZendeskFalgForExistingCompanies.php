<?php
namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use \Illuminate\Database\QueryException;

class EnableZendeskFalgForExistingCompanies extends Seeder
{
    /**
     * This seeder will enable 'is_intercom'(field is being used for 'zendesk' as of now) field to true for all the companies as requested by client ticket #ZL-3198
     *
     * @return void
     */
    public function run()
    {
        try {
            Company::where('id', '>', 0)->update([
                'is_intercom' => true,
            ]);
        } catch (QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}

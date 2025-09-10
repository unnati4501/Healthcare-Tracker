<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanyBranding;

class UpdatePortalSubDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companyBranding = CompanyBranding::get();
        foreach ($companyBranding as $value) {
            $value->portal_sub_description      = config('zevolifesettings.company_branding.portal_sub_description');
            $value->portal_footer_header_text   = config('zevolifesettings.portalFooter.footerHeader');
            $value->dt_title                    = config('zevolifesettings.digital_therapy.title');
            $value->dt_description              = config('zevolifesettings.digital_therapy.description');
            $value->update();
        }
    }
}

<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\CompanyBrandingContactDetails;
use Carbon\Carbon;

class UpdateContactusForPortalBranding extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $appEnvironment      = app()->environment();
        $isTikTokcompanyCode = config('zevolifesettings.branding_contact_details.company_codes.' . $appEnvironment);
        $companyBranding     = CompanyBranding::get();
        foreach ($companyBranding as $value) {
            $insertArr           = [];
            $company = Company::where('id', $value['company_id'])->first();
            if (in_array($company->code, $isTikTokcompanyCode)) {
                $logo       = config('zevolifesettings.fallback_image_url.company.contact_us_image_tiktok');
                $logoName   = config('zevolifesettings.default_seeder_images.company.contactus_tiktok');
                $insertArr['contact_us_description']  = config('zevolifesettings.branding_contact_details.contact_us_description_tiktok');
                $insertArr['contact_us_request']      = config('zevolifesettings.branding_contact_details.clinical');
            } else {
                $logo       = config('zevolifesettings.fallback_image_url.company.contact_us_image_local');
                $logoName   = config('zevolifesettings.default_seeder_images.company.contactus_default');
                $insertArr['contact_us_description']  = config('zevolifesettings.branding_contact_details.contact_us_description_local');
                $insertArr['contact_us_request']      = config('zevolifesettings.branding_contact_details.technical');
                
            }
            $insertArr['company_id']          = $value['company_id'];
            $insertArr['contact_us_header']   = config('zevolifesettings.branding_contact_details.contact_us_header');
            $insertArr['created_at']          = Carbon::now();
            $insertArr['updated_at']          = Carbon::now();

            $record = CompanyBrandingContactDetails::updateOrCreate(
                ['company_id' => $value['company_id']],
                $insertArr
            );
            
            if($record){
                $name       = $record->id . '_' . \time();
                $extention  = pathinfo($logoName, PATHINFO_EXTENSION);
                $record->clearMediaCollection('contact_us_image')
                    ->addMediaFromUrl($logo)
                    ->usingName($logoName)
                    ->usingFileName($name.".".$extention)
                    ->toMediaCollection('contact_us_image', config('medialibrary.disk_name'));
            }
        }
    }
}

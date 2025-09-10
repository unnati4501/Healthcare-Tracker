<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\CompanyBranding;

class UpdateAppointmentDetailsForPortalBranding extends Seeder
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
        $companyBranding     = CompanyBranding::all();

        foreach ($companyBranding as $value) {
            $company = Company::where('id', $value->company_id)->first();
            if (in_array($company->code, $isTikTokcompanyCode)) {
                $logo       = config('zevolifesettings.fallback_image_url.company.appointment_image_tiktok');
                $logoName   = config('zevolifesettings.default_seeder_images.company.appointment_tiktok');
            } else {
                $logo       = config('zevolifesettings.fallback_image_url.company.appointment_image_local');
                $logoName   = config('zevolifesettings.default_seeder_images.company.appointment_default');
            }
            if($company){
                $name       = $company->id . '_' . \time();
                $extention  = pathinfo($logoName, PATHINFO_EXTENSION);
                $company->clearMediaCollection('appointment_image')
                    ->addMediaFromUrl($logo)
                    ->usingName($logoName)
                    ->usingFileName($name.".".$extention)
                    ->toMediaCollection('appointment_image', config('medialibrary.disk_name'));
            }
        }
    }
}

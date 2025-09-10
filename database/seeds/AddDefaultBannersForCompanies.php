<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\CompanyDigitalTherapyBanner;

class AddDefaultBannersForCompanies extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $zevoBanners            = config('zevolifesettings.zevo_banners');
        $parentChildBanners     = config('zevolifesettings.portal_banners');
        try {
            $companies = Company::select('companies.*')->leftJoin('cp_company_plans', 'companies.id', '=', 'cp_company_plans.company_id')
                        ->join('cp_plan', 'cp_plan.id', '=', 'cp_company_plans.plan_id')
                        ->join('cp_plan_features', 'cp_plan_features.plan_id', '=', 'cp_plan.id')
                        ->join('cp_features', 'cp_features.id', '=', 'cp_plan_features.feature_id');
                        
            $companies = $companies->where(function ($q) {
                $q->where('cp_features.slug', 'digital-therapy')
                    ->orWhere('cp_features.slug', 'eap');
                });
            $companies = $companies->get()->chunk(500);
                
            $companies->each(function ($companyChunk) use($parentChildBanners, $zevoBanners) {
            $companyChunk->each(function ($company) use($parentChildBanners, $zevoBanners) {
                if ($company->is_reseller || (!$company->is_reseller && !is_null($company->parent_id))) {
                    foreach($parentChildBanners as $parentChildBannerData){
                        $record = CompanyDigitalTherapyBanner::create([
                            'company_id'          =>  $company->id,
                            'description'         =>  $parentChildBannerData['description'],
                            'order_priority'      =>  $parentChildBannerData['order']
                        ]);
                        if (!empty($record)) {
                            $name = $record->id . '_' . \time();
                            $record->clearMediaCollection('banner_image')
                                    ->addMediaFromUrl($parentChildBannerData['image'])
                                    ->usingName($name)
                                    ->toMediaCollection('banner_image', config('medialibrary.disk_name'));
                        }
                    }
                } elseif (!$company->is_reseller &&  is_null($company->parent_id)){
                    foreach($zevoBanners as $zevoBannerData){
                        $record = CompanyDigitalTherapyBanner::create([
                            'company_id'          =>  $company->id,
                            'description'         =>  $zevoBannerData['description'],
                            'order_priority'      =>  $zevoBannerData['order']
                        ]);
                        if (!empty($record)) {
                            $name = $record->id . '_' . \time();
                            $record->clearMediaCollection('banner_image')
                                    ->addMediaFromUrl($zevoBannerData['image'])
                                    ->usingName($name)
                                    ->toMediaCollection('banner_image', config('medialibrary.disk_name'));
                        }
                    }
                }
                });
            });
        } catch (QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}

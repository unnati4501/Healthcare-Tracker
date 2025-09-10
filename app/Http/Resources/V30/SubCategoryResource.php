<?php

namespace App\Http\Resources\V30;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\SubCategory;

class SubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $return =[
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->short_name,
        ];
        
        if ($this->id != 0  && $this->id != -1) {
            $category = SubCategory::select('categories.short_name as categoryShortName')->
            leftJoin('categories', 'categories.id', '=', 'sub_categories.category_id')->where('sub_categories.id', $this->id)
            ->first();
            
            if ($category->categoryShortName == 'course' || $category->categoryShortName =='webinar' || $category->categoryShortName =='feed') {
                $return['logo']          = $this->getMediaData('logo', ['w' => 640, 'h' => 1260, 'zc' => 3]);
                $return['background']    = $this->getMediaData('background', ['w' => 320, 'h' => 320, 'zc' => 3]);
            } elseif ($category->categoryShortName == 'meditation') {
                $return['background']    = $this->getMediaData('background', ['w' => 320, 'h' => 320, 'zc' => 3]);
            } else {
            }
        } elseif ($this->id == 0) {
            $return['background'] = array('width'=>320, 'height'=>320, "url"=> config('zevolifesettings.fallback_image_url.sub_category.favorite'));
        } elseif ($this->id == -1) {
            $return['background'] = array('width'=>320, 'height'=>320, "url"=> config('zevolifesettings.fallback_image_url.sub_category.view_all'));
        }

        return $return;
    }
}

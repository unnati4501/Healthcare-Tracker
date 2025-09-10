<?php

namespace App\Http\Resources\V33;

use App\Http\Resources\V12\GethelpResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;

class ConsentFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'question_title'             => (!empty($this->title) ? $this->title : ""),
            'question_description'       => (!empty($this->description) ? $this->description : ""),
        ];
    }
}

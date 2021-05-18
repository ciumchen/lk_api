<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessDataResources extends JsonResource
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
            'id' => $this->id,
            'banners' => $this->banners,
            'name' => $this->name,
            'district' => $this->districtLabel->name,
            'category' => $this->category->name,
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'run_time' => $this->run_time,
            'created_at' => $this->created_at->format("Y-m-d H:i:s"),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RebateDataResources extends JsonResource
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
            'consumer' => (float) (isset($this->consumer) ? bcdiv($this->consumer, 10000, 4) : 0),
            'business' => (float) (isset($this->business) ? bcdiv($this->business, 10000, 4) : 0),
            'welfare' => (float) (isset($this->welfare) ? bcdiv($this->welfare, 10000, 4) : 0),
            'share' => (float) (isset($this->share) ? bcdiv($this->share, 10000, 4) : 0),
            'market' => (float) (isset($this->market) ? bcdiv($this->market, 10000, 4) : 0),
            'platform' => (float) (isset($this->platform) ? bcdiv($this->platform, 10000, 4) : 0),
            'people' => (int) (isset($this->people) ? $this->people : 0),
            'new_business' => (int) (isset($this->new_business) ? $this->new_business : 0),
            'total_consumption' => (float) (isset($this->total_consumption) ? $this->total_consumption : 0),
            'consumer_lk_iets' => (float) (isset($this->consumer_lk_iets) ? $this->consumer_lk_iets : 0),
            'business_lk_iets' => (float) (isset($this->business_lk_iets) ? $this->business_lk_iets : 0),
        ];
    }
}

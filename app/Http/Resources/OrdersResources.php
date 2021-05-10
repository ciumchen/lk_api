<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdersResources extends JsonResource
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
            'phone' => $this->user->phone,
            'username' => $this->user->username,
            'status' => $this->status,
            'status_label' => Order::$statusLabel[$this->status],
            'business_name' => $this->business->name,
            'profit_ratio' => rtrim_zero($this->profit_ratio),
            'price' => rtrim_zero($this->price),
            'name' => $this->name,
            'created_at' => $this->created_at->format("Y-m-d H:i:s"),
            'pay_status' => $this->pay_status,
            'numeric' => $this->numeric
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\IntegralLogs;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegralLogsResources extends JsonResource
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
            'operate_type' => IntegralLogs::$typeLabel[$this->operate_type],
            'amount' => rtrim_zero($this->amount),
            'role' => IntegralLogs::$rolLabel[$this->role],
            'remark' => $this->remark,
            'created_at' => $this->created_at->format("Y-m-d H:i:s"),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\AssetsLogs;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetsLogsResources extends JsonResource
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
            'operate_type' => AssetsLogs::$operateTypeTexts[$this->operate_type] ?? '',
            'amount' => rtrim_zero($this->amount),
            'created_at' => $this->created_at->format("Y-m-d H:i:s"),
        ];
    }
}

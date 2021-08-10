<?php

namespace App\Jobs;

use App\Exceptions\LogicException;
use App\Services\GatherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGatherLottery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $orderData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderData)
    {
        $this->orderData = $orderData;
    }

    /**执行队列任务
     * Execute the job.
     * @param GatherService $gatherService
     * @return mixed
     * @throws LogicException
     */
    public function handle(GatherService $gatherService)
    {
        if (!empty($this->orderData))
        {
            $gatherService->updGatherTrade($this->orderData->orderData);
            $gatherService->completeOrderGather($this->orderData->orderData);
        } else
        {
            return json_encode(['code' => 200, 'msg' => '本拼团已完成开奖！']);
        }
    }
}

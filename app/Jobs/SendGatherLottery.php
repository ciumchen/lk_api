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
            sleep(10);
            //更新未中奖用户录单数据
            $gatherService->updGatherTrade($this->orderData->orderData);
            \Log::info('QUEUE队列：', $this->orderData->orderData);
            //自动审核未中奖用户录单、加积分
            $gatherService->completeOrderGather($this->orderData->orderData);
            \Log::info('录单队列：', $this->orderData->orderData);
        } else
        {
            return json_encode(['code' => 200, 'msg' => '本拼团已完成开奖！']);
        }
    }
}

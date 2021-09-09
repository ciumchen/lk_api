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
use Illuminate\Support\Facades\Log;
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
            Log::debug("===============测试============拼团录单=======handle=====1111111111111=================");
            sleep(2);
            //更新未中奖用户录单数据
            $gatherService->updGatherTrade($this->orderData->orderData);
            Log::debug("===============测试============拼团录单=======handle=====aaaaaaaaaaaaaaaaaaaaa=================");
            //自动审核未中奖用户录单、加积分
            $gatherService->completeOrderGather($this->orderData->orderData);
            Log::debug("===============测试============拼团录单=======handle=====bbbbbbbbbbbbbbbbbb=================");
        } else
        {
            throw new LogicException('本拼团已完成开奖！');
        }
    }
}

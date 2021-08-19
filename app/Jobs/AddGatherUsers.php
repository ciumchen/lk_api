<?php

namespace App\Jobs;

use App\Exceptions\LogicException;
use App\Models\GatherGoldLogs;
use App\Models\GatherUsers;
use App\Models\Jobs;
use App\Models\Setting;
use App\Services\GatherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AddGatherUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**参与拼团队列
     * Execute the job.
     * @return mixed
     * @throws LogicException
     */
    public function handle()
    {
        //获取设置拼团总人数
        $userRatio = Setting::getSetting('gather_users_ number') ?? 100;
        $userQueueSum = Jobs::where(['queue' => 'addGatherUsers'])->count();
        if ($userQueueSum >= $userRatio)
        {
            throw new LogicException('本拼团参团人数已满！');
        }
        if (!empty($this->data->data['gid']) && !empty($this->data->data['uid']))
        {
            //新增用户参团记录
            $gatherUsersData = (new GatherUsers())->setGatherUsers($this->data->data['gid'], $this->data->data['uid']);
            //新增来拼金记录
            (new GatherGoldLogs())->setGatherGold($this->data->data['gid'], $this->data->data['uid'], $gatherUsersData->id);
            //判断是否开团、开奖
            (new GatherService())->isMaxGatherUser($this->data->data['gid'], $userRatio);
        } else
        {
            throw new LogicException('请点击参与拼团！！');
        }
    }
}

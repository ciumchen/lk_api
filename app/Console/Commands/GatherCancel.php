<?php

namespace App\Console\Commands;

use App\Models\Gather;
use App\Models\GatherGoldLogs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GatherCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gather:cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '取消72小时还未开启的拼团';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws
     */
    public function handle()
    {
        $time = time();
        $diffTime = 72 * 3600;

        //获取还未开启的拼团数据
        $gatherData = (new Gather())->getNoOpen(0);
        $gatherList = json_decode($gatherData, 1);

        try {
            foreach ($gatherList as $val)
            {
                if ($time - strtotime($val['created_at']) > $diffTime)
                {
                    //更新 gather 表拼团状态
                    $gather = Gather::find($val['id']);
                    $gather->status = 3;
                    $gather->save();

                    //更新 gather_gold_logs 表来拼金状态
                    GatherGoldLogs::where(['gid' => $val['id']])
                        ->update(['status' => 3, 'type' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
                    Log::info('拼团 ' . $val['id'] . ' 关闭成功！');
                }
            }
        } catch (\Exception $e)
        {
            Log::info('未知错误：' . $e->getMessage());
        }
    }
}

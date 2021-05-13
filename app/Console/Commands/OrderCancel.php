<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OrderCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '取消订单当天未支付订单';

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
        $year = date("Y");
        $month = date("m");
        $day = date("d");

        $time = time();
        //当天订单结束时间戳
        $end= mktime(14,40,00, $month, $day, $year);

        //获取当天订单数据
        $orderData = (new Order())->getTodayOrders();
        $ids = array_column($orderData, 'id');

        try {
            if ($time - $end > 0)
            {
                //更新 order 表订单状态
                Order::whereIn('id', $ids)->update(['pay_status' => 'close', 'updated_at' => date("Y-m-d H:i:s")]);
                Log::info('订单关闭成功：'. implode(',', $ids));
            }
        } catch (\Exception $e)
        {
            Log::info('未知错误：' . $e->getMessage());
        }
    }
}

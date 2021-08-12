<?php

namespace App\Console\Commands;

use App\Services\AssetsService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class exchangeUsdt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchangeUsdt';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '原有USDT转换命令';
    
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
     *
     * @return void
     */
    public function handle()
    {
        $this->info(Carbon::now()->toDateTimeString()." \n开始执行\n USDT转换\n");
        (new AssetsService())->exchangeUSDTRatio();
        $this->info(Carbon::now()->toDateTimeString()." \nUSDT转换\n 执行完毕\n");
    }
}

<?php

namespace App\Console\Commands;

use App\Services\AssetsService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class resetChangeTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resetChangeTimes';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    
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
     * @return int
     */
    public function handle()
    {
        $this->info(Carbon::now()->toDateTimeString()." \n开始执行\n 转换次数重置\n");
        (new AssetsService())->exchangeUSDTRatio();
        $this->info(Carbon::now()->toDateTimeString()." \n转换次数重置\n 执行完毕\n");
    }
}

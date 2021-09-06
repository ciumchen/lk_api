<?php

namespace App\Console\Commands;

use App\Services\UserRebateService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class weightRewardsScale extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weightRewardsScale';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '加权分红';
    
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
        $this->info(Carbon::now()->toDateTimeString()." \n开始执行\n 加权分红\n");
        (new UserRebateService())->weightRewardsScale();
        $this->info(Carbon::now()->toDateTimeString()." \n加权分红\n 执行完毕\n");
    }
}

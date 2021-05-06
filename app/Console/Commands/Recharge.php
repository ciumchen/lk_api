<?php

namespace App\Console\Commands;

use App\Services\TransactionsService;
use Illuminate\Console\Command;

class Recharge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Recharge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '充值命令';

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
        (new TransactionsService())->tokenCharge();
    }
}

<?php

namespace App\Console\Commands;

use App\Services\TransactionsService;
use Illuminate\Console\Command;

class SyncTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncTransactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步交易记录';

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
        (new TransactionsService())->synchronizeTransactionLogs();
    }
}

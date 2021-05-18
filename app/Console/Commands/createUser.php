<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class createUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'createUser';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成初始用户';

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
        if(User::where("phone",'laike')->count() == 0)
        {
            $user = new User();
            $user->role = 1;
            $user->phone = 'laike';
            $user->username = 'laike';
            $user->salt = Str::random(6);;
            $user->code_invite = '000001';
            $user->save();
        }

        $this->info('执行成功');
    }
}

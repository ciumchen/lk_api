<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Database\Events\QueryExecuted;

class QueryListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(QueryExecuted $event)
    {
        if (env('APP_ENV', 'production') == 'local') {
            $sql = str_replace('?', "'%s'", $event->sql);
            $log = vsprintf($sql, $event->bindings);
            $this->putLog('sql', $log);
        }
    }
    
    private function putLog($file = 'app', $content = '')
    {
        $data = date('Y-m-d');
        $cut_line = str_repeat("-", 100);
        is_dir(storage_path('logs/sql')) or mkdir(storage_path('logs/sql'), 0777, true); // 文件夹不存在则创建
        $content = '['.date('Y-m-d H:i:s')."]".$content;
        @file_put_contents(
            storage_path('logs/sql/'.$file.'-'.$data.'.log'),
            $content."\n".$cut_line."\n\n",
            FILE_APPEND
        );
    }
}

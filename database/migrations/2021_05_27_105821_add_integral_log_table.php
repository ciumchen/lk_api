<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntegralLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integral_log',function (Blueprint $table){
            $table->unsignedInteger('activityState')->nullable()->default(0)->comment('积分活动状态,0表示关闭,1标识开启');
            $table->unsignedInteger('consumer_uid')->nullable()->comment('贡献积分的消费者uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integral_log', function (Blueprint $table) {
            $table->dropColumn(['activityState']);
            $table->dropColumn(['consumer_uid']);
        });
    }
}

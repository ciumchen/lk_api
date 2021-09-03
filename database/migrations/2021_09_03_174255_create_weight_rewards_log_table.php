<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeightRewardsLogTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weight_rewards_log', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->default('')->comment('订单号');
            $table->char('count_date', 8)->default('')->comment('统计日期YYYmmdd');
            $table->decimal('silver_money', 20, 4)->default(0.0000)->comment('银卡已累计金额');
            $table->decimal('gold_money', 20, 4)->default(0.0000)->comment('金卡已累计金额');
            $table->decimal('diamond_money', 20, 4)->default(0.0000)->comment('钻石卡已累计金额');
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weight_rewards_log');
    }
}

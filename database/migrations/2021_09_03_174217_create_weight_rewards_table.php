<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeightRewardsTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weight_rewards', function (Blueprint $table) {
            $table->id();
            $table->char('count_date', 8)->default('')->comment('统计日期YYYmmdd')->index();
            $table->decimal('silver_money', 20, 4)->default(0.0000)->comment('银卡已累计金额');
            $table->decimal('gold_money', 20, 4)->default(0.0000)->comment('金卡已累计金额');
            $table->decimal('diamond_money', 20, 4)->default(0.0000)->comment('钻石卡已累计金额');
            $table->tinyInteger('is_del')->default(0)->comment('是否已处理[分红]')->index();
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
        Schema::dropIfExists('weight_rewards');
    }
}

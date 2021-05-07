<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRechargeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recharge_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('order_no')->nullable()->comment('trade_order表 -- order_no');
            $table->unsignedInteger('reorder_id')->nullable()->comment('充值订单 id');
            $table->string('type', 10)->comment('充值类型：HF 话费；YK 油卡；MT 美团');
            $table->string('status', 24)->comment('充值状态');
            $table->timestamp('created_at')->nullable()->comment('创建时间');

            $table->index(['order_no', 'reorder_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recharge_logs');
    }
}

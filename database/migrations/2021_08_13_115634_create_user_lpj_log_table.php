<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLpjLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_lpj_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->nullable()->comment('users表 -- id');
            $table->string('operate_type', 20)->nullable()->default('')->comment('操作类型,充值：recharge，提现：withdrawal，消费：consumption');
            $table->decimal('money', 15, 2)->default(0.00)->comment('变动来拼金');
            $table->decimal('money_before_change', 15, 2)->default(0.00)->comment('变动前来拼金');
            $table->string('order_no', 40)->default('')->comment('充值订单号');
            $table->tinyInteger('status')->default(1)->comment('充值状态:1处理中,2成功,3失败');
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
        Schema::dropIfExists('user_lpj_log');
    }
}


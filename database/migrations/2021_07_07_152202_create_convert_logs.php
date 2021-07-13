<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConvertLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('convert_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('uid')->default(0)->comment('充值用户id');
            $table->string('phone', 15)->default('')->comment('充值手机号/卡号');
            $table->string('user_name', 20)->default('')->comment('充值姓名');
            $table->unsignedDecimal('price', 10, 2)->default(0.00)->comment('充值金额');
            $table->unsignedDecimal('usdt_amount', 10, 2)->default(0.00)->comment('兑换金额');
            $table->string('order_no', 30)->default('')->comment('充值订单号');
            $table->unsignedInteger('oid')->default(0)->comment('order 表 id');
            $table->tinyInteger('type')->default(0)->comment('兑换类型：1 话费；2 美团');
            $table->tinyInteger('status')->default(0)->comment('兑换状态：0 待兑换；1 处理中； 2 成功；3 失败');
            $table->timestamp('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->nullable()->comment('更新时间');

            $table->index(['uid', 'order_no', 'oid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('convert_logs');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderMobileRecharge extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_mobile_recharge', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id('id');
            $table->string('order_no', '30')
                  ->default('')
                  ->comment('订单号');
            $table->string('mobile', '11')
                  ->default('')
                  ->comment('充值手机号');
            $table->bigInteger('order_id')
                  ->default(0)
                  ->comment('订单(order)表ID');
            $table->dateTime('created_at')
                  ->nullable()
                  ->comment('创建时间');
            $table->dateTime('updated_at')
                  ->nullable()
                  ->comment('更新时间');
            $table->decimal('money')
                  ->default(0)
                  ->comment('充值金额');
            $table->string('trade_no', '50')
                  ->default('')
                  ->comment('接口方返回单号');
            $table->tinyInteger('status')
                  ->unsigned()
                  ->default('0')
                  ->comment('充值状态：0充值中 1成功 9撤销');
            $table->string('goods_title', '80')
                  ->default('')
                  ->comment('商品名称');
            $table->bigInteger('uid')
                  ->default(0)
                  ->comment('充值用户ID');
        });
        DB::statement('ALTER TABLE `order_mobile_recharge` comment "斑马力方手机充值记录表";');
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_mobile_recharge');
    }
}

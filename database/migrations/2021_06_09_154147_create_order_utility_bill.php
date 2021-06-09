<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderUtilityBill extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_utility_bill', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id('id');
            $table->string('order_no', '30')
                  ->default('')
                  ->comment('订单号');
            $table->bigInteger('user_id')
                  ->default(0)
                  ->comment('充值用户ID');
            $table->string('account', '30')
                  ->default('')
                  ->comment('充值账号');
            $table->bigInteger('order_id')
                  ->default(0)
                  ->comment('订单表ID');
            $table->decimal('money')
                  ->default(0)
                  ->comment('充值金额');
            $table->string('trade_no', '50')
                  ->default('')
                  ->comment('接口方返回单号');
            $table->tinyInteger('pay_status')
                  ->unsigned()
                  ->default('0')
                  ->comment('平台订单付款状态:0未付款,1已付款');
            $table->tinyInteger('status')
                  ->unsigned()
                  ->default('0')
                  ->comment('充值状态:0充值中,1成功,9撤销');
            $table->string('goods_title', '80')
                  ->default('')
                  ->comment('商品名称');
            $table->tinyInteger('create_type')
                  ->default(0)
                  ->comment('订单类型:1水费,2电费,3燃气费');
            $table->dateTime('created_at')
                  ->nullable()
                  ->comment('创建时间');
            $table->dateTime('updated_at')
                  ->nullable()
                  ->comment('更新时间');
        });
        DB::statement('ALTER TABLE `order_utility_bill` comment "斑马力方生活缴费记录表";');
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_utility_bill');
    }
}

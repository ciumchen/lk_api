<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderAirTrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_air_trade', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('trade_no', 30)->nullable()->comment('主订单号');
            $table->string('order_no', 30)->nullable()->comment('子订单号');
            $table->unsignedDecimal('total_face_price', 10, 2)->default(0.00)->comment('票面价（元）');
            $table->unsignedDecimal('total_other_fee', 10, 2)->default(0.00)->comment('费用总和（元）');
            $table->unsignedDecimal('total_pay_cash', 10, 2)->default(0.00)->comment('实际支付金额（元）');
            $table->unsignedInteger('order_type')->default(2)->comment('票务类型：1 火车票，2 飞机票，3 汽车票');
            $table->unsignedInteger('state')->default(0)->comment('订单状态：0 预定中，1 已完成，2 预定完成待支付，9 已取消');
            $table->unsignedInteger('order_state')->default(0)->comment('子订单状态：0 交易中，1 出票成功，6 退票中，7 退票失败，9 出票失败，10 已退票，11 已退款');
            $table->unsignedInteger('bill_state')->default(0)->comment('支付状态： 0 未支付，1 已支付');
            $table->string('title', 20)->nullable()->comment('订单标题');
            $table->string('item_id', 20)->nullable()->comment('标准商品编号');
            $table->string('passenger_name', 20)->nullable()->comment('乘客姓名');
            $table->string('passenger_tel', 20)->nullable()->comment('乘客联系号码');
            $table->unsignedInteger('idcard_type')->default(0)->comment('证件类型 0 身份证');
            $table->string('idcard_no', 35)->nullable()->comment('证件号码');
            $table->string('ticket_no', 35)->nullable()->comment('车票号码');
            $table->unsignedDecimal('pay_cash', 10, 2)->default(0.00)->comment('实际支付的金额，保留两位小数（元）');
            $table->unsignedDecimal('other_fee', 10, 2)->default(0.00)->comment('其它费用总和，保留两位小数（元）');
            $table->unsignedDecimal('refund_fee', 10, 2)->default(0.00)->comment('退款手续费，保留两位小数（元）');
            $table->unsignedInteger('seat_type')->default(0)->comment('座位类型： 0 二等座 ，1 一等座，2 特等座，3 商务座，4 无座，5 硬座，6 软座，7 硬卧，8 软卧，9 高级软卧，10 火车其他座，11 经济舱，12 头等舱，21 汽车座位');
            $table->string('legs', 20)->nullable()->comment('航段');
            $table->string('contact_name', 20)->nullable()->comment('联系人');
            $table->string('contact_tel', 20)->nullable()->comment('联系人电话');
            $table->string('start_time', 30)->nullable()->comment('发车时间');
            $table->string('start_station', 20)->nullable()->comment('出发站');
            $table->string('recevie_station', 20)->nullable()->comment('抵达站');
            $table->string('train_no', 20)->nullable()->comment('车次号');
            $table->string('remark', 20)->nullable()->comment('备注');
            $table->unsignedDecimal('total_refund_amount', 10, 2)->default(0.00)->comment('实际支付金额（元差额退款（元）');
            $table->timestamp('bill_time')->nullable()->comment('支付时间');
            $table->timestamp('etime')->nullable()->comment('完成时间');
            $table->timestamp('ctime')->nullable()->comment('创建时间');
            $table->timestamp('utime')->nullable()->comment('更新时间');
            $table->unsignedInteger('aid')->default(0)->comment('air_trade_logs 表 -- id');

            $table->index(['trade_no', 'order_no', 'aid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_air_trade');
    }
}

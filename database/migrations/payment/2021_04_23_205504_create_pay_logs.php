<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('pid', 50)->nullable()->comment('支付对象PID');
            $table->unsignedInteger('uid')->nullable()->comment('用户UID');
            $table->string('order_no',70)->nullable()->comment("订单号");
            $table->string('pay_channel',30)->nullable()->comment("支付渠道");
            $table->decimal('pay_amt', 8, 2)->comment('交易金额');
            $table->string('description', 150)->comment('支付附加说明：MT - 美团；HF - 话费；YK - 油卡');
            $table->string('party_order_id', 70)->comment('商户订单号');
            $table->string('out_trans_id', 70)->comment('交易订单号');
            $table->string('status', 20)->comment('订单状态：await 待支付；pending 支付处理中； succeeded 支付成功；failed 支付失败');
            $table->string('created_time', 20)->comment('支付创建时间');
            $table->string('end_time', 20)->comment('支付完成时间');

            $table->index(['pid', 'uid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_logs');
    }
}

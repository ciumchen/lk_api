<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradeOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_order', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('user_id')->nullable()->comment('买家id');
            $table->string('title',90)->nullable()->comment("商品标题");
            $table->decimal('price', 20, 2)->comment('商品价格');
            $table->unsignedInteger('num')->nullable()->comment('购买数量');
            $table->string('numeric',30)->nullable()->comment("话费：手机号；美团、油卡：卡号");
            $table->string('telecom',20)->nullable()->comment("话费充值运营商");
            $table->string('pay_time', 20)->nullable()->comment('付款时间');
            $table->string('end_time', 20)->nullable()->comment('结束时间');
            $table->timestamp('modified_time')->nullable()->comment('最后更新时间');
            $table->string('status', 24)->comment('交易状态：await 待支付；pending 支付处理中； succeeded 支付成功；failed 支付失败');
            $table->string('order_from', 10)->comment('订单来源：alipay；wx');
            $table->string('order_no', 40)->comment('订单号');
            $table->decimal('need_fee', 20, 2)->comment('支付金额');
            $table->decimal('profit_ratio', 20, 2)->comment('让利比例');
            $table->decimal('profit_price', 20, 2)->comment('实际让利金额');
            $table->decimal('integral', 20, 2)->comment('订单用户积分');
            $table->string('description',15)->nullable()->comment("支付附加说明：MT - 美团；HF - 话费；YK - 油卡");
            $table->unsignedInteger('oid')->nullable()->comment('order表 -- id');
            $table->timestamp('created_at')->nullable()->comment('创建时间');

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trade_order');
    }
}

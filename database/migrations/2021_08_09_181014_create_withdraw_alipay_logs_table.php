<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateWithdrawAlipayLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw_cash_logs', function (Blueprint $table) {
            $table->id();
            $table->string('balance_type', 20)->default('')->comment('本平台内提现账户类型:来拼金或可提现余额');
            $table->string('channel', 20)->default('')->comment('提现渠道:支付宝或微信');
            $table->decimal('money', 15, 2)->default(0.00)->comment('提现金额');
            $table->bigInteger('user_id')->default(0)->comment('用户ID');
            $table->string('alipay_user_id', 20)->default('')->comment('提现支付宝UID');
            $table->string('alipay_account', 50)->default('')->comment('用户支付宝账户');
            $table->string('order_no', 40)->default('')->comment('提现订单号');
            $table->string('alipay_nickname', 50)->default('')->comment('用户支付宝昵称');
            $table->string('alipay_avatar')->default('')->comment('用户支付宝头像');
            $table->string('real_name', 50)->default('')->comment('用户真实姓名');
            $table->string('out_trade_no', 32)->default('')->comment('转账单号[支付宝返回]');
            $table->string('pay_fund_order_id', 32)->default('')->comment('支付资金流水号[支付宝返回]');
            $table->timestamp('trans_date', 0)->nullable()->comment('订单支付时间[支付宝返回]');
            $table->string('alipay_status', 32)->default('')->comment('状态[支付宝返回]');
            $table->decimal('handling_ratio', 5, 2)->default(0.00)->comment('手续费比例');
            $table->decimal('handling_price', 15, 2)->default(0.00)->comment('手续费');
            $table->decimal('actual_amount', 5, 2)->default(0.00)->comment('实际到账金额');
            $table->decimal('balance_fee', 15, 2)->default(0.00)->comment('提现后账户余额');
            $table->tinyInteger('status')->default(1)->comment('交易状态:1处理中,2成功,3失败');
            $table->text('remark')->nullable()->comment('业务备注');
            $table->text('failed_reason')->nullable()->comment('提现失败原因');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE `withdraw_cash_logs` comment "现金提现记录表";');
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdraw_alipay_logs');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserShoppingCardDhLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_shopping_card_dh_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->nullable()->comment('users表 -- id');
            $table->string('operate_type', 20)->nullable()->default('')->comment('操作类型,兑换代充：exchange_dc,批量代充：exchange_pl,兑换美团：exchange_mt');
            $table->decimal('money', 15, 2)->default(0.00)->comment('变动购物卡金额');
            $table->decimal('money_before_change', 15, 2)->default(0.00)->comment('变动前购物卡余额');
            $table->string('order_no', 40)->default('')->comment('充值订单号');
            $table->tinyInteger('status')->default(1)->comment('兑换状态:1处理中,2成功,3失败');
            $table->text('remark')->nullable()->comment('备注');
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
        Schema::dropIfExists('user_shopping_card_dh_log');
    }
}

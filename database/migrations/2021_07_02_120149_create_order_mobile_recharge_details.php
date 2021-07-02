<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderMobileRechargeDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'order_mobile_recharge_details',
            function (Blueprint $table) {
                $table->id();
                $table->bigInteger('order_mobile_id')->default(0)->comment('order_mobile订单ID');
                $table->bigInteger('order_id')->default(0)->comment('order订单ID');
                $table->string('mobile', 11)->default('')->comment('充值手机');
                $table->decimal('money')->default(0.00)->comment('充值金额');
                $table->tinyInteger('status')->unsigned()->default('0')->comment('充值状态:0充值中,1成功,9撤销');
                $table->timestamps();
            }
        );
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_mobile_recharge_details');
    }
}

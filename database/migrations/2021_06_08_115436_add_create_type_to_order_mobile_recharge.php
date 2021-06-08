<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreateTypeToOrderMobileRecharge extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_mobile_recharge', function (Blueprint $table) {
            //添加type 字段
            $table->tinyInteger('create_type')
                  ->default(0)
                  ->comment('订单类型:1充值订单,代充订单');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_mobile_recharge', function (Blueprint $table) {
            //
            $table->dropColumn('create_type');
        });
    }
}

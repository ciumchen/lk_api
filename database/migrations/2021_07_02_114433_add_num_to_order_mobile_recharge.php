<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNumToOrderMobileRecharge extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'order_mobile_recharge',
            function (Blueprint $table) {
                $table->mediumInteger('num')->default(1)->comment('数量');
                $table->tinyInteger('has_child')->default(0)->comment('是否有子订单');
                //
            }
        );
        DB::statement('ALTER TABLE `order_mobile_recharge` comment "手机充值订单表";');
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'order_mobile_recharge',
            function (Blueprint $table) {
                //
            }
        );
    }
}

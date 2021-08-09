<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrderIntegralLkDistribution2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_integral_lk_distribution', function (Blueprint $table) {
            $table->unsignedDecimal('other_price',8,2)->nullable()->default(0)->comment('订单消费金额其他让利比例的累计');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_integral_lk_distribution', function (Blueprint $table) {
            $table->dropColumn(['other_price']);
        });
    }
}


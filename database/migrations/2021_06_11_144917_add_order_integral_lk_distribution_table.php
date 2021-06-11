<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderIntegralLkDistributionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_integral_lk_distribution', function (Blueprint $table) {
            $table->unsignedDecimal('price_5',8,2)->nullable()->default(0)->comment('订单消费金额5%让利比例的累计');
            $table->unsignedDecimal('price_10',8,2)->nullable()->default(0)->comment('订单消费金额10%让利比例的累计');
            $table->unsignedDecimal('price_20',8,2)->nullable()->default(0)->comment('订单消费金额20%让利比例的累计');

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
            $table->dropColumn(['price_5']);
            $table->dropColumn(['price_10']);
            $table->dropColumn(['price_20']);
        });
    }
}


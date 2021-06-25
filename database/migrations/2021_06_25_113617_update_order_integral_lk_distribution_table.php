<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrderIntegralLkDistributionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_integral_lk_distribution', function (Blueprint $table) {
            $table->unsignedInteger('dr_count')->nullable()->default(0)->comment('导入订单数量统计');

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
            $table->dropColumn(['dr_count']);
        });
    }
}


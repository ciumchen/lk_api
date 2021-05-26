<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIntegralLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integral_log',function (Blueprint $table){
            $table->string('order_no', 30)->nullable()->comment('trade_order表 -- order_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integral_log', function (Blueprint $table) {
            $table->dropColumn(['order_no']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToOrderAirTrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_air_trade', function (Blueprint $table) {
            $table->unsignedInteger('oid')->default(0)->comment('order 表 -- id');

            $table->index('oid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_air_trade', function (Blueprint $table) {
            $table->dropColumn(['oid']);
        });
    }
}

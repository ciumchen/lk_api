<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldTradeOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_order', function (Blueprint $table) {
            $table->string('idcard', 25)->default('')->comment('身份证号');
            $table->string('user_name', 25)->default('')->comment('姓名');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trade_order', function (Blueprint $table) {
            $table->dropColumn(['idcard', 'user_name']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIntegralLog2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integral_log',function (Blueprint $table){
            $table->string('description', 20)->nullable()->comment('订单类型');
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
            $table->dropColumn(['description']);
        });
    }
}

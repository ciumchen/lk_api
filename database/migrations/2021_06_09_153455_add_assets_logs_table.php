<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssetsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_logs', function (Blueprint $table) {
            $table->string('order_no', 40)->nullable()->default('')->comment('订单号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets_logs', function (Blueprint $table) {
            $table->dropColumn(['order_no']);
        });
    }
}

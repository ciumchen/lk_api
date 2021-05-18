<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBusinessDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_data',function (Blueprint $table){
            $table->unsignedInteger('state')->default(0)->comment('商户单独设置今日限额开关，默认0，0表示关闭，1表示开启');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_data', function (Blueprint $table) {
            $table->dropColumn(['state']);
        });
    }
}

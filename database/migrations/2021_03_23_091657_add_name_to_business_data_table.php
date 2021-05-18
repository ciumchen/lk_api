<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToBusinessDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_data', function (Blueprint $table) {
            $table->string('name',30)->comment('商店名称');
            $table->string('main_business',30)->comment('主营业务');
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
            $table->dropColumn(['name']);
        });
    }
}

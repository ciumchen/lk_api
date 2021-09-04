<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCityDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('city_data',function (Blueprint $table){
            $table->mediumInteger('level')->default(0)->comment('层级')->index();
            $table->bigInteger('pid')->default(0)->comment('父级id')->index();
            $table->string('pid_route',255)->default(0)->comment('父级路由');
            $table->index('name');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('city_data', function (Blueprint $table) {
            $table->dropColumn(['level','pid','pid_route']);
        });
    }
}

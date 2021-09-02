<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCityDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_city_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->nullable()->comment('users表id')->index();
            $table->string("province",20)->default('')->comment('省份');
            $table->string("city",30)->default('')->comment('市');
            $table->string("district",30)->default('')->comment('区');
            $table->string("address",100)->default('')->comment('详细地址');
            $table->string("lng",30)->default('')->comment('经度');
            $table->string("lat",30)->default('')->comment('纬度');
            $table->unsignedInteger("city_data_id")->nullable()->comment('city_data表id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_city_data');
    }
}

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
            $table->unsignedInteger("province_id")->nullable()->comment('city_data表--省份id')->index();
            $table->unsignedInteger("city_id")->nullable()->comment('city_data表--城市id')->index();
            $table->unsignedInteger("district_id")->nullable()->comment('city_data表--区id')->index();
            $table->string("address",100)->default('')->comment('详细地址');
            $table->string("lng",30)->default('')->comment('经度');
            $table->string("lat",30)->default('')->comment('纬度');
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

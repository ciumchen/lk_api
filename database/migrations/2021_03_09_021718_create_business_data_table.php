<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('uid')->unique()->comment('uid');
            $table->text('banners')->nullable()->comment('商家头图');
            $table->string('contact_number',64)->nullable()->comment('联系方式');
            $table->string('address',128)->nullable()->comment('商家详细地址');
            $table->unsignedInteger('province')->nullable()->comment('省');
            $table->unsignedInteger('city')->nullable()->comment('市');
            $table->unsignedInteger('district')->nullable()->comment('区');
            $table->string('lt',32)->nullable()->comment('经度');
            $table->string('lg',32)->nullable()->comment('纬度');
            $table->tinyInteger('category_id')->index()->comment('店铺类别');
            $table->tinyInteger('status')->default('1')->index()->comment('1正常，2休息，3已关店,4店铺已被封禁');
            $table->string('run_time',32)->nullable()->comment('营业时间');
            $table->text('content')->nullable()->comment('商家内容介绍');
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
        Schema::dropIfExists('business_data');
    }
}

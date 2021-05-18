<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad', function (Blueprint $table) {
            $table->engine="InnoDB";
            $table->id();
            $table->string('name',50)->comment('广告名称');
            $table->tinyInteger('position')->comment('位置');
            $table->string('img_url',100)->nullable()->comment('图片');
            $table->tinyInteger('status')->default(1)->comment('状态 1显示 2不显示');
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
        Schema::dropIfExists('ad');
    }
}

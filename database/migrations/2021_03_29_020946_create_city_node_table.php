<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCityNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city_node', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('uid')->index()->nullable()->comment('站长UID');
            $table->string('name', 100)->nullable()->comment('站点名称');
            $table->integer('province')->index()->comment('省');
            $table->integer('city')->index()->nullable()->comment('市');
            $table->integer('district')->index()->nullable()->comment('区');
            $table->integer('user_number')->default(0)->comment('站点用户数');
            $table->integer('new_user_number')->default(0)->comment('昨日新增站点用户数');
            $table->integer('user_active')->default(0)->comment('活跃用户数');
            $table->tinyInteger('status')->default('1')->comment('1正常返佣，2不参与返佣');
            $table->decimal('total_consumption', 2)->default(0)->comment('站点总消费额');
            $table->decimal('yesterday_consumption', 2)->default(0)->comment('昨日站点消费额');


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
        Schema::dropIfExists('city_node');
    }
}

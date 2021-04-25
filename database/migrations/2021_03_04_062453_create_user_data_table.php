<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_data', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('uid')->comment('uid');
            $table->dateTime('last_login')->nullable()->comment('最后登录时间');
            $table->string('last_ip',16)->nullable()->comment("最后登录ip");
            $table->dateTime('change_address_time')->nullable()->comment('上次修改地址时间');
            $table->dateTime('change_password_time')->nullable()->comment('上次修改密码时间');
            $table->string('change_password_ip',16)->nullable()->comment("修改密码ip");
            $table->string('id_card',19)->nullable()->comment('身份证号');
            $table->string('name',10)->nullable()->comment('姓名');
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
        Schema::dropIfExists('user_data');
    }
}

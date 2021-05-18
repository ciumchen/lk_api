<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('invite_uid')->nullable()->index()->comment('邀请人id');
            $table->unsignedTinyInteger('role')->default(1)->index()->comment('1普通用户，2商家');
            $table->unsignedTinyInteger('business_lk')->default(0)->comment('商家权');
            $table->unsignedTinyInteger('lk')->default(0)->comment('消费者权');
            $table->unsignedDecimal('integral',18, 2)->default(0)->comment('消费者积分');
            $table->unsignedDecimal('business_integral',18, 2)->default(0)->comment('商家积分');
            $table->string('phone',15)->unique()->comment("手机号");
            $table->string('username',20)->unique()->nullable()->comment("用户名");
            $table->string('avatar',64)->nullable()->comment("用户名头像");
            $table->string('salt', 6)->comment('盐');
            $table->string('code_invite', 6)->unique()->comment('邀请码');
            $table->tinyInteger('status')->default('1')->comment('1正常，2异常');
            $table->tinyInteger('is_auth')->default('1')->comment('1未实名，2已实名');
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
        Schema::dropIfExists('users');
    }
}

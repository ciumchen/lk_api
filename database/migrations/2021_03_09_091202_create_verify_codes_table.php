<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerifyCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verify_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 50)->comment('手机号');
            $table->string('code', 10)->comment('验证码');
            $table->unsignedTinyInteger('type')->comment('类型: 1 登录，2 注册，3 修改密码');
            $table->boolean('used')->default(false)->comment('是否使用');
            $table->dateTime('expires_at')->nullable()->comment('过期时间');
            $table->timestamps();

            $table->index(['phone', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('verify_codes');
    }
}

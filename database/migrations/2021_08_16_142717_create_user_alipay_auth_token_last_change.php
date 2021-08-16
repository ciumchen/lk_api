<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAlipayAuthTokenLastChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_alipay_auth_last_change', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->default(0)->comment('用户ID');
            $table->string('alipay_user_id', 20)->default('')->comment('用户alipayID');
            $table->string('alipay_nickname', 50)->default('')->comment('用户支付宝昵称');
            $table->string('alipay_avatar')->default('')->comment('用户支付宝头像');
            $table->string('alipay_alipay_user_id', 50)->default('')->comment('用户支付宝alipay_user_id');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE `user_alipay_auth_token_last_change` comment "用户支付宝授权记录表";');
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_alipay_auth_last_change');
    }
}

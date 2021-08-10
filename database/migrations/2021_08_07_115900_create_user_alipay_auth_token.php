<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAlipayAuthToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_alipay_auth_token', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('uid')->default(0)->comment('用户ID');
            $table->char('auth_code', 50)->default('')->comment('支付宝用户授权后的auth_code');
            $table->char('app_id', 20)->default('')->comment('用户授权APPID');
            $table->char('source', 50)->default('')->comment('用户授权source');
            $table->char('scope', 50)->default('')->comment('用户授权scope');
            $table->tinyInteger('is_used')->default(0)->comment('是否已被使用过:0未使用,1已使用');
            $table->char('alipay_user_id', 20)->default('')->comment('用户支付宝UID');
            $table->char('alipay_alipay_user_id', 50)->default('')->comment('用户支付宝alipay_user_id');
            $table->char('access_token', 50)->default('')->comment('用户访问令牌');
            $table->integer('expires_in')->default(0)->comment('访问令牌的有效时间，单位是秒');
            $table->char('refresh_token', 50)->default('')->comment('刷新令牌。通过该令牌可以刷新access_token');
            $table->integer('re_expires_in')->default(0)->comment('刷新令牌的有效时间，单位是秒');
            $table->timestamps();
        });
        DB::statement('ALTER TABLE `user_alipay_auth_token` comment "用户支付宝Access_token表";');
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_alipay_auth_token');
    }
}

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
        Schema::dropIfExists('user_alipay_auth_token');
    }
}

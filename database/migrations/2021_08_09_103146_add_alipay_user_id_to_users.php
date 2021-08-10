<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlipayUserIdToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('alipay_user_id', 20)->default('')->comment('用户支付宝ID');
            $table->string('alipay_account')->default('')->comment('用户支付宝账户');
            $table->string('alipay_nickname')->default('')->comment('用户支付宝昵称');
            $table->string('alipay_avatar')->default('')->comment('用户支付宝头像');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('alipay_user_id');
        });
    }
}

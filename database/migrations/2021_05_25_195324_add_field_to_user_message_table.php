<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToUserMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_message', function (Blueprint $table) {
            $table->unsignedInteger('is_del')->default(0)->comment('是否删除：0 否；1 是');
            $table->string('order_no', 30)->nullable()->comment('trade_order表 -- order_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_message', function (Blueprint $table) {
            $table->dropColumn(['is_del']);
            $table->dropColumn(['order_no']);
        });
    }
}

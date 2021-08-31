<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexUserMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_message', function (Blueprint $table) {
            //
            $table->index('user_id');
            $table->index('sys_mid');
            $table->index('is_del');
            $table->index('order_no');
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
            //
            $table->dropIndex('user_id');
            $table->dropIndex('sys_mid');
            $table->dropIndex('is_del');
            $table->dropIndex('order_no');
        });
    }
}

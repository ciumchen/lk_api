<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToOrderVideo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_video', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('order_no');
            $table->index('order_id');
            $table->index('pay_status');
            $table->index('status');
            $table->index('create_type');
            $table->index('channel');
            //
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_video', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('order_no');
            $table->dropIndex('order_id');
            $table->dropIndex('pay_status');
            $table->dropIndex('status');
            $table->dropIndex('create_type');
            $table->dropIndex('channel');
            //
        });
    }
}

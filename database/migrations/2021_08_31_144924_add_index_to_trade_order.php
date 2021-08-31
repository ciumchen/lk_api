<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToTradeOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_order', function (Blueprint $table) {
            //
            $table->index('numeric');
            $table->index('status');
            $table->index('order_from');
            $table->index('order_no');
            $table->index('oid');
            $table->index('description');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trade_order', function (Blueprint $table) {
            //
            $table->dropIndex('numeric');
            $table->dropIndex('status');
            $table->dropIndex('order_from');
            $table->dropIndex('order_no');
            $table->dropIndex('oid');
            $table->dropIndex('description');
        });
    }
}

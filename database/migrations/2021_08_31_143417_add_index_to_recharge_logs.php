<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToRechargeLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recharge_logs', function (Blueprint $table) {
            $table->index('order_no');
            $table->index('reorder_id');
            $table->index('type');
            $table->index('status');
            $table->dropIndex(['order_no', 'reorder_id']);
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
        Schema::table('recharge_logs', function (Blueprint $table) {
            $table->dropIndex('order_no');
            $table->dropIndex('reorder_id');
            $table->dropIndex('type');
            $table->dropIndex('status');
            $table->index(['order_no', 'reorder_id']);
            //
        });
    }
}

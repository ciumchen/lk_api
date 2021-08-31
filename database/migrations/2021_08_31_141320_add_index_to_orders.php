<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order', function (Blueprint $table) {
            $table->index('status');
            $table->index('line_up');
            $table->index('profit_ratio');
            $table->index('pay_status');
            $table->index('created_at');
            $table->index('description');
            $table->index('to_status');
            $table->index('state');
            $table->index('member_gl_oid');
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
        Schema::table('order', function (Blueprint $table) {
            $table->dropIndex('status');
            $table->dropIndex('line_up');
            $table->dropIndex('profit_ratio');
            $table->dropIndex('pay_status');
            $table->dropIndex('created_at');
            $table->dropIndex('description');
            $table->dropIndex('to_status');
            $table->dropIndex('state');
            $table->dropIndex('member_gl_oid');
            //
        });
    }
}

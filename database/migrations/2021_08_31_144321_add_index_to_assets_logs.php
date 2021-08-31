<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToAssetsLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets_logs', function (Blueprint $table) {
            $table->index('assets_type_id');
            $table->index('assets_name');
            $table->index('operate_type');
            $table->index('order_no');
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
        Schema::table('assets_logs', function (Blueprint $table) {
            $table->dropIndex('assets_type_id');
            $table->dropIndex('assets_name');
            $table->dropIndex('operate_type');
            $table->dropIndex('order_no');
            //
        });
    }
}

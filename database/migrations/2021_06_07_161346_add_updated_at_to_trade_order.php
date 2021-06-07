<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedAtToTradeOrder extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_order', function (Blueprint $table) {
            $table->dateTime('updated_at')
                  ->nullable()
                  ->comment('更新时间');
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
        Schema::table('trade_order', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexAtGatherTrade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gather_trade', function (Blueprint $table) {
            $table->index('gid');
            $table->index('uid');
            $table->index('guid');
            $table->index('oid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gather_trade', function (Blueprint $table) {
            //
        });
    }
}

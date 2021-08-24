<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserShoppingCardDhLog2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_shopping_card_dh_log', function (Blueprint $table) {
            $table->unsignedInteger('gather_shopping_card_id')->nullable()->comment('gather_shopping_card表的id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_shopping_card_dh_log', function (Blueprint $table) {
            $table->dropColumn('gather_shopping_card_id');
        });
    }
}

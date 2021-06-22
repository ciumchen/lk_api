<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChannelToOrderVideo extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_video', function (Blueprint $table) {
            $table->string('channel', 20)
                  ->default('')
                  ->comment('下单渠道:bm斑马力方,ww万维易源');
            $table->text('card_list')
                  ->nullable()
                  ->comment('订单卡密信息');
            $table->string('item_id', 50)->change();
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
            //
        });
    }
}

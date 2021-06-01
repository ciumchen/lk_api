<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order',function (Blueprint $table){
            $table->unsignedTinyInteger('to_be_added_integral')->nullable()->default(0)->comment('用户待加积分');
            $table->unsignedInteger('to_status')->nullable()->default(0)->comment('订单处理状态：默认0,1表示待处理,2表示已处理');
            $table->unsignedInteger('line_up')->nullable()->default(0)->comment('排队状态,默认0不排队,1表示排队');
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
            $table->dropColumn(['to_be_added_integral']);
            $table->dropColumn(['to_status']);
            $table->dropColumn(['line_up']);
        });
    }
}

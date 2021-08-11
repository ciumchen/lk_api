<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBalanceAllowanceToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance_allowance', 15, 2)->default(0.00)->comment('可提现额度[补贴]');
            $table->decimal('balance_consume', 15, 2)->default(0.00)->comment('再消费额度');
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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('balance_allowance');
            $table->dropColumn('balance_consume');
        });
    }
}

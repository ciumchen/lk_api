<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReturnIntergralToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('return_integral',18,2)->default(0)->comment('已返消费者积分');
            $table->decimal('return_business_integral',18,2)->default(0)->comment('已返商家积分');
            $table->decimal('return_lk',18,2)->default(0)->comment('已返LK积分');
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
            $table->dropColumn(['return_integral','return_business_integral', 'return_lk']);
        });
    }
}

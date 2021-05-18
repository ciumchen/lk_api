<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('business_lk')->default(0)->comment('商家权')->change();
            $table->unsignedInteger('lk')->default(0)->comment('消费者权')->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->unsignedTinyInteger('business_lk')->default(0)->comment('商家权')->change();
            $table->unsignedTinyInteger('lk')->default(0)->comment('消费者权')->change();
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignToUsers extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table
                ->string('sign', '50')
                ->default('')
                ->comment('个性签名');
            $table
                ->tinyInteger('sex')
                ->default(0)
                ->comment('性别:0保密,1男,2女');
            $table
                ->date('birth')
                ->nullable()
                ->comment('生日');
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
            $table->dropColumn('sign');
            $table->dropColumn('sex');
            $table->dropColumn('birth');
        });
    }
}

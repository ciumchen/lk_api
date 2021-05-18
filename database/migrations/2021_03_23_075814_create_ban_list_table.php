<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ban_list', function (Blueprint $table) {
            $table->engine="InnoDB";
            $table->id();
            $table->integer('uid')->comment('用户ID');
            $table->string('reason',32)->comment('封禁原因');
            $table->string('ip',16)->comment('ip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ban_list');
    }
}

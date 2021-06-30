<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfficheTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'affiche',
            function (Blueprint $table) {
                $table->id();
                $table->string('title', 100)->default('')->comment('公告标题');
                $table->text('content')->nullable()->comment('公告内容');
                $table->tinyInteger('is_del')->default(0)->comment('标记删除');
                $table->timestamps();
            }
        );
        DB::statement('ALTER TABLE `affiche` comment "公告信息表";');
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiche');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_category', function (Blueprint $table) {
            $table->id();
            $table->string('name',32)->comment('分类名称');
            $table->string('img_url',64)->nullable()->comment('分类图片');
            $table->unsignedInteger('parent_id')->default(0)->comment('上级分类');
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
        Schema::dropIfExists('business_category');
    }
}

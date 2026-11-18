<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('my_product_menu_local', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_id')->nullable()->index('idx_product_id')->comment('商品ID');
            $table->string('main_img_path')->nullable()->comment('本地商品图片存放路径');
            $table->text('img_path')->nullable()->comment('其他图片');
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
        Schema::dropIfExists('my_product_menu_local');
    }
};

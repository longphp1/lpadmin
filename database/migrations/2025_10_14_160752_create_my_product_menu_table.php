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
        Schema::create('my_product_menu', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('site_id')->nullable()->comment('网站id');
            $table->integer('menu_id')->nullable()->comment('所属菜单id');
            $table->integer('page')->nullable()->comment('当前页数');
            $table->integer('range')->nullable()->comment('当前商品排序');
            $table->string('name')->nullable()->comment('商品名称');
            $table->string('price')->nullable()->comment('商品价格');
            $table->string('brand')->nullable()->comment('商品品牌');
            $table->string('source')->nullable()->comment('商品来源平台');
            $table->string('source_product_id')->nullable()->comment('商品采购平台id');
            $table->string('source_product_url')->nullable()->comment('商品采购平台url');
            $table->string('main_img')->nullable()->comment('商品主图');
            $table->text('img')->nullable()->comment('商品图片列表');
            $table->string('main_buy_link')->nullable()->comment('购买链接');
            $table->string('main_buy_platform')->nullable()->comment('购买平台');
            $table->string('other_buy_link')->nullable()->comment('购买平台及链接');
            $table->string('other_buy_platform')->nullable()->comment('购买平台');
            $table->string('note_link')->nullable()->comment('商品注释链接');
            $table->string('note_content')->nullable()->comment('商品注释');
            $table->timestamp('created_at')->useCurrentOnUpdate()->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
            $table->string('tao_buy_platform')->nullable();
            $table->string('tao_buy_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('my_product_menu');
    }
};

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
        Schema::create('my_menu_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('site_id')->nullable()->comment('网站id');
            $table->string('menu_name')->nullable()->comment('菜单名称');
            $table->string('menu_name_en')->nullable()->comment('菜单英文名称');
            $table->string('menu_url')->nullable()->comment('菜单URL');
            $table->integer('range')->nullable()->comment('菜单展示顺序');
            $table->integer('type')->default(0)->comment('菜单类型,0:首页1底部栏2商品栏3跳转公司栏');
            $table->tinyInteger('status')->default(0)->comment('菜单状态0启用1禁用');
            $table->timestamp('created_at')->useCurrentOnUpdate()->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
            $table->string('meta_keyword', 512)->nullable()->comment('页面keywords');
            $table->string('meta_description', 512)->nullable()->comment('页面description');
            $table->string('meta_title', 512)->nullable()->comment('页面标题');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('my_menu_config');
    }
};

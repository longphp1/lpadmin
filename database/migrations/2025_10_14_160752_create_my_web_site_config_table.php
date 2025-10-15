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
        Schema::create('my_web_site_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable()->comment('网站名称');
            $table->string('domain')->nullable()->comment('域名');
            $table->string('min_logo')->nullable()->comment('网站tab栏logo');
            $table->string('apple_touch_logo')->nullable()->comment('网站tab栏logo');
            $table->timestamp('created_at')->useCurrentOnUpdate()->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
            $table->string('meta_keyword', 512)->nullable()->comment('所有页面基础keywords');
            $table->string('meta_description', 512)->nullable()->comment('所有页面基础description');
            $table->string('title', 512)->nullable()->comment('所有页面基础标题');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('my_web_site_config');
    }
};

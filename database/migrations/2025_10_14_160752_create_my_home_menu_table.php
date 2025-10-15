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
        Schema::create('my_home_menu', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('site_id')->nullable()->comment('网站id');
            $table->string('top_head')->nullable()->comment('头部顶底');
            $table->string('large_head')->nullable()->comment('头部大标题');
            $table->string('top_head_bottom')->nullable()->comment('头部底部');
            $table->text('header_content')->nullable()->comment('头部');
            $table->string('middle_qr_code')->nullable()->comment('中部二维码');
            $table->text('middle_qr_title')->nullable()->comment('中部文字');
            $table->string('middle_logo')->nullable()->comment('中部图片');
            $table->text('middle_content')->nullable()->comment('首页中间其他内容');
            $table->text('content')->nullable()->comment('首页内容');
            $table->timestamp('created_at')->useCurrentOnUpdate()->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('my_home_menu');
    }
};

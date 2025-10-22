<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('my_website_platform', function (Blueprint $table) {
            $table->id();
            $table->string('site_id')->nullable()->comment('网站id');
            $table->string('platform')->nullable()->comment('平台');
            $table->string('platform_name')->nullable()->comment('平台名称');
            $table->string('platform_url')->nullable()->comment('平台url');
            $table->string('discord_url')->nullable()->comment('discord url');
            $table->string('meta_keyword')->nullable()->comment('网站关键词');
            $table->string('meta_description')->nullable()->comment('网站描述');
            $table->string('meta_title')->nullable()->comment('网站标题');
            $table->string('status')->nullable()->comment('状态：update_success:更新成功，update_failed:更新失败,push_success:推送成功，push_failed:推送失败');
            $table->timestamp('push_at')->nullable()->comment('推送时间');
            $table->timestamp('created_at')->useCurrentOnUpdate()->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('my_website_platform');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('my_website_platform', 'site_name')) {
            return;
        }
        Schema::table('my_website_platform', function (Blueprint $table) {
            $table->string('site_name')->nullable()->after('site_id')->comment('网站名称');
            $table->string('domain')->nullable()->after('site_name')->comment('网站域名');
            $table->string('min_logo', 500)->nullable()->after('domain')->comment('网站logo');
        });
    }

    public function down(): void
    {
        Schema::table('my_website_platform', function (Blueprint $table) {
            $table->dropColumn(['site_name', 'domain', 'min_logo']);
        });
    }
};

<?php


namespace App\Models\LPadmin\Website;


use Illuminate\Database\Eloquent\Model;

class WebsitePlatform extends Model
{
    public $timestamps = false;

    protected $table = 'my_website_platform';

    protected $fillable = ['site_id', 'platform', 'platform_name', 'platform_url', 'discord_url', 'meta_keyword', 'meta_description', 'meta_title', 'status', 'push_at', 'created_at', 'updated_at'];

    public function siteConfig()
    {
        return $this->hasOne(WebSiteConfig::class, 'id', 'site_id');
    }
}

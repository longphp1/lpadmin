<?php


namespace App\Models\LPadmin\Website;


use Illuminate\Database\Eloquent\Model;

class MenuConfig extends Model
{
    public $timestamps = false;

    protected $table = 'my_menu_config';

    protected $fillable = ['site_id', 'menu_name', 'menu_name_en','menu_url', 'range', 'type', 'status','meta_keyword','meta_description','title'];

    public function siteConfig()
    {
        return $this->hasOne(WebSiteConfig::class, 'id', 'site_id');
    }
}

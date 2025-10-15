<?php


namespace App\Models\LPadmin\Website;


use Illuminate\Database\Eloquent\Model;

class HomeMenu extends Model
{
    public $timestamps = false;

    protected $table = 'my_home_menu';

    protected $fillable = ['site_id', 'top_head', 'large_head', 'top_head_bottom', 'header_content', 'middle_qr_code', 'middle_qr_title', 'middle_logo', 'middle_content', 'content'];

    public function siteConfig()
    {
        return $this->hasOne(WebSiteConfig::class, 'id', 'site_id');
    }
}

<?php


namespace App\Models\LPadmin\Website;


use Illuminate\Database\Eloquent\Model;

class ProductMenu extends Model
{
    public $timestamps = false;

    protected $table = 'my_product_menu';

    protected $fillable = ['site_id', 'menu_id', 'page', 'range', 'name', 'price','source','source_product_id','source_product_url', 'main_img', 'img', 'main_buy_link', 'main_buy_platform', 'buy_link', 'note_link', 'note_content', 'tao_buy_platform', 'tao_buy_link'];

    public function siteConfig()
    {
        return $this->hasOne(WebSiteConfig::class, 'id', 'site_id');
    }

    public function menu()
    {
        return $this->hasOne(MenuConfig::class, 'id', 'menu_id');
    }

    public function local()
    {
        return $this->hasOne(ProductMenuLocal::class, 'product_id', 'id');
    }
}

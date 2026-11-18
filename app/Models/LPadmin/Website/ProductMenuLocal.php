<?php


namespace App\Models\LPadmin\Website;


use Illuminate\Database\Eloquent\Model;

class ProductMenuLocal extends Model
{
    public $timestamps = false;

    protected $table = 'my_product_menu_local';

    protected $fillable = ['product_id', 'main_img_path', 'img_path'];

    public function product()
    {
        return $this->hasOne('Modules\WebSite\Models\ProductMenu', 'id', 'product_id');
    }
    public function menu()
    {
        return $this->hasOne('Modules\WebSite\Models\MenuConfig', 'id', 'menu_id');
    }
}

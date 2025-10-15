<?php


namespace App\Models\LPadmin\Website;


use Illuminate\Database\Eloquent\Model;

class WebSiteConfig extends Model
{
    public $timestamps = false;

    protected $table = 'my_web_site_config';

    protected $fillable = ['name', 'domain', 'min_logo', 'apple_touch_logo','meta_keyword','meta_description','title'];

    public static $menuType = [['type'=>0,'name'=>'首页'],['type'=>1,'name'=>'底部栏'],['type'=>2,'name'=>'商品栏'],['type'=>3,'name'=>'跳转公司栏']];

    public static $statusList = [['type'=>0,'name'=>'启用'],['type'=>1,'name'=>'禁用']];
}

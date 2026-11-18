<?php

namespace App\Exports;

use App\Models\LPadmin\Website\ProductMenu;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // 1. 构建带表头的数据集合
        $data = new Collection([
            ['分类(Category)', '品牌(Brand)', '商品名称(Name)', '价格(Price)', '主图(Main Image)', '子图(Img)', '主购买链接(Main Buy Link)', '主购买平台(Main Buy Platform)', '其他购买链接(Other Buy Links)', '其他购买平台(Other Buy Platforms)','Note Link', 'Note Content', '来源(Source)', '来源商品ID(Source Product ID)', '来源商品URL(Source Product URL)']
        ]);

        // 2. 查询并追加数据行
        ProductMenu::query()
            ->with(['menu'])
            ->chunk(1000, function ($items) use ($data) {
                foreach ($items as $v) {
                    $data->push([
                        $v->menu->menu_name,
                        $v->brand,
                        $v->name,
                        $v->price,
                        $v->main_img,
                        $v->img,
                        $v->main_buy_link,
                        $v->main_buy_platform,
                        $v->other_buy_link,
                        $v->other_buy_platform,
                        $v->note_link,
                        $v->note_content,
                        $v->source,
                        $v->source_product_id,
                        $v->source_product_url,
                    ]);
                }
            });

        return $data;
    }
}

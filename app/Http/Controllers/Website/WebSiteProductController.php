<?php

namespace App\Http\Controllers\Website;

use App\Exports\ProductExport;
use App\Http\Controllers\LPadmin\BaseController;
use App\Imports\ProductImport;
use App\Models\LPadmin\Website\MenuConfig;
use App\Models\LPadmin\Website\ProductMenu;
use App\Models\LPadmin\Website\WebSiteConfig;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;


class WebSiteProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($request->ajax() && $request->wantsJson()) {
            $query = ProductMenu::with(['siteConfig:id,name', 'menu:id,menu_name']);
            // 搜索
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->filled('brand')) {
                $query->where('brand', 'like', '%' . $request->brand . '%');
            }
            if ($request->filled('menu_id')) {
                $query->where('menu_id', $request->menu_id);
            }
            // 创建时间范围搜索
            if ($request->filled('created_at') && is_array($request->created_at)) {
                $dates = $request->created_at;
                if (!empty($dates[0])) {
                    $query->where('created_at', '>=', $dates[0] . ' 00:00:00');
                }
                if (!empty($dates[1])) {
                    $query->where('created_at', '<=', $dates[1] . ' 23:59:59');
                }
            }
            $field = $request->get('field', 'id');
            $order = $request->get('order', 'desc');
            $query->orderBy($field, $order);

            $page  = $request->get('page', 1);
            $limit = $request->get('limit', 15);

            $productList = $query->paginate($limit, ['*'], 'page', $page);
            $data        = [];
            foreach ($productList as $product) {
                $item   = [
                    'id'                 => $product->id,
                    'site_id'            => $product->siteConfig->id,
                    'site_name'          => $this->cleanUtf8($product->siteConfig->name),
                    'menu_id'            => $product->menu->id,
                    'menu_name'          => $this->cleanUtf8($product->menu->menu_name),
                    'name'               => $this->cleanUtf8($product->name),
                    'brand'              => $this->cleanUtf8($product->brand),
                    'page'               => $product->page,
                    'range'              => $product->range,
                    'price'              => $product->price,
                    'source'             => $product->source,
                    'source_product_id'  => $product->source_product_id,
                    'source_product_url' => $product->source_product_url,
                    'main_img'           => $product->main_img,
                    'img'                => $product->img,
                    'main_buy_link'      => $product->main_buy_link,
                    'main_buy_platform'  => $product->main_buy_platform,
                    'other_buy_link'     => $product->other_buy_link,
                    'other_buy_platform' => $product->other_buy_platform,
                    'note_link'          => $product->note_link,
                    'note_content'       => $product->note_content,
                    'tao_buy_platform'   => $product->tao_buy_platform,
                    'tao_buy_link'       => $product->tao_buy_link,
                    'created_at'         => $product->created_at,
                    'updated_at'         => $product->updated_at,
                ];
                $data[] = $item;
            }
            return response()->json([
                'code'  => 0,
                'msg'   => '',
                'count' => $productList->total(),
                'data'  => $data,
            ]);
        }
        $brandList = ProductMenu::query()->groupBy('brand')->pluck('brand')->toArray();
        $menuList       = MenuConfig::query()->select(['id', 'menu_name'])->get();
        return view('website.product.index', compact('brandList', 'menuList'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        $menuList       = MenuConfig::query()->select(['id', 'menu_name'])->get();
        return view('website.product.create', compact('siteConfigList', 'menuList'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $data    = $request->validate([
            'site_id'           => 'required|integer',
            'menu_id'           => 'required|integer',
            'name'              => 'sometimes|nullable|string',
            'price'             => 'sometimes|nullable|string',
            'main_img'          => 'sometimes|nullable|string',
            'img'               => 'sometimes|nullable|string',
            'main_buy_link'     => 'sometimes|nullable|string',
            'main_buy_platform' => 'sometimes|nullable|string',
            'note_link'         => 'sometimes|nullable|string',
            'note_content'      => 'sometimes|nullable|string',
            'brand'             => 'sometimes|nullable|string',
        ]);
        $product = [
            'site_id'            => $data['site_id'],
            'menu_id'            => $data['menu_id'],
            'page'               => $data['page']??1,
            'range'              => $data['range']??1,
            'name'               => $data['name'],
            'price'              => $data['price'],
            'main_img'           => $data['main_img'],
            'img'                => $data['img']??'',
            'source'             => $data['main_buy_platform'],
            'source_product_url' => $data['main_buy_link'],
            'main_buy_link'      => $data['main_buy_link'],
            'main_buy_platform'  => $data['main_buy_platform'],
            'note_link'          => $data['note_link']??'',
            'note_content'       => $data['note_content']??'',
            'brand'              => $data['brand'],
            'created_at'         => Carbon::now()->toDateTimeString(),
            'updated_at'         => Carbon::now()->toDateTimeString(),
        ];
        $result  = ProductMenu::query()->create($product);
        return $this->success($result);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $data = ProductMenu::query()->find($id);
        return $this->success($data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param Request $request
     * @return Renderable
     */
    public function edit(Request $request, $id)
    {
        $data           = ProductMenu::query()->find($id);
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        $menuList       = MenuConfig::query()->select(['id', 'menu_name'])->get();
        return view('website.product.edit', compact('data', 'siteConfigList', 'menuList'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function update(Request $request,  $id)
    {
        $data     = $request->validate([
            'site_id'           => 'required|integer',
            'menu_id'           => 'required|integer',
            'page'              => 'sometimes|integer',
            'range'             => 'sometimes|integer',
            'name'              => 'sometimes|nullable|string',
            'brand'             => 'sometimes|nullable|string',
            'price'             => 'sometimes|nullable|string',
            'main_img'          => 'sometimes|nullable|string',
            'img'               => 'sometimes|nullable|string',
            'main_buy_link'     => 'sometimes|nullable|string',
            'main_buy_platform' => 'sometimes|nullable|string',
            'note_link'         => 'sometimes|nullable|string',
            'note_content'      => 'sometimes|nullable|string',
        ]);
        $product  = [
            'site_id'            => $data['site_id'],
            'menu_id'            => $data['menu_id'],
            'page'               => $data['page'],
            'range'              => $data['range'],
            'name'               => $data['name'],
            'brand'              => $data['brand'],
            'price'              => $data['price'],
            'main_img'           => $data['main_img'],
            'img'                => $data['img'],
            'source'             => $data['main_buy_platform'],
            'source_product_url' => $data['main_buy_link'],
            'main_buy_link'      => $data['main_buy_link'],
            'main_buy_platform'  => $data['main_buy_platform'],
            'note_link'          => $data['note_link'],
            'note_content'       => $data['note_content'],
            'created_at'         => Carbon::now()->toDateTimeString(),
            'updated_at'         => Carbon::now()->toDateTimeString(),
        ];
        $homeData = ProductMenu::query()->find($id);
        if ($homeData) {
            $result = ProductMenu::query()->where('id', $id)->update($product);
            return $this->success($result);
        }
        return $this->error('failed');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return mixed
     */
    public function destroy($id)
    {
        $result = ProductMenu::where('id', $id)->delete();
        return $this->success($result);
    }

    public function batchDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return $this->error('请选择要删除的商品');
        }
        $result = ProductMenu::whereIn('id', $ids)->delete();
        return $this->success($result);
    }



    public function showImport()
    {
        $siteConfigList = WebSiteConfig::query()->select(['id', 'name'])->get();
        $menuList       = MenuConfig::query()->select(['id', 'menu_name'])->get();
        return view('website.product.upload', compact('siteConfigList', 'menuList'));
    }

    public function import(Request $request)
    {
        $data = $request->all();
        Log::info('import data:' . json_encode($data));
        validator($data, [
            'file'    => 'required|file|mimes:xlsx,xls|max:20480',
            'site_id' => 'sometimes|nullable|integer',
            'menu_id' => 'sometimes|nullable|integer'
        ])->validate();

        $requestFile = $data['file'];

        $originalPath = $requestFile->getRealPath();

        $tempFileName = $requestFile->getFilename();

        $sheetDataList = Excel::toArray(new ProductImport, $requestFile);
        Log::info('sheetDataList toArray:' ,$sheetDataList);
        $importProductList  = $sheetDataList[0];
        $header = $importProductList[0];
        unset($importProductList[0]);
        $range            = 1;
        $productList      = [];
        $existProductList = [];
        foreach ($importProductList as $k => $cells) {
            Log::info($cells);
            $page = ceil($range / 100);
            /*if (empty($cells[0]) && $cells[1]) {
                continue;
            }*/
            if (!isset($cells[0])) {
                continue;
            }
            $menuName   = trim($cells[0]);
            $menuConfig = MenuConfig::query()->where('menu_name', $menuName)->first();
            if (!$menuConfig) {
                Log::info('menu:' . $cells[0] . ',brand:' . $cells[1] . ',name:' . $cells[2] . ',price:' . $cells[3]);
                continue;
            }
            $brand = str_replace([PHP_EOL, ",", "，", ".", "\r\n", "\n", "\r"], '', $cells[1]);
            $tmp   = [
                'site_id'            => $data['site_id']??1,
                'menu_id'            => $menuConfig->id,
                'page'               => $page,
                'range'              => $range,
                'name'               => trim($cells[2]),
                'price'              => trim($cells[3]),
                'brand'              => trim($brand),
                'main_img'           => $cells[4],
                'img'                => $cells[5],
                'source_product_url' => $cells[6],
                'main_buy_link'      => $cells[6],
                'main_buy_platform'  => 'weidian',
                'tao_buy_platform'   => 'Open TaooBuy Link',
                'tao_buy_link'       => $cells[7],
                'created_at'         => Carbon::now()->toDateTimeString(),
                'updated_at'         => Carbon::now()->toDateTimeString(),
            ];
            if (empty($tmp['name']) || empty($tmp['price']) || is_array($tmp['name'])) {
                Log::info('menu:' . $cells[0] . ',brand:' . $cells[1] . ',name:' . $cells[2] . ',price:' . $cells[3]);
                continue;
            }
            if (in_array($tmp['name'], $existProductList)) {
                Log::info('menu:' . $cells[0] . ',brand:' . $cells[1] . ',name:' . $cells[2] . ',price:' . $cells[3]);
                continue;
            }
            $existProductList[] = $tmp['name'];
            $source_product_url = $tmp['source_product_url'];
            if (strpos($source_product_url, 'weidian') !== false) {
                // 1. 解析URL获取查询参数部分
                $urlComponents = parse_url($source_product_url);
                $queryString   = $urlComponents['query'] ?? '';
                // 2. 将查询字符串转换为关联数组
                parse_str($queryString, $queryParams);
                // 3. 获取itemID的值
                $itemID                   = $queryParams['itemID'] ?? null;
                $tmp['source']            = 'WD';
                $tmp['source_product_id'] = $itemID;
            } elseif (strpos($source_product_url, 'taobao') !== false) {
                // 1. 解析URL获取查询参数部分
                $urlComponents = parse_url($source_product_url);
                $queryString   = $urlComponents['query'] ?? '';
                // 2. 将查询字符串转换为关联数组
                parse_str($queryString, $queryParams);
                // 3. 获取itemID的值
                $itemID                   = $queryParams['id'] ?? null;
                $tmp['source']            = 'TB';
                $tmp['source_product_id'] = $itemID;
            }
            if ($source_product_url && empty($tmp['source_product_url'])) {
                $keyWord                 = $this->encrypt($source_product_url, 'secret-key');
                $tmp['tao_buy_link']     = 'https://www.taoobuy.com/en_US/goods/detail?keyword=' . $keyWord;
                $tmp['tao_buy_platform'] = 'Open TaooBuy Link';
            }
            $range++;
            Log::info('tmp:', $tmp);
            $existProduct = ProductMenu::query()->where('name', $tmp['name'])->first();
            if ($existProduct && $existProduct->id) {
                Log::info('existProduct:'.$existProduct->name);
                unset($tmp['site_id']);
                unset($tmp['menu_id']);
                unset($tmp['page']);
                unset($tmp['range']);
                unset($tmp['created_at']);
                ProductMenu::query()->where('id', $existProduct->id)->update($tmp);
            } else {
                $productList[] = $tmp;
            }
        }
        Log::info('productList:', $productList);
        if (!empty($productList)) {
            foreach (array_chunk($productList, 500) as $chunk) {
                ProductMenu::query()->insert($chunk);
            }
        }
        return $this->success([]);
    }

    public function encrypt($data, $passphrase, $salt = null)
    {
        $salt = $salt ?: openssl_random_pseudo_bytes(8);
        list($key, $iv) = self::evpkdf($passphrase, $salt);

        $ct = openssl_encrypt($data, 'aes-256-cbc', $key, true, $iv);

        return self::encode($ct, $salt);
    }

    public function showBrand()
    {
        $brandList = ProductMenu::query()->groupBy('brand')->pluck('brand')->toArray();
        return view('website.product.brand', compact('brandList'));
    }

    public function getProductBrand(Request $request)
    {
        $data = $request->all();
        validator($data, [
            'brand' => 'sometimes|nullable|string',
        ])->validate();
        $brand = $data['brand'];
        if (empty($brand)) {
            return ProductMenu::query()->groupBy('brand')->pluck('brand')->toArray();
        }
        $brand = str_replace([PHP_EOL, ",", "，", ".", "\r\n", "\n", "\r"], '', $brand);
        return ProductMenu::query()->where('brand', 'like', '%' . $brand . '%')->groupBy('brand')->pluck('brand')->toArray();
    }

    public function updateProductBrand(Request $request)
    {
        $data = $request->all();
        validator($data, [
            'oldBrand' => 'required|string',
            'newBrand' => 'required|string',
        ])->validate();
        $oldBrand = $data['oldBrand'];
        if (empty($oldBrand)) {
            return $this->result(false, ['msg' => 'brand is empty']);
        }
        $newBrand = $data['newBrand'];
        if (empty($newBrand)) {
            return $this->result(false, ['msg' => 'newBrand is empty']);
        }
        $newBrand = str_replace([PHP_EOL, ",", "，", ".", "\r\n", "\n", "\r"], '', $newBrand);
        ProductMenu::query()->where('brand', 'like', '%' . $oldBrand . '%')->update(['brand' => $newBrand]);
        return $this->success([]);
    }

    public function exportProduct(Request $request)
    {
        return Excel::download(new ProductExport, 'product.xlsx');
    }

}

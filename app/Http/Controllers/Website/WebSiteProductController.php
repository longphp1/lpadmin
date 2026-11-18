<?php

namespace App\Http\Controllers\Website;

use App\Exports\ProductExport;
use App\Http\Controllers\LPadmin\BaseController;
use App\Imports\ProductImport;
use App\Models\LPadmin\Website\MenuConfig;
use App\Models\LPadmin\Website\ProductMenu;
use App\Models\LPadmin\Website\WebSiteConfig;
use App\Services\Website\HiobuyProductService;
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
            'name'               => 'sometimes|nullable|string',
            'price'              => 'sometimes|nullable|string',
            'main_img'           => 'sometimes|nullable|string',
            'img'                => 'sometimes|nullable|string',
            'source'             => 'sometimes|nullable|string',
            'source_product_id'  => 'sometimes|nullable|string',
            'source_product_url' => 'sometimes|nullable|string',
            'main_buy_link'      => 'sometimes|nullable|string',
            'main_buy_platform'  => 'sometimes|nullable|string',
            'note_link'          => 'sometimes|nullable|string',
            'note_content'       => 'sometimes|nullable|string',
            'brand'              => 'sometimes|nullable|string',
        ]);
        $source = trim((string) ($data['source'] ?? ''));
        if ($source === '') {
            $source = $data['main_buy_platform'] ?? '';
        }
        $sourceProductUrl = trim((string) ($data['source_product_url'] ?? ''));
        if ($sourceProductUrl === '') {
            $sourceProductUrl = $data['main_buy_link'] ?? '';
        }
        $product = [
            'site_id'            => $data['site_id'],
            'menu_id'            => $data['menu_id'],
            'page'               => $data['page']??1,
            'range'              => $data['range']??1,
            'name'               => $data['name'],
            'price'              => $data['price'],
            'main_img'           => $data['main_img'],
            'img'                => $data['img']??'',
            'source'             => $source,
            'source_product_id'  => $data['source_product_id'] ?? '',
            'source_product_url' => $sourceProductUrl,
            'main_buy_link'      => $data['main_buy_link'] ?? $sourceProductUrl,
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
            'page'              => 'sometimes|nullable|integer',
            'range'             => 'sometimes|nullable|integer',
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
            'page'               => $data['page']??1,
            'range'              => $data['range']??1,
            'name'               => $data['name']??'',
            'brand'              => $data['brand']??'',
            'price'              => $data['price']??0,
            'main_img'           => $data['main_img']??'',
            'img'                => $data['img']??'',
            'source'             => $data['main_buy_platform']??'',
            'source_product_url' => $data['main_buy_link']??'',
            'main_buy_link'      => $data['main_buy_link']??'',
            'main_buy_platform'  => $data['main_buy_platform']??'',
            'note_link'          => $data['note_link']??'',
            'note_content'       => $data['note_content']??'',
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
        Log::info('import raw data:' . json_encode($data, JSON_UNESCAPED_UNICODE));
        Log::info('import site_id=' . var_export($data['site_id'] ?? null, true) . ', menu_id=' . var_export($data['menu_id'] ?? null, true));

        $siteId = (int) trim((string) ($data['site_id'] ?? ''));
        $menuId = (int) trim((string) ($data['menu_id'] ?? ''));
        Log::info('import parsed site_id=' . $siteId . ', menu_id=' . $menuId);

        validator($data, [
            'file'    => 'required|file|mimes:xlsx,xls|max:20480',
        ])->validate();

        $requestFile = $data['file'];

        $originalPath = $requestFile->getRealPath();

        $tempFileName = $requestFile->getFilename();

        $sheetDataList = Excel::toArray(new ProductImport, $requestFile);
        Log::info('sheetDataList toArray:' ,$sheetDataList);
        $importProductList  = $sheetDataList[0] ?? [];
        if ($importProductList === [] || !is_array($importProductList[0] ?? null)) {
            return $this->error('文件内容为空', 200);
        }
        $data['site_id'] = $siteId;
        $data['menu_id'] = $menuId;
        $header = $importProductList[0];
        if ($this->isSourceUrlImport($header)) {
            return $this->importBySourceUrl($importProductList, $data);
        }
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

    public function fetchSource(Request $request, HiobuyProductService $hiobuy)
    {
        $data = $request->validate([
            'url' => 'required|string|max:2000',
        ]);
        try {
            return $this->success($hiobuy->fetchByUrl($data['url']));
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 200);
        }
    }

    private function isSourceUrlImport(array $header): bool
    {
        $first = trim((string) ($header[0] ?? ''));
        if ($first === '') {
            return false;
        }
        return mb_stripos($first, '来源商品URL') !== false
            || stripos($first, 'Source Product URL') !== false;
    }

    private function importBySourceUrl(array $rows, array $data)
    {
        ini_set('max_execution_time', '1800');
        ini_set('memory_limit', '512M');

        $siteId = max(1, (int) ($data['site_id'] ?? 0));
        $menuId = (int) ($data['menu_id'] ?? 0);
        Log::info('importBySourceUrl site_id=' . $siteId . ', menu_id=' . $menuId);

        if ($menuId <= 0 || !MenuConfig::query()->where('id', $menuId)->exists()) {
            return $this->error('链接导入请先选择商品分类', 200);
        }

        unset($rows[0]);

        $allUrls = [];
        foreach ($rows as $cells) {
            $url = trim((string) ($cells[0] ?? ''));
            if ($url !== '') {
                $allUrls[] = $url;
            }
        }
        $totalUrls = count($allUrls);
        Log::info('importBySourceUrl total unique urls=' . $totalUrls);

        if ($totalUrls === 0) {
            return $this->error('没有可导入的商品链接', 200);
        }

        $existingProducts = ProductMenu::query()
            ->where('site_id', $siteId)
            ->where('menu_id', $menuId)
            ->select(['id', 'source_product_id', 'source_product_url'])
            ->get();

        $existById = [];
        $existBySourceId = [];
        $existBySourceUrl = [];
        foreach ($existingProducts as $ep) {
            $existById[$ep->id] = $ep->id;
            if ($ep->source_product_id) {
                $existBySourceId[$ep->source_product_id] = $ep->id;
            }
            if ($ep->source_product_url) {
                $existBySourceUrl[$ep->source_product_url] = $ep->id;
            }
        }
        Log::info('importBySourceUrl existing products=' . count($existingProducts));

        $range = (int) ProductMenu::query()
            ->where('site_id', $siteId)
            ->where('menu_id', $menuId)
            ->max('range');

        $hiobuy = app(HiobuyProductService::class);
        $concurrency = min(10, max(5, (int) ceil($totalUrls / 20)));
        Log::info('importBySourceUrl fetching with concurrency=' . $concurrency);

        $startedAt = microtime(true);
        $batchResult = $hiobuy->fetchByUrls($allUrls, $concurrency);
        $elapsed = round(microtime(true) - $startedAt, 2);
        Log::info('importBySourceUrl fetch completed in ' . $elapsed . 's');

        $results = $batchResult['results'] ?? [];
        $skipped = $batchResult['skipped'] ?? [];

        $toInsert = [];
        $updates = [];
        $failed = $skipped;
        $now = Carbon::now()->toDateTimeString();

        foreach ($results as $r) {
            if (empty($r['success']) || !isset($r['product'])) {
                $failed[] = ['url' => $r['url'] ?? '', 'error' => $r['error'] ?? '未知错误'];
                continue;
            }

            $product = $r['product'];
            $sourceId = $product['source_product_id'] ?? '';
            $sourceUrl = $product['source_product_url'] ?? '';

            $existId = null;
            if ($sourceId !== '' && isset($existBySourceId[$sourceId])) {
                $existId = $existBySourceId[$sourceId];
            } elseif ($sourceUrl !== '' && isset($existBySourceUrl[$sourceUrl])) {
                $existId = $existBySourceUrl[$sourceUrl];
            }

            if ($existId !== null) {
                $updates[$existId] = array_merge($product, ['updated_at' => $now]);
            } else {
                $range++;
                $product['site_id'] = $siteId;
                $product['menu_id'] = $menuId;
                $product['page'] = (int) ceil($range / 100);
                $product['range'] = $range;
                $product['created_at'] = $now;
                $product['updated_at'] = $now;
                $toInsert[] = $product;

                if ($sourceId !== '') {
                    $existBySourceId[$sourceId] = true;
                }
                if ($sourceUrl !== '') {
                    $existBySourceUrl[$sourceUrl] = true;
                }
            }
        }

        if (!empty($updates)) {
            Log::info('importBySourceUrl updating ' . count($updates) . ' products');
            foreach (array_chunk($updates, 200) as $chunk) {
                foreach ($chunk as $id => $data) {
                    ProductMenu::query()->where('id', $id)->update($data);
                }
            }
        }

        if (!empty($toInsert)) {
            Log::info('importBySourceUrl inserting ' . count($toInsert) . ' products');
            foreach (array_chunk($toInsert, 500) as $chunk) {
                ProductMenu::query()->insert($chunk);
            }
        }

        $created = count($toInsert);
        $updated = count($updates);
        $failedCount = count($failed);

        $message = '新增 ' . $created . ' 条，更新 ' . $updated . ' 条';
        if ($failedCount > 0) {
            $preview = array_slice(array_map(function ($f) {
                return ($f['url'] ?? '') . '：' . ($f['error'] ?? '未知');
            }, $failed), 0, 5);
            $message .= '，失败 ' . $failedCount . ' 条：' . implode('；', $preview);
        }
        Log::info('importBySourceUrl done: created=' . $created . ', updated=' . $updated . ', failed=' . $failedCount);

        if ($created === 0 && $updated === 0) {
            return $this->error($message === '新增 0 条，更新 0 条' ? '没有可导入的商品链接' : $message, 200);
        }
        return $this->success([
            'created' => $created,
            'updated' => $updated,
            'failed'  => $failedCount,
        ], $message);
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
